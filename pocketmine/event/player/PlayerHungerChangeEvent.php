<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

namespace pocketmine\event\player;

use pocketmine\event\Cancellable;
use pocketmine\Player;

class PlayerHungerChangeEvent extends PlayerEvent implements Cancellable{
	public static $handlerList = null;
	
	public $data;

	public function __construct(Player $player, $data){
		$this->data = $data;
		$this->player = $player;
	}
	
	public function getData(){
		return $this->data;
	}
	
	public function setData($data){
		$this->data = $data;
	}

}
