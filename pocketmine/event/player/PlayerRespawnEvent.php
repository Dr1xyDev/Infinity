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

use pocketmine\level\Position;
use pocketmine\Player;

class PlayerRespawnEvent extends PlayerEvent{
	public static $handlerList = null;

	
	protected $position;

	
	public function __construct(Player $player, Position $position){
		$this->player = $player;
		$this->position = $position;
	}

	
	public function getRespawnPosition(){
		return $this->position;
	}

	
	public function setRespawnPosition(Position $position){
		$this->position = $position;
	}
}