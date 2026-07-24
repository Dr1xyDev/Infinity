<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

namespace pocketmine\item;

use pocketmine\block\Block;

class WoodenDoor extends Door{
	public function __construct($meta = 0, $count = 1){
		$this->block = Block::get(Item::WOODEN_DOOR_BLOCK);
		parent::__construct(self::WOODEN_DOOR, 0, $count, "Wooden Door");
	}
}