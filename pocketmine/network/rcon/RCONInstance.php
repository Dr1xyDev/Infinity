<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____  
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/ |  _ \ 
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/ 
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___/     |_|  |_|_| 
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 * 
 *
*/

namespace pocketmine\network\rcon;

use pocketmine\thread\Thread;
use pocketmine\utils\Binary;
use pocketmine\utils\MainLogger;

/**
 * RCON listener thread. The socket is created, bound and used entirely inside
 * the thread context, because pmmpthread does not allow raw socket resources
 * to be stored in ThreadSafe properties.
 */
class RCONInstance extends Thread{
	/** @var bool */
	public $stop;
	/** @var string */
	public $cmd;
	/** @var string */
	public $response;
	/** @var resource|null */
	private $socket = null;
	/** @var string */
	private $interface;
	/** @var int */
	private $port;
	private $password;
	private $maxClients;
	/** @var bool */
	private $waiting = false;

	/** @var MainLogger */
	private $logger;

	/** @var string */
	public $serverStatus = "";

	/** @var bool */
	public $bound = false;
	/** @var string */
	public $bindError = "";

	public function isWaiting(){
		return $this->waiting === true;
	}

	/**
	 * @param MainLogger $logger
	 */
	public function __construct($logger, string $interface, int $port, $password, $maxClients = 50){
		$this->logger = $logger;
		$this->stop = false;
		$this->cmd = "";
		$this->response = "";
		$this->interface = $interface;
		$this->port = $port;
		$this->password = $password;
		$this->maxClients = (int) $maxClients;
		for($n = 0; $n < $this->maxClients; ++$n){
			$this->{"client" . $n} = null;
			$this->{"status" . $n} = 0;
			$this->{"timeout" . $n} = 0;
		}

		$this->start();
	}

	private function bind() : bool{
		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if($this->socket === false){
			$this->bindError = "socket_create failed";
			return false;
		}
		socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
		if(!@socket_bind($this->socket, $this->interface, $this->port) or !@socket_listen($this->socket)){
			$this->bindError = socket_strerror(socket_last_error($this->socket));
			return false;
		}
		socket_set_block($this->socket);
		return true;
	}

	private function writePacket($client, $requestID, $packetType, $payload){
		$pk = Binary::writeLInt((int) $requestID)
			. Binary::writeLInt((int) $packetType)
			. $payload
			. "\x00\x00"; //Terminate payload and packet
		return socket_write($client, Binary::writeLInt(strlen($pk)) . $pk);
	}

	private function readPacket($client, &$size, &$requestID, &$packetType, &$payload){
		socket_set_nonblock($client);
		$d = @socket_read($client, 4);
		if($this->stop === true){
			return false;
		}elseif($d === false){
			return null;
		}elseif($d === "" or strlen($d) < 4){
			return false;
		}
		socket_set_block($client);
		$size = Binary::readLInt($d);
		if($size < 0 or $size > 65535){
			return false;
		}
		$requestID = Binary::readLInt(socket_read($client, 4));
		$packetType = Binary::readLInt(socket_read($client, 4));
		$payload = rtrim(socket_read($client, $size + 2)); //Strip two null bytes
		return true;
	}

	public function close(){
		$this->stop = true;
		$this->notify();
	}

	public function run() : void{
		if(!$this->bind()){
			$this->bound = false;
			$this->stop = true;
			return;
		}
		$this->bound = true;
		$this->synchronized(function(){
			$this->notify(); //wake up the main thread waiting for the bind result
		});

		while($this->stop !== true){
			$this->synchronized(function(){
				$this->wait(2000);
			});
			if($this->socket === null){
				break;
			}
			$r = [$this->socket];
			$w = null;
			$e = null;
			if(@socket_select($r, $w, $e, 0, 100000) === 1){
				if(($client = @socket_accept($this->socket)) !== false){
					socket_set_block($client);
					socket_set_option($client, SOL_SOCKET, SO_KEEPALIVE, 1);
					$done = false;
					for($n = 0; $n < $this->maxClients; ++$n){
						if($this->{"client" . $n} === null){
							$this->{"client" . $n} = $client;
							$this->{"status" . $n} = 0;
							$this->{"timeout" . $n} = microtime(true) + 5;
							$done = true;
							break;
						}
					}
					if($done === false){
						@socket_close($client);
					}
				}
			}

			for($n = 0; $n < $this->maxClients; ++$n){
				$client = $this->{"client" . $n};
				if($client !== null){
					if($this->{"status" . $n} !== -1 and $this->stop !== true){
						if($this->{"status" . $n} === 0 and $this->{"timeout" . $n} < microtime(true)){ //Timeout
							$this->{"status" . $n} = -1;
							continue;
						}
						$p = $this->readPacket($client, $size, $requestID, $packetType, $payload);
						if($p === false){
							$this->{"status" . $n} = -1;
							continue;
						}elseif($p === null){
							continue;
						}

						switch($packetType){
							case 9: //Protocol check
								if($this->{"status" . $n} !== 1){
									$this->{"status" . $n} = -1;
									continue;
								}
								$this->writePacket($client, $requestID, 0, RCON::PROTOCOL_VERSION);
								$this->response = "";

								if($payload == RCON::PROTOCOL_VERSION) $this->logger->setSendMsg(true); //GeniRCON output
								break;
							case 4: //Logger
								if($this->{"status" . $n} !== 1){
									$this->{"status" . $n} = -1;
									continue;
								}
								$res = (array) [
									"serverStatus" => unserialize($this->serverStatus),
									"logger" => str_replace("\n", "\r\n", trim($this->logger->getMessages()))
								];
								$this->writePacket($client, $requestID, 0, serialize($res));
								$this->response = "";
								break;
							case 3: //Login
								if($this->{"status" . $n} !== 0){
									$this->{"status" . $n} = -1;
									continue;
								}
								if($payload === $this->password){
									socket_getpeername($client, $addr, $port);
									$this->response = "[INFO] Successful Rcon connection from: /$addr:$port";
									$this->response = "";
									$this->writePacket($client, $requestID, 2, "");
									$this->{"status" . $n} = 1;
								}else{
									$this->{"status" . $n} = -1;
									$this->writePacket($client, -1, 2, "");
									continue;
								}
								break;
							case 2: //Command
								if($this->{"status" . $n} !== 1){
									$this->{"status" . $n} = -1;
									continue;
								}
								if(strlen($payload) > 0){
									$this->cmd = ltrim($payload);
									$this->synchronized(function(){
										$this->waiting = true;
										$this->wait();
									});
									$this->waiting = false;
									$this->writePacket($client, $requestID, 0, str_replace("\n", "\r\n", trim($this->response)));
									$this->response = "";
									$this->cmd = "";
								}
								break;
						}

					}else{
						@socket_set_option($client, SOL_SOCKET, SO_LINGER, ["l_onoff" => 1, "l_linger" => 1]);
						@socket_shutdown($client, 2);
						@socket_set_block($client);
						@socket_read($client, 1);
						@socket_close($client);
						$this->{"status" . $n} = 0;
						$this->{"client" . $n} = null;
					}
				}
			}
		}
		if($this->socket !== null){
			@socket_close($this->socket);
			$this->socket = null;
		}
		unset($this->cmd, $this->response, $this->stop);
	}

	public function getThreadName(){
		return "RCON";
	}
}
