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

namespace pocketmine\level\generator\vanilla\level\generator;

use pocketmine\level\generator\vanilla\block\Air;
use pocketmine\level\generator\vanilla\block\Grass;
use pocketmine\level\generator\vanilla\level\format\Chunk;

class VoidGenerator extends Generator {
	private Chunk $chunk;
	private ?Chunk $emptyChunk = null;

	public function getName() : string
	{
		return "void";
	}

	public function generateChunk(int $chunkX, int $chunkZ) : void{
		if ($this->emptyChunk === null) {
			$this->chunk = clone $this->level->getChunk($chunkX, $chunkZ);

			for ($Z = 0; $Z < 16; ++$Z) {
				for ($X = 0; $X < 16; ++$X) {
					$this->chunk->setBiomeId($X, $Z, 1);
					for ($y = 0; $y < 128; ++$y) {
						$this->chunk->setFullBlock($X, $y, $Z, (new Air())->getFullId());
					}
				}
			}

			if (0 >> Chunk::COORD_BIT_SIZE === $chunkX && 0 >> Chunk::COORD_BIT_SIZE === $chunkZ) {
				$this->chunk->setFullBlock(0, 62, 0, (new Grass())->getFullId());
			} else {
				$this->emptyChunk = clone $this->chunk;
			}
		} else {
			$this->chunk = clone $this->emptyChunk;
		}

		$chunk = clone $this->chunk;
		$chunk->setX($chunkX);
		$chunk->setZ($chunkZ);
		$this->level->setChunk($chunkX, $chunkZ, $chunk);
	}

	public function populateChunk(int $chunkX, int $chunkZ) : void{
		// NOOP
	}
}
