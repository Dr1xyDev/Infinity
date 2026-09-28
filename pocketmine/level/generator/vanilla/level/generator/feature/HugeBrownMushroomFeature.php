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

use pocketmine\level\generator\vanilla\level\ChunkManager;
use pocketmine\level\generator\vanilla\level\generator\feature\configurations\HugeMushroomFeatureConfiguration;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\Random;

class HugeBrownMushroomFeature extends AbstractHugeMushroomFeature {

	protected function makeCap(
		ChunkManager                     $level,
		Random                           $random,
		Vector3                          $origin,
		int                              $treeHeight,
		HugeMushroomFeatureConfiguration $config
	) : void {
		$radius = $config->foliageRadius;

		for ($dx = -$radius; $dx <= $radius; $dx++) {
			for ($dz = -$radius; $dz <= $radius; $dz++) {
				$minX = $dx == -$radius;
				$maxX = $dx == $radius;
				$minZ = $dz == -$radius;
				$maxZ = $dz == $radius;
				$xEdge = $minX || $maxX;
				$zEdge = $minZ || $maxZ;
				if (!$xEdge || !$zEdge) {
					$blockPos = $origin->add($dx, $treeHeight, $dz);
					$state = $config->capProvider->getState($random, $origin);
					$west = $minX || $zEdge && $dx == 1 - $radius;
					$east = $maxX || $zEdge && $dx == $radius - 1;
					$north = $minZ || $xEdge && $dz == 1 - $radius;
					$south = $maxZ || $xEdge && $dz == $radius - 1;

					if ($west && !$east && !$north && !$south) {
						$state->setDamage(4); // WEST
					} elseif (!$west && $east && !$north && !$south) {
						$state->setDamage(6); // EAST
					} elseif (!$west && !$east && $north && !$south) {
						$state->setDamage(2); // NORTH
					} elseif (!$west && !$east && !$north && $south) {
						$state->setDamage(8); // SOUTH
					} elseif ($west && !$east && $north && !$south) {
						$state->setDamage(1); // NORTH_WEST
					} elseif (!$west && $east && $north && !$south) {
						$state->setDamage(3); // NORTH_EAST
					} elseif ($west && !$east && !$north && $south) {
						$state->setDamage(7); // SOUTH_WEST
					} elseif (!$west && $east && !$north && $south) {
						$state->setDamage(9); // SOUTH_EAST
					} else {
						$state->setDamage(5); // CENTER
					}

					$this->placeMushroomBlock($level, $blockPos, $state);
				}
			}
		}
	}

	protected function getTreeRadiusForHeight(int $trunkHeight, int $treeHeight, int $leafRadius, int $yo) : int {
		return $yo <= 3 ? 0 : $leafRadius;
	}
}
