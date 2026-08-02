<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\event\server;

use pocketmine\command\CommandSender;
use pocketmine\event\Cancellable;

class ServerCommandEvent extends ServerEvent implements Cancellable{
	public static $handlerList = null;

	
	protected $command;

	
	protected $sender;

	
	public function __construct(CommandSender $sender, $command){
		$this->sender = $sender;
		$this->command = $command;
	}

	
	public function getSender(){
		return $this->sender;
	}

	
	public function getCommand(){
		return $this->command;
	}

	
	public function setCommand($command){
		$this->command = $command;
	}

}