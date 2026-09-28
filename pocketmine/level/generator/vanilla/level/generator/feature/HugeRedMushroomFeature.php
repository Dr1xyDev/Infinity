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

class HugeRedMushroomFeature extends AbstractHugeMushroomFeature {

	protected function makeCap(
		ChunkManager                     $level,
		Random                           $random,
		Vector3                          $origin,
		int                              $treeHeight,
		HugeMushroomFeatureConfiguration $config
	) : void {
		for ($dy = $treeHeight - 3; $dy <= $treeHeight; $dy++) {
			$radius = $dy < $treeHeight ? $config->foliageRadius : $config->foliageRadius - 1;
			$center = $config->foliageRadius - 2;

			for ($dx = -$radius; $dx <= $radius; $dx++) {
				for ($dz = -$radius; $dz <= $radius; $dz++) {
					$minX = $dx == -$radius;
					$maxX = $dx == $radius;
					$minZ = $dz == -$radius;
					$maxZ = $dz == $radius;
					$xEdge = $minX || $maxX;
					$zEdge = $minZ || $maxZ;
					if ($dy >= $treeHeight || $xEdge != $zEdge) {
						$blockPos = $origin->add($dx, $dy, $dz);
						$state = $config->capProvider->getState($random, $origin);
						$west = $dx < -$center;
						$east = $dx > $center;
						$north = $dz < -$center;
						$south = $dz > $center;

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
						} elseif (!$west && !$east && !$north && $south) {
							$state->setDamage(5); // CENTER
						} else {
							$state->setDamage(14); //ALL_OUTSIDE
						}

						$this->placeMushroomBlock($level, $blockPos, $state);
					}
				}
			}
		}
	}

	protected function getTreeRadiusForHeight(int $trunkHeight, int $treeHeight, int $leafRadius, int $yo) : int {
		$radius = 0;
		if ($yo < $treeHeight && $yo >= $treeHeight - 3) {
			$radius = $leafRadius;
		} elseif ($yo == $treeHeight) {
			$radius = $leafRadius;
		}

		return $radius;
	}
}
