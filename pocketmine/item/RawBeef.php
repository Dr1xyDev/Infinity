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

class RawBeef extends Food{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::RAW_BEEF, $meta, $count, "Raw Beef");
	}

	public function getFoodRestore() : int{
		return 3;
	}

	public function getSaturationRestore() : float{
		return 1.8;
	}
}

