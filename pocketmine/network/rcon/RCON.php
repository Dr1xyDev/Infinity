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

/**
 * Implementation of the Source RCON Protocol to allow remote console commands
 * Source: https://developer.valvesoftware.com/wiki/Source_RCON_Protocol
 *
 * Implementation of the GeniRCON Protocol to allow full remote console access
 * Source: https://github.com/iTXTech/GeniRCON
 */
namespace pocketmine\network\rcon;

use pocketmine\command\RemoteConsoleCommandSender;
use pocketmine\event\server\RemoteServerCommandEvent;
use pocketmine\utils\Utils;
use pocketmine\Server;

class RCON{
	const PROTOCOL_VERSION = 3;

	/** @var Server */
	private $server;
	/** @var string */
	private $interface;
	/** @var int */
	private $port;
	private $password;
	/** @var RCONInstance[] */
	private $workers = [];
	/** @var int */
	private $threads = 0;
	private $clientsPerThread;

	public function __construct(Server $server, $password, $port = 19132, $interface = "0.0.0.0", $threads = 1, $clientsPerThread = 50){
		$this->server = $server;
		$this->workers = [];
		$this->password = (string) $password;
		$this->interface = (string) $interface;
		$this->port = (int) $port;
		$this->server->getLogger()->info("Starting remote control listener");
		if($this->password === ""){
			$this->server->getLogger()->critical("RCON can't be started: Empty password");

			return;
		}
		$this->threads = (int) max(1, $threads);
		$this->clientsPerThread = (int) max(1, $clientsPerThread);

		for($n = 0; $n < $this->threads; ++$n){
			$this->workers[$n] = new RCONInstance($this->server->getLogger(), $this->interface, $this->port, $this->password, $this->clientsPerThread);
		}

		//wait for the threads to bind their sockets
		$deadline = microtime(true) + 5;
		$boundOk = true;
		foreach($this->workers as $worker){
			while(!$worker->bound and !$worker->bindError and microtime(true) < $deadline){
				$worker->synchronized(function(){
					$this->wait(50000);
				});
			}
			if($worker->bindError){
				$boundOk = false;
				$this->server->getLogger()->critical("RCON can't be started: " . $worker->bindError);
			}
		}
		if(!$boundOk){
			$this->stop();
			$this->threads = 0;
			return;
		}
		$this->server->getLogger()->info("RCON running on $this->interface:$this->port");
	}

	public function stop(){
		for($n = 0; $n < $this->threads; ++$n){
			if(isset($this->workers[$n])){
				$this->workers[$n]->close();
				$this->workers[$n]->quit();
			}
		}
		$this->threads = 0;
	}

	public function check(){
		$d = Utils::getRealMemoryUsage();

		$u = Utils::getMemoryUsage(true);
		$usage = round(($u[0] / 1024) / 1024, 2) . "/" . round(($d[0] / 1024) / 1024, 2) . "/" . round(($u[1] / 1024) / 1024, 2) . "/" . round(($u[2] / 1024) / 1024, 2) . " MB @ " . Utils::getThreadCount() . " threads";
		$serverStatus = serialize([
			"online" => count($this->server->getOnlinePlayers()),
			"max" => $this->server->getMaxPlayers(),
			"upload" => round($this->server->getNetwork()->getUpload() / 1024, 2),
			"download" => round($this->server->getNetwork()->getDownload() / 1024, 2),
			"tps" => $this->server->getTicksPerSecondAverage(),
			"load" => $this->server->getTickUsageAverage(),
			"usage" => $usage
		]);
		for($n = 0; $n < $this->threads; ++$n){
			if(!isset($this->workers[$n])){
				continue;
			}
			$worker = $this->workers[$n];
			if(!$worker->isTerminated()){
				$worker->serverStatus = $serverStatus;
			}
			if($worker->isTerminated() === true){
				$this->workers[$n] = new RCONInstance($this->server->getLogger(), $this->interface, $this->port, $this->password, $this->clientsPerThread);
			}elseif($worker->isWaiting()){
				if($worker->response !== ""){
					$this->server->getLogger()->info($worker->response);
					$worker->synchronized(function(){
						$this->notify();
					});
				}else{

					$response = new RemoteConsoleCommandSender();
					$command = $worker->cmd;

					$this->server->getPluginManager()->callEvent($ev = new RemoteServerCommandEvent($response, $command));

					if(!$ev->isCancelled()){
						$this->server->dispatchCommand($ev->getSender(), $ev->getCommand());
					}

					$worker->response = $response->getMessage();
					$worker->synchronized(function(){
						$this->notify();
					});
				}
			}
		}
	}

}
