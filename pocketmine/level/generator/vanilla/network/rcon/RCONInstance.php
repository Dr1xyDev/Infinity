<?php

/*
 *
 *   _____       _                          _
 *  / ____|     | |                        (_)
 * | (___  _   _| |__  _ __ ___   __ _ _ __ _ _ __   ___
 *  \___ \| | | | '_ \| '_ ` _ \ / _` | '__| | '_ \ / _ \
 *  ____) | |_| | |_) | | | | | | (_| | |  | | | | |  __/
 * |_____/ \__,_|_.__/|_| |_| |_|\__,_|_|  |_|_| |_|\___|
 *
 * This program is private software. No license required.
 * Publication of this program is forbidden and will be punished.
 *
 * @author SEMENNEJO
 * @link vk.com/vk.snikers && t.me/semennejo
 *
 *
 */

declare(strict_types=1);

namespace pocketmine\level\generator\vanilla\network\rcon;

use pocketmine\level\generator\vanilla\snooze\SleeperNotifier;
use pocketmine\level\generator\vanilla\thread\log\ThreadSafeLogger;
use pocketmine\level\generator\vanilla\thread\Thread;
use pocketmine\level\generator\vanilla\utils\Binary;
use Socket;

use function count;
use function ltrim;
use function microtime;
use function socket_accept;
use function socket_close;
use function socket_getpeername;
use function socket_last_error;
use function socket_read;
use function socket_select;
use function socket_set_block;
use function socket_set_nonblock;
use function socket_set_option;
use function socket_shutdown;
use function socket_strerror;
use function socket_write;
use function str_replace;
use function strlen;
use function substr;
use function trim;

use const SO_KEEPALIVE;
use const SO_LINGER;
use const SOCKET_ECONNRESET;
use const SOL_SOCKET;

class RCONInstance extends Thread
{
	public string $cmd = "";
	public string $response = "";
	private bool $stop = false;

	public function __construct(
		private Socket $socket,
		private string $password,
		private int $maxClients,
		private ThreadSafeLogger $logger,
		private Socket $ipcSocket,
		private SleeperNotifier $notifier
	) {
	}

	private function writePacket(Socket $client, int $requestID, int $packetType, string $payload) : bool|int
	{
		$pk = Binary::writeLInt($requestID)
			. Binary::writeLInt($packetType)
			. $payload
			. "\x00\x00"; //Terminate payload and packet
		return socket_write($client, Binary::writeLInt(strlen($pk)) . $pk);
	}

	private function readPacket(Socket $client, ?int &$requestID, ?int &$packetType, ?string &$payload) : bool
	{
		$d = @socket_read($client, 4);

		socket_getpeername($client, $ip, $port);
		if ($d === false) {
			$err = socket_last_error($client);
			if ($err !== SOCKET_ECONNRESET) {
				$this->logger->debug("Connection error with $ip $port: " . trim(socket_strerror($err)));
			}
			return false;
		}
		if (strlen($d) !== 4) {
			if ($d !== "") { //empty data is returned on disconnection
				$this->logger->debug("Truncated packet from $ip $port (want 4 bytes, have " . strlen($d) . "), disconnecting");
			}
			return false;
		}
		$size = Binary::readLInt($d);
		if ($size < 0 || $size > 65535) {
			$this->logger->debug("Packet with too-large length header $size from $ip $port, disconnecting");
			return false;
		}
		$buf = @socket_read($client, $size);
		if ($buf === false) {
			$err = socket_last_error($client);
			if ($err !== SOCKET_ECONNRESET) {
				$this->logger->debug("Connection error with $ip $port: " . trim(socket_strerror($err)));
			}
			return false;
		}
		if (strlen($buf) !== $size) {
			$this->logger->debug("Truncated packet from $ip $port (want $size bytes, have " . strlen($buf) . "), disconnecting");
			return false;
		}
		$requestID = Binary::readLInt(substr($buf, 0, 4));
		$packetType = Binary::readLInt(substr($buf, 4, 4));
		$payload = substr($buf, 8, -2); //Strip two null bytes
		return true;
	}

	public function close() : void
	{
		$this->stop = true;
	}

	public function onRun() : void
	{
		/**
		 * @var Socket[]                   $clients
		 * @phpstan-var array<int, Socket> $clients
		 */
		$clients = [];
		/** @var bool[] $authenticated */
		$authenticated = [];
		/** @var float[] $timeouts */
		$timeouts = [];

		/** @var int $nextClientId */
		$nextClientId = 0;

		while (!$this->stop) {
			$r = $clients;
			$r["main"] = $this->socket; //this is ugly, but we need to be able to mass-select()
			$r["ipc"] = $this->ipcSocket;
			$w = null;
			$e = null;

			$disconnect = [];

			if (socket_select($r, $w, $e, 5, 0) > 0) {
				foreach ($r as $id => $sock) {
					if ($sock === $this->socket) {
						if (($client = socket_accept($this->socket)) !== false) {
							if (count($clients) >= $this->maxClients) {
								@socket_close($client);
							} else {
								socket_set_nonblock($client);
								socket_set_option($client, SOL_SOCKET, SO_KEEPALIVE, 1);

								$id = $nextClientId++;
								$clients[$id] = $client;
								$authenticated[$id] = false;
								$timeouts[$id] = microtime(true) + 5;
							}
						}
					} elseif ($sock === $this->ipcSocket) {
						//read dummy data
						socket_read($sock, 65535);
					} else {
						$p = $this->readPacket($sock, $requestID, $packetType, $payload);
						if ($p === false) {
							$disconnect[$id] = $sock;
							continue;
						}

						switch ($packetType) {
							case 3: //Login
								if ($authenticated[$id]) {
									$disconnect[$id] = $sock;
									break;
								}
								if ($payload === $this->password) {
									socket_getpeername($sock, $addr, $port);
									$this->logger->info("Successful Rcon connection from: /$addr:$port");
									$this->writePacket($sock, $requestID, 2, "");
									$authenticated[$id] = true;
								} else {
									$disconnect[$id] = $sock;
									$this->writePacket($sock, -1, 2, "");
								}
								break;
							case 2: //Command
								if (!$authenticated[$id]) {
									$disconnect[$id] = $sock;
									break;
								}
								if ($payload !== "") {
									$this->cmd = ltrim($payload);
									$this->synchronized(function () : void {
										$this->notifier->wakeupSleeper();
										$this->wait();
									});
									$this->writePacket($sock, $requestID, 0, str_replace("\n", "\r\n", trim($this->response)));
									$this->response = "";
									$this->cmd = "";
								}
								break;
						}
					}
				}
			}

			foreach ($authenticated as $id => $status) {
				if (!isset($disconnect[$id]) && !$authenticated[$id] && $timeouts[$id] < microtime(true)) { //Timeout
					$disconnect[$id] = $clients[$id];
				}
			}

			foreach ($disconnect as $id => $client) {
				$this->disconnectClient($client);
				unset($clients[$id], $authenticated[$id], $timeouts[$id]);
			}
		}

		foreach ($clients as $client) {
			$this->disconnectClient($client);
		}
	}

	private function disconnectClient(Socket $client) : void
	{
		socket_getpeername($client, $ip, $port);
		@socket_set_option($client, SOL_SOCKET, SO_LINGER, ["l_onoff" => 1, "l_linger" => 1]);
		@socket_shutdown($client, 2);
		@socket_set_block($client);
		@socket_read($client, 1);
		@socket_close($client);
		$this->logger->info("Disconnected client: /$ip:$port");
	}

	public function getThreadName() : string
	{
		return "RCON";
	}
}
