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

class GoldenCarrot extends Food{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::GOLDEN_CARROT, $meta, $count, "Golden Carrot");
	}

	public function getFoodRestore() : int{
		return 6;
	}

	public function getSaturationRestore() : float{
		return 14.4;
	}
}
