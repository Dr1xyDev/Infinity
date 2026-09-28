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

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\level\ChunkManager;
use pocketmine\level\generator\vanilla\level\generator\Generator;
use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\math\Vector3;

class PlacementContext {
	public function __construct(
		private ChunkManager $level,
		private Generator $generator
	){}

	public function getBlockState(Vector3 $pos) : Block {
		return $this->level->getBlockAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());
	}

	public function getMinY() : int {
		return Level::Y_MIN; //TODO:
	}

	public function getLevel() : ChunkManager {
		return $this->level;
	}

	public function getGenerator() : Generator {
		return $this->generator;
	}
}
