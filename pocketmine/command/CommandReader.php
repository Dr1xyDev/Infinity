<?php

/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.2 Release
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\command;

use pocketmine\thread\Thread;
use pocketmine\utils\MainLogger;
use pocketmine\utils\Utils;

class CommandReader extends Thread{
	private $readline;
	
	protected $buffer;
	private $shutdown = false;
	
	private $logger;

	public function __construct($logger){
		$this->logger = $logger;
		$this->buffer = new \pmmp\thread\ThreadSafeArray;
		$this->start();
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

	private function readLine($stdin){
		if(!$this->readline){
			$line = trim(fgets($stdin));
			if($line !== ""){
				$this->buffer[] = $line;
			}
		}else{
			readline_callback_read_char();
		}
	}

	
	public function getLine(){
		if($this->buffer->count() !== 0){
			return $this->buffer->shift();
		}

		return null;
	}

	public function quit() : void{
		$this->shutdown();
		
		if(Utils::getOS() !== "win"){
			parent::quit();
		}
	}

	public function onRun() : void{
		//resources (like this stream) can't be assigned to thread-safe instance properties,
		//so it's kept as a local variable within this method instead
		$stdin = fopen("php://stdin", "r");
		$opts = getopt("", ["disable-readline"]);
		if(extension_loaded("readline") && !isset($opts["disable-readline"]) && (!function_exists("posix_isatty") || posix_isatty($stdin))){
			$this->readline = true;
		}else{
			$this->readline = false;
		}

		if($this->readline){
			readline_callback_handler_install("Genisys> ", [$this, "readline_callback"]);
			$this->logger->setConsoleCallback("readline_redisplay");
		}

		while(!$this->shutdown){
			$r = [$stdin];
			$w = null;
			$e = null;
			if(stream_select($r, $w, $e, 0, 200000) > 0){
				
				if(feof($stdin)){
					if(Utils::getOS() == "win"){
						$stdin = fopen("php://stdin", "r");
						if(!is_resource($stdin)){
							break;
						}
					}else{
						break;
					}
				}
				$this->readLine($stdin);
			}
		}

		if($this->readline){
			$this->logger->setConsoleCallback(null);
			readline_callback_handler_remove();
		}
	}

	public function getThreadName() : string{
		return "Console";
	}
}
