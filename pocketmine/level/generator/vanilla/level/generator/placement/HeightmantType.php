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

namespace pocketmine\level\generator\vanilla\level\generator\placement;

use pocketmine\level\generator\vanilla\block\Air;
use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\BlueIce;
use pocketmine\level\generator\vanilla\block\Ice;
use pocketmine\level\generator\vanilla\block\Leaves;
use pocketmine\level\generator\vanilla\block\PackedIce;
use pocketmine\level\generator\vanilla\block\SnowLayer;
use pocketmine\level\generator\vanilla\block\TallGrass;
use pocketmine\level\generator\vanilla\block\Water;
use pocketmine\level\generator\vanilla\level\ChunkManager;
use pocketmine\level\generator\vanilla\level\format\Chunk;

enum HeightmantType {
	case MOTION;
	case SOLID;
	case OCEAN_SOLID;

	public function getHighestWorkableBlock(ChunkManager $level, int $x, int $z) : int{
		$highestBlock = $level->getChunk($x >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE)?->getHighestBlockAt($x & Chunk::COORD_MASK, $z & Chunk::COORD_MASK);
		if ($highestBlock === null) {
			return -1;
		}

		for ($y = $highestBlock; $y >= 0; --$y) {
			if (!$this->hasBlock($level->getBlockAt($x, $y, $z))) {
				return $y + 1;
			}
		}

		return -1;
	}

	public function hasBlock(Block $block) : bool {
		if ($this === self::MOTION) {
			return $block instanceof Air;
		} elseif ($this === self::SOLID) {
			return
				$block instanceof Air ||
				$block instanceof Leaves ||
				$block instanceof SnowLayer ||
				$block instanceof TallGrass;
		} elseif ($this === self::OCEAN_SOLID) {
			return
				$block instanceof Air ||
				$block instanceof Water ||
				$block instanceof Ice ||
				$block instanceof PackedIce ||
				$block instanceof BlueIce;
		}

		return true;
	}
}
