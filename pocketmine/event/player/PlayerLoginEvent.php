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

class PlayerLoginEvent extends PlayerEvent implements Cancellable{
	public static $handlerList = null;

	
	protected $kickMessage;

	public function __construct(Player $player, $kickMessage){
		$this->player = $player;
		$this->kickMessage = $kickMessage;
	}

	public function setKickMessage($kickMessage){
		$this->kickMessage = $kickMessage;
	}

	public function getKickMessage(){
		return $this->kickMessage;
	}

}