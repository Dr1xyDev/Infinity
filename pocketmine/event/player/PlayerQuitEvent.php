<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\event\player;

use pocketmine\Player;

class PlayerQuitEvent extends PlayerEvent{
	public static $handlerList = null;

	
	protected $quitMessage;
	protected $autoSave = true;

	public function __construct(Player $player, $quitMessage, $autoSave = true){
		$this->player = $player;
		$this->quitMessage = $quitMessage;
		$this->autoSave = $autoSave;
	}

	public function setQuitMessage($quitMessage){
		$this->quitMessage = $quitMessage;
	}

	public function getQuitMessage(){
		return $this->quitMessage;
	}

	public function getAutoSave(){
		return $this->autoSave;
	}

	public function setAutoSave($value = true){
		$this->autoSave = (bool) $value;
	}

}