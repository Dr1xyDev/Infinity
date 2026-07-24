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

class StillLava extends Lava{

	protected $id = self::STILL_LAVA;

	public function onUpdate($type){
		if($type == Level::BLOCK_UPDATE_NORMAL){
			parent::onUpdate($type);
		}
	}

	public function getName() : string{
		return "Still Lava";
	}

}