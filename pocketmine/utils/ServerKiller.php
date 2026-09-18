<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

namespace pocketmine\utils;

use pocketmine\thread\Thread;

class ServerKiller extends Thread{

	public $time;

	public function __construct($time = 15){
		$this->time = $time;
	}

	public function onRun() : void{
		$start = time() + 1;
		$this->synchronized(function(){
			$this->wait($this->time * 1000000);
		});
		if(time() - $start >= $this->time){
			echo "\nTook too long to stop, server was killed forcefully!\n";
			@\pocketmine\kill(getmypid());
		}
	}

	public function getThreadName() : string{
		return "Server Killer";
	}
}
