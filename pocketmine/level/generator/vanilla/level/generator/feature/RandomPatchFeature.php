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

use pocketmine\level\generator\vanilla\block\Air;
use pocketmine\level\generator\vanilla\block\Water;
use pocketmine\level\generator\vanilla\level\generator\feature\configurations\BlockClusterConfiguration;
use pocketmine\level\generator\vanilla\math\Facing;
use function count;

class RandomPatchFeature extends Feature {

	public function __construct(
		public BlockClusterConfiguration $config
	){}

	public function place(FeaturePlaceContext $context) : bool{
		$level = $context->level();
		$origin = $context->origin();
		$random = $context->random();
		$config = $this->config;

		$blockState = $config->stateProvider->getState($random, $origin);
		$placed = 0;
		for ($i = 0; $i < $config->tryCount; ++$i) {
			$mutable = $origin->add(
				$random->nextBoundedInt($config->xSpread + 1) - $random->nextBoundedInt($config->xSpread + 1),
				$random->nextBoundedInt($config->ySpread + 1) - $random->nextBoundedInt($config->ySpread + 1),
				$random->nextBoundedInt($config->zSpread + 1) - $random->nextBoundedInt($config->zSpread + 1)
			);
			$blockPos1 = $mutable->down();

			$blockState1 = $level->getBlockAt($blockPos1->getFloorX(), $blockPos1->getFloorY(), $blockPos1->getFloorZ());
			$target = $level->getBlockAt($mutable->getFloorX(), $mutable->getFloorY(), $mutable->getFloorZ());
			$isValidPosition = $config->supportBlock->isValidPosition($level, $mutable);
			$isBlacklisted = false;
			$isWhitelisted = true;
			foreach ($config->blacklist as $blackBlock) {
				if ($blockState1->isSameType($blackBlock)) {
					$isBlacklisted = true;
					break;
				}
			}
			if (count($config->whitelist) !== 0) {
				$isWhitelisted = false;
				foreach ($config->whitelist as $whitelist) {
					if ($blockState1->isSameType($whitelist)) {
						$isWhitelisted = true;
						break;
					}
				}
			}
			$hasWater = false;
			if ($config->requiresWater) {
				foreach (Facing::HORIZONTAL as $face) {
					$sidePos = $blockPos1->getSide($face);
					$sideBlock = $level->getBlockAt($sidePos->getFloorX(), $sidePos->getFloorY(), $sidePos->getFloorZ());
					if ($sideBlock instanceof Water) {
						$hasWater = true;
						break;
					}
				}
			} else {
				$hasWater = true; // Not required, so true
			}

			if (($target instanceof Air || ($config->isReplaceable && $target->canBeReplaced())) &&
				$isWhitelisted &&
				$isValidPosition &&
				!$isBlacklisted &&
				$hasWater
			) {
				$config->blockPlacer->place($level, $mutable, clone $blockState, $random);
				$placed++;
			}
		}

		return $placed > 0;
	}
}
