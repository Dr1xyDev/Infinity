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

/**
 * Implementation of the Source RCON Protocol to allow remote console commands
 * Source: https://developer.valvesoftware.com/wiki/Source_RCON_Protocol
 */

namespace pocketmine\level\generator\vanilla\network\rcon;

use InvalidArgumentException;
use pocketmine\level\generator\vanilla\command\RemoteConsoleCommandSender;
use pocketmine\level\generator\vanilla\event\server\RemoteServerCommandEvent;
use pocketmine\Server;
use pocketmine\level\generator\vanilla\snooze\SleeperNotifier;
use pocketmine\level\generator\vanilla\utils\TextFormat;
use RuntimeException;
use Socket;

use function max;
use function socket_bind;
use function socket_close;
use function socket_create;
use function socket_create_pair;
use function socket_getsockname;
use function socket_last_error;
use function socket_listen;
use function socket_set_block;
use function socket_set_option;
use function socket_strerror;
use function socket_write;
use function trim;

use const AF_INET;
use const AF_UNIX;
use const SO_REUSEADDR;
use const SOCK_STREAM;
use const SOCKET_ENOPROTOOPT;
use const SOCKET_EPROTONOSUPPORT;
use const SOL_SOCKET;
use const SOL_TCP;

class RCON
{
	private Server $server;
	private Socket $socket;

	private RCONInstance $instance;

	private Socket $ipcMainSocket;
	private Socket $ipcThreadSocket;

	public function __construct(Server $server, string $password, int $port = 19132, string $interface = "0.0.0.0", int $maxClients = 50)
	{
		$this->server = $server;
		$this->server->getLogger()->info("Starting remote control listener");
		if ($password === "") {
			throw new InvalidArgumentException("Empty password");
		}

		$socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if ($socket === false) {
			throw new RuntimeException("Failed to create socket:" . trim(socket_strerror(socket_last_error())));
		}
		$this->socket = $socket;

		if (!socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1)) {
			throw new RuntimeException("Unable to set option on socket: " . trim(socket_strerror(socket_last_error())));
		}

		if (!@socket_bind($this->socket, $interface, $port) || !@socket_listen($this->socket, 5)) {
			throw new RuntimeException(trim(socket_strerror(socket_last_error())));
		}

		socket_set_block($this->socket);

		$ret = @socket_create_pair(AF_UNIX, SOCK_STREAM, 0, $ipc);
		if (!$ret) {
			$err = socket_last_error();
			if (($err !== SOCKET_EPROTONOSUPPORT && $err !== SOCKET_ENOPROTOOPT) || !@socket_create_pair(AF_INET, SOCK_STREAM, 0, $ipc)) {
				throw new RuntimeException(trim(socket_strerror(socket_last_error())));
			}
		}

		[$this->ipcMainSocket, $this->ipcThreadSocket] = $ipc;

		$notifier = new SleeperNotifier();
		$this->server->getTickSleeper()->addNotifier($notifier, function () : void {
			$this->check();
		});
		$this->instance = new RCONInstance($this->socket, $password, max(1, $maxClients), $this->server->getLogger(), $this->ipcThreadSocket, $notifier);
		$this->instance->start();

		socket_getsockname($this->socket, $addr, $port);
		$this->server->getLogger()->info("RCON running on $addr:$port");
	}

	public function stop() : void
	{
		$this->instance->close();
		socket_write($this->ipcMainSocket, "\x00"); //make select() return
		$this->instance->quit();

		@socket_close($this->socket);
		@socket_close($this->ipcMainSocket);
		@socket_close($this->ipcThreadSocket);
	}

	public function check() : void
	{
		$response = new RemoteConsoleCommandSender();
		$command = $this->instance->cmd;

		$ev = new RemoteServerCommandEvent($response, $command);
		$ev->call();

		if (!$ev->isCancelled()) {
			$this->server->dispatchCommand($ev->getSender(), $ev->getCommand());
		}

		$this->instance->response = TextFormat::clean($response->getMessage());
		$this->instance->synchronized(function (RCONInstance $thread) : void {
			$thread->notify();
		}, $this->instance);
	}
}
