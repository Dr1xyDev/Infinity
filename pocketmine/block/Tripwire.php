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

use pocketmine\item\Tool;

class Tripwire extends Transparent{

	protected $id = self::TRIPWIRE;

	public function __construct($meta = 0){
		$this->meta = (int) $meta;
	}

	public function getName() : string{
		return "Tripwire";
	}

	public function getToolType(){
		return Tool::TYPE_SHEARS;
	}

	public function getHardness(){
		return 0;
	}

	public function getResistance(){
		return 0;
	}

}
