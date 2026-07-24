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

class CookedPorkchop extends Food{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::COOKED_PORKCHOP, $meta, $count, "Cooked Porkchop");
	}

	public function getFoodRestore() : int{
		return 8;
	}

	public function getSaturationRestore() : float{
		return 12.8;
	}
}

