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

use pocketmine\level\format\FullChunk;

abstract class ChunkEvent extends LevelEvent{
	/** @var FullChunk */
	private $chunk;

	/**
	 * @param FullChunk $chunk
	 */
	public function __construct(FullChunk $chunk){
		parent::__construct($chunk->getProvider()->getLevel());
		$this->chunk = $chunk;
	}

	/**
	 * @return FullChunk
	 */
	public function getChunk(){
		return $this->chunk;
	}
}