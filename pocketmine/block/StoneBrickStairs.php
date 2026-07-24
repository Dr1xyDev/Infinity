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

class StoneBrickStairs extends Stair{

	protected $id = self::STONE_BRICK_STAIRS;

	public function __construct($meta = 0){
		$this->meta = (int) $meta;
	}

	public function getToolType(){
		return Tool::TYPE_PICKAXE;
	}

	public function getHardness() {
		return 1.5;
	}

	public function getName() : string{
		return "Stone Brick Stairs";
	}

}