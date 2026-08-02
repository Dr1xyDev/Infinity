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

use pocketmine\event\Cancellable;
use pocketmine\Player;

class PlayerKickEvent extends PlayerEvent implements Cancellable{
	public static $handlerList = null;

	
	protected $quitMessage;

	
	protected $reason;

	public function __construct(Player $player, $reason, $quitMessage){
		$this->player = $player;
		$this->quitMessage = $quitMessage;
		$this->reason = $reason;
	}

	public function getReason(){
		return $this->reason;
	}

	public function setQuitMessage($quitMessage){
		$this->quitMessage = $quitMessage;
	}

	public function getQuitMessage(){
		return $this->quitMessage;
	}

}