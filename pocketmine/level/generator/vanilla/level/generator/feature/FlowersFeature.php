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

namespace pocketmine\level\generator\vanilla\level\generator\feature;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\block\Dirt;
use pocketmine\level\generator\vanilla\block\Farmland;
use pocketmine\level\generator\vanilla\block\Grass;
use pocketmine\level\generator\vanilla\block\Mud;
use pocketmine\level\generator\vanilla\block\Mycelium;
use pocketmine\level\generator\vanilla\block\Podzol;
use pocketmine\level\generator\vanilla\level\ChunkManager;
use pocketmine\level\generator\vanilla\level\generator\feature\configurations\BlockClusterConfiguration;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\Random;

class FlowersFeature extends Feature {

	public function __construct(
		public BlockClusterConfiguration $config
	){}

	public function place(FeaturePlaceContext $context) : bool{
		$level = $context->level();
		$origin = $context->origin();
		$random = $context->random();
		$config = $this->config;

		$blockState = $this->getFlowerToPlace($random, $origin, $config);
		$placed = 0;

		for($j = 0; $j < $this->getFlowerCount($config); ++$j) {
			$blockStateFromPos = $level->getBlockAt($origin->x, $origin->y, $origin->z);
			$blockPos = $this->getNearbyPos($random, $origin, $config);
			if (
				$blockStateFromPos->getId() === BlockIds::AIR &&
				$blockPos->getY() < $level->getWorldHeight() &&
				$this->canBeSupportedAt($level, $blockPos) &&
				$this->isValidPosition($level, $blockPos, $config)
			) {
				$level->setBlockAt($origin->x, $origin->y, $origin->z, $blockState);
				++$placed;
			}
		}

		return $placed > 0;
	}

	protected function canBeSupportedAt(ChunkManager $level, Vector3 $pos) : bool{
		$block = $level->getBlockAt($pos->x, $pos->y - 1, $pos->z);

		return
			$block instanceof Grass ||
			$block instanceof Dirt ||
			$block instanceof Mycelium ||
			$block instanceof Podzol ||
			$block instanceof Farmland ||
			$block instanceof Mud;
	}

	public function isValidPosition(ChunkManager $level, Vector3 $pos, BlockClusterConfiguration $config) : bool {
		$block = $level->getBlockAt($pos->x, $pos->y, $pos->z);

		$has = true;
		foreach ($config->blacklist as $blackBlock) {
			if ($blackBlock->isSameType($block)) {
				$has = false;
				break;
			}
		}

		return $has;
	}

	public function getFlowerCount(BlockClusterConfiguration $config) : int {
		return $config->tryCount;
	}

	public function getNearbyPos(Random $random, Vector3 $pos, BlockClusterConfiguration $config) : Vector3 {
		return $pos->add($random->nextBoundedInt($config->xSpread) - $random->nextBoundedInt($config->xSpread), $random->nextBoundedInt($config->ySpread) - $random->nextBoundedInt($config->ySpread), $random->nextBoundedInt($config->zSpread) - $random->nextBoundedInt($config->zSpread));
	}

   public function getFlowerToPlace(Random $random, Vector3 $pos) : Block {
	   return $this->config->stateProvider->getState($random, $pos);
   }
}
