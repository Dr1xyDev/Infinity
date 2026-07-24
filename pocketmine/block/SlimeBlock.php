<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\Player;

class SlimeBlock extends Solid{

	protected $id = self::SLIME_BLOCK;

	public function __construct($meta = 15){
		$this->meta = (int) $meta;
	}

	public function hasEntityCollision(){
		return true;
	}

	public function getHardness() {
		return 0;
	}

	public function getName() : string{
		return "Slime Block";
	}
}
