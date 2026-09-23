<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
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

namespace pocketmine\command;

use pmmp\thread\Thread as PmmpThread;

use pocketmine\thread\Thread;
use pocketmine\utils\MainLogger;
use pocketmine\utils\Utils;

class CommandReader extends Thread{
	private $readline;
	/** @var \pmmp\thread\ThreadSafeArray */
	protected $buffer;
	private $shutdown = false;
	private $stdinId = -1;
	/** @var MainLogger */
	private $logger;

	public function __construct($logger){
		$stdin = $this->getStdinResource();
		unset($stdin);
		$opts = getopt("", ["disable-readline"]);
		if(extension_loaded("readline") && !isset($opts["disable-readline"]) && (!function_exists("posix_isatty") || posix_isatty($this->getStdinResource()))){
			$this->readline = true;
		}else{
			$this->readline = false;
		}
		$this->logger = $logger;
		$this->buffer = new \pmmp\thread\ThreadSafeArray;
		$this->start(PmmpThread::INHERIT_ALL);
	}

	public function shutdown(){
		$this->shutdown = true;
	}

	private function readline_callback($line){
		if($line !== ""){
			$this->buffer[] = $line;
			readline_add_history($line);
		}
	}

	private function readLine(){
		if(!$this->readline){
			$line = trim((string) fgets($this->getStdinResource()));
			if($line !== ""){
				$this->buffer[] = $line;
			}
		}else{
			readline_callback_read_char();
		}
	}

	/**
	 * Reads a line from console, if available. Returns null if not available
	 *
	 * @return string|null
	 */
	public function getLine(){
		if($this->buffer->count() !== 0){
			return $this->buffer->shift();
		}

		return null;
	}

	public function quit() : void{
		$this->shutdown();
		// Windows sucks
		if(Utils::getOS() !== "win"){
			parent::quit();
		}
	}

	/**
	 * Returns an open stdin stream resource, re-opening it if necessary.
	 * The resource is kept alive by the caller until used.
	 */
	private function getStdinResource(){
		foreach(get_resources() as $res){
			if((int) $res === $this->stdinId and get_resource_type($res) === "stream"){
				return $res;
			}
		}
		$stdin = fopen("php://stdin", "r");
		if($stdin === false){
			return false;
		}
		$this->stdinId = (int) $stdin;
		return $stdin;
	}

		public function run() : void{
		if($this->readline){
			readline_callback_handler_install("Genisys> ", [$this, "readline_callback"]);
			$this->logger->setConsoleCallback("readline_redisplay");
		}

		while(!$this->shutdown){
			$stdin = $this->getStdinResource();
			if($stdin === false){
				break;
			}
			$r = [$stdin];
			$w = null;
			$e = null;
			if(stream_select($r, $w, $e, 0, 200000) > 0){
				// PHP on Windows sucks
				if(feof($stdin)){
					if(Utils::getOS() == "win"){
						$stdin = fopen("php://stdin", "r");
						if($stdin === false){
							break;
						}
						$this->stdinId = (int) $stdin;
						unset($stdin);
					}else{
						break;
					}
				}else{
					$this->readLine();
				}
			}
		}

		if($this->readline){
			$this->logger->setConsoleCallback(null);
			readline_callback_handler_remove();
		}
	}

	public function getThreadName(){
		return "Console";
	}
}
