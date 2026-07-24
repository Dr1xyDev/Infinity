<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

/**
 * Level related events
 */
namespace pocketmine\event\level;

use pocketmine\event\Event;
use pocketmine\level\Level;

abstract class LevelEvent extends Event{
	/** @var \pocketmine\level\Level */
	private $level;

	/**
	 * @param Level $level
	 */
	public function __construct(Level $level){
		$this->level = $level;
	}

	/**
	 * @return \pocketmine\level\Level
	 */
	public function getLevel(){
		return $this->level;
	}
}