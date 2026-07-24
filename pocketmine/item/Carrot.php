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

class Carrot extends Food{
	public function __construct($meta = 0, $count = 1){
		$this->block = Block::get(Item::CARROT_BLOCK);
		parent::__construct(self::CARROT, 0, $count, "Carrot");
	}

	public function getFoodRestore() : int{
		return 3;
	}

	public function getSaturationRestore() : float{
		return 4.8;
	}
}
