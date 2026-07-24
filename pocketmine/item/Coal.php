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


class Coal extends Item{
	const NORMAL = 0;
	const CHARCOAL = 1;
	
	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::COAL, $meta, $count, "Coal");
		if($this->meta === 1){
			$this->name = "Charcoal";
		}
	}

}