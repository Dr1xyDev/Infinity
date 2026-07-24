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


class GlowingObsidian extends Solid implements SolidLight{

	protected $id = self::GLOWING_OBSIDIAN;

	public function __construct($meta = 0){
		$this->meta = (int) $meta;
	}

	public function getName() : string{
		return "Glowing Obsidian";
	}

	public function getLightLevel(){
		return 12;
	}

}