<?php

/*
 *
 *   _____       _                          _
 *  / ____|     | |                        (_)
 * | (___  _   _| |__  _ __ ___   __ _ _ __ _ _ __   ___
 *  \___ \| | | | '_ \| '_ ` _ \ / _` | '__| | '_ \ / _ \
 *  ____) | |_| | |_) | | | | | | (_| | |  | | | | |  __/
 * |_____/ \__,_|_.__/|_| |_| |_|\__,_|_|  |_|_| |_|\___|
 *
 * This program is private software. No license required.
 * Publication of this program is forbidden and will be punished.
 *
 * @author SEMENNEJO
 * @link vk.com/vk.snikers && t.me/semennejo
 *
 *
 */

declare(strict_types=1);

namespace pocketmine\level\generator\vanilla\level\utils;

use pocketmine\level\generator\vanilla\level\ChunkManager;
use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\level\format\EmptySubChunk;
use pocketmine\level\generator\vanilla\level\format\SubChunkInterface;

class SubChunkIteratorManager
{
	/** @var ChunkManager */
	public $level;

	/** @var Chunk|null */
	public $currentChunk;
	/** @var SubChunkInterface|null */
	public $currentSubChunk;

	/** @var int */
	protected $currentX;
	/** @var int */
	protected $currentY;
	/** @var int */
	protected $currentZ;
	/** @var bool */
	protected $allocateEmptySubs = true;

	public function __construct(ChunkManager $level, bool $allocateEmptySubs = true)
	{
		$this->level = $level;
		$this->allocateEmptySubs = $allocateEmptySubs;
	}

	public function moveTo(int $x, int $y, int $z) : bool
	{
		if ($this->currentChunk === null || $this->currentX !== ($x >> Chunk::COORD_BIT_SIZE) || $this->currentZ !== ($z >> Chunk::COORD_BIT_SIZE)) {
			$this->currentX = $x >> Chunk::COORD_BIT_SIZE;
			$this->currentZ = $z >> Chunk::COORD_BIT_SIZE;
			$this->currentSubChunk = null;

			$this->currentChunk = $this->level->getChunk($this->currentX, $this->currentZ);
			if ($this->currentChunk === null) {
				return false;
			}
		}

		if ($this->currentSubChunk === null || $this->currentY !== ($y >> 4)) {
			$this->currentY = $y >> 4;

			$this->currentSubChunk = $this->currentChunk->getSubChunk($y >> 4, $this->allocateEmptySubs);
			if ($this->currentSubChunk instanceof EmptySubChunk) {
				return false;
			}
		}

		return true;
	}

	public function invalidate() : void
	{
		$this->currentChunk = null;
		$this->currentSubChunk = null;
	}
}
