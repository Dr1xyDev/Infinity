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

class PlayerCommandPreprocessEvent extends PlayerEvent implements Cancellable{
	public static $handlerList = null;

	
	protected $message;

	
	public function __construct(Player $player, $message){
		$this->player = $player;
		$this->message = $message;
	}

	
	public function getMessage(){
		return $this->message;
	}

	
	public function setMessage($message){
		$this->message = $message;
	}

	
	public function setPlayer(Player $player){
		$this->player = $player;
	}

}