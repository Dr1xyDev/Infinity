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

use pocketmine\level\Level;

class StillWater extends Water{

	protected $id = self::STILL_WATER;

	public function onUpdate($type){
		if($type == Level::BLOCK_UPDATE_NORMAL){
			parent::onUpdate($type);
		}
	}

	public function getName() : string{
		return "Still Water";
	}
}