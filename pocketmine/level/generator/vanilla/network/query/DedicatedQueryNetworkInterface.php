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

namespace pocketmine\level\generator\vanilla\network\query;

use pocketmine\level\generator\vanilla\network\AdvancedNetworkInterface;
use pocketmine\level\generator\vanilla\network\Network;

use function preg_match;
use function socket_bind;
use function socket_close;
use function socket_create;
use function socket_last_error;
use function socket_recvfrom;
use function socket_select;
use function socket_sendto;
use function socket_set_nonblock;
use function socket_strerror;
use function strlen;
use function time;
use function trim;

use const AF_INET;
use const PHP_INT_MAX;
use const SOCK_DGRAM;
use const SOCKET_EADDRINUSE;
use const SOCKET_ECONNRESET;
use const SOCKET_EWOULDBLOCK;
use const SOL_UDP;

/**
 * This is a supplementary network interface to maintain Query functionality when the RakLibInterface is not registered.
 *
 * Normally, Query runs on the same port as RakLib does, so Query handles packets coming in on RakLib's socket instead
 * of using its own interface.
 *
 * However, it's necessary to have a separate interface for the cases where the RakLib interface is either not registered
 * or running on a different port than Query.
 */
final class DedicatedQueryNetworkInterface implements AdvancedNetworkInterface
{
	private \Socket $socket;
	private Network $network;
	/**
	 * @var int[] address => timeout time
	 * @phpstan-var array<string, int>
	 */
	private array $blockedIps = [];
	/** @var string[] */
	private array $rawPacketPatterns = [];

	public function __construct(
		private string $ip,
		private int $port,
		private \Logger $logger
	) {
		$socket = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
		if ($socket === false) {
			throw new \RuntimeException("Failed to create socket");
		}
		$this->socket = $socket;
	}

	public function start() : void
	{
		if (!@socket_bind($this->socket, $this->ip, $this->port)) {
			$error = socket_last_error($this->socket);
			if ($error === SOCKET_EADDRINUSE) { //platform error messages aren't consistent
				throw new \RuntimeException("Failed to bind socket: Something else is already running on $this->ip $this->port", $error);
			}
			throw new \RuntimeException("Failed to bind to $this->ip $this->port: " . trim(socket_strerror($error)), $error);
		}
		socket_set_nonblock($this->socket);
		$this->logger->info("Running on $this->ip $this->port");
	}

	public function setName(string $name) : void
	{
		//NOOP
	}

	public function tick() : void
	{
		$r = [$this->socket];
		$w = null;
		$e = null;
		if (@socket_select($r, $w, $e, 0, 0) === 1) {
			$address = "";
			$port = 0;
			$buffer = "";
			while (true) {
				$bytes = @socket_recvfrom($this->socket, $buffer, 65535, 0, $address, $port);
				if ($bytes !== false) {
					if (isset($this->blockedIps[$address]) && $this->blockedIps[$address] > time()) {
						$this->logger->debug("Dropped packet from banned address $address");
						continue;
					}
					foreach ($this->rawPacketPatterns as $pattern) {
						if (preg_match($pattern, $buffer) === 1) {
							$this->network->processRawPacket($this, $address, $port, $buffer);
							break;
						}
					}
				} else {
					$errno = socket_last_error($this->socket);
					if ($errno === SOCKET_EWOULDBLOCK) {
						break;
					}
					if ($errno !== SOCKET_ECONNRESET) { //remote peer disappeared unexpectedly, this might spam like crazy so we don't log it
						$this->logger->debug("Failed to recv (errno $errno): " . trim(socket_strerror($errno)));
					}
				}
			}
		}
	}

	public function blockAddress(string $address, int $timeout = 300) : void
	{
		$this->blockedIps[$address] = $timeout > 0 ? time() + $timeout : PHP_INT_MAX;
	}

	public function unblockAddress(string $address) : void
	{
		unset($this->blockedIps[$address]);
	}

	public function setNetwork(Network $network) : void
	{
		$this->network = $network;
	}

	public function sendRawPacket(string $address, int $port, string $payload) : void
	{
		if (@socket_sendto($this->socket, $payload, strlen($payload), 0, $address, $port) === false) {
			$errno = socket_last_error($this->socket);
			throw new \RuntimeException("Failed to send to $address $port (errno $errno): " . trim(socket_strerror($errno)), $errno);
		}
	}

	public function addRawPacketFilter(string $regex) : void
	{
		$this->rawPacketPatterns[] = $regex;
	}

	public function shutdown() : void
	{
		@socket_close($this->socket);
	}
}
