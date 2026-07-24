<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

namespace pocketmine\utils;

class Range{
	public $minValue;
	public $maxValue;

	public function __construct(int $min, int $max){
		$this->minValue = $min;
		$this->maxValue = $max;
	}

	public function isInRange(int $v) : bool{
		return $v >= $this->minValue && $v <= $this->maxValue;
	}
}