<?php

/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.2 Release
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\network\rcon;

use pocketmine\thread\Thread;
use pocketmine\utils\Binary;
use pocketmine\utils\MainLogger;

class RCONInstance extends Thread{
	public $stop;
	public $cmd;
	public $response;
	private $socket;
	private $password;
	private $maxClients;
	private $waiting;

	
	private $logger;

	public $serverStatus;

	public function isWaiting(){
		return $this->waiting === true;
	}

	public function __construct($logger, $socket, $password, $maxClients = 50){
		$this->logger = $logger;
		$this->stop = false;
		$this->cmd = "";
		$this->response = "";
		$this->socket = $socket;
		$this->password = $password;
		$this->maxClients = (int) $maxClients;
		for($n = 0; $n < $this->maxClients; ++$n){
			$this->{"client" . $n} = null;
			$this->{"status" . $n} = 0;
			$this->{"timeout" . $n} = 0;
		}

		$this->start();
	}

	private function writePacket($client, $requestID, $packetType, $payload){
		$pk = Binary::writeLInt((int) $requestID)
			. Binary::writeLInt((int) $packetType)
			. $payload
			. "\x00\x00"; 
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
		$payload = rtrim(socket_read($client, $size + 2)); 
		return true;
	}

	public function close(){
		$this->stop = true;
	}

	public function onRun() : void{

		while($this->stop !== true){
			$this->synchronized(function(){
				$this->wait(2000);
			});
			$r = [$socket = $this->socket];
			$w = null;
			$e = null;
			if(socket_select($r, $w, $e, 0) === 1){
				if(($client = socket_accept($this->socket)) !== false){
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
						try{
							socket_close($client);
						}catch(\Throwable $e){
							//the client may have already closed the connection; best-effort cleanup
						}
					}
				}
			}

			for($n = 0; $n < $this->maxClients; ++$n){
				$client = $this->{"client" . $n};
				if($client !== null){
					if($this->{"status" . $n} !== -1 and $this->stop !== true){
						if($this->{"status" . $n} === 0 and $this->{"timeout" . $n} < microtime(true)){ 
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
							case 9: 
								if($this->{"status" . $n} !== 1){
									$this->{"status" . $n} = -1;
									continue 2;
								}
								$this->writePacket($client, $requestID, 0, RCON::PROTOCOL_VERSION);
								$this->response = "";

								if($payload == RCON::PROTOCOL_VERSION) $this->logger->setSendMsg(true); 
								break;
							case 4: 
								if($this->{"status" . $n} !== 1){
									$this->{"status" . $n} = -1;
									continue 2;
								}
								$res = (array) [
									"serverStatus" => unserialize($this->serverStatus),
									"logger" => str_replace("\n", "\r\n", trim($this->logger->getMessages()))
								];
								$this->writePacket($client, $requestID, 0, serialize($res));
								$this->response = "";
								break;
							case 3: 
								if($this->{"status" . $n} !== 0){
									$this->{"status" . $n} = -1;
									continue 2;
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
									continue 2;
								}
								break;
							case 2: 
								if($this->{"status" . $n} !== 1){
									$this->{"status" . $n} = -1;
									continue 2;
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
						try{
							socket_close($client);
						}catch(\Throwable $e){
							//the client may have already been closed (e.g. by the OS on connection reset);
							//this is a best-effort cleanup, so failing to close an already-closed socket is fine
						}
						$this->{"client" . $n} = null;
						$this->{"status" . $n} = 0;
						$this->{"timeout" . $n} = 0;
					}
				}
			}
		}
	}
}
