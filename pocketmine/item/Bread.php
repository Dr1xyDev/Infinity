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

class Bread extends Food{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::BREAD, $meta, $count, "Bread");
	}

	public function getFoodRestore() : int{
		return 5;
	}

	public function getSaturationRestore() : float{
		return 6;
	}
}

