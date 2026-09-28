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

use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\level\generator\feature\configurations\BlockStateConfiguration;
use pocketmine\level\generator\vanilla\level\Level;

class BlockBlobFeature extends Feature {

	public function __construct(
		public BlockStateConfiguration $config
	){}

	public function place(FeaturePlaceContext $context) : bool{
		$level = $context->level();
		$origin = $context->origin();
		$random = $context->random();
		$config = $this->config;

		for (; $origin->getY() > Level::Y_MIN + 3; $origin = $origin->down()) {
			$down = $origin->down();
			$subState = $level->getBlockAt($down->getFloorX(), $down->getFloorY(), $down->getFloorZ());
			if ($subState->getId() !== BlockIds::AIR) {
				if (Feature::isDirt($subState) || Feature::isStone($subState)) {
					break;
				}
			}
		}

		if ($origin->getY() <= Level::Y_MIN + 3) {
			return false;
		} else {
			for ($c = 0; $c < 3; $c++) {
				$xr = $random->nextBoundedInt(2);
				$yr = $random->nextBoundedInt(2);
				$zr = $random->nextBoundedInt(2);

				$tr = ($xr + $yr + $zr) * 0.333 + 0.5;

				$minX = $origin->getX() - $xr;
				$maxX = $origin->getX() + $xr;
				$minY = $origin->getY() - $yr;
				$maxY = $origin->getY() + $yr;
				$minZ = $origin->getZ() - $zr;
				$maxZ = $origin->getZ() + $zr;

				for ($x = $minX; $x <= $maxX; $x++) {
					for ($y = $minY; $y <= $maxY; $y++) {
						for ($z = $minZ; $z <= $maxZ; $z++) {
							$dx = $x - $origin->getX();
							$dy = $y - $origin->getY();
							$dz = $z - $origin->getZ();
							$distSqr = $dx * $dx + $dy * $dy + $dz * $dz;

							if ($distSqr <= $tr * $tr) {
								$level->setBlockAt($x, $y, $z, $config->state);
							}
						}
					}
				}

				$origin = $origin->add(-1 + $random->nextBoundedInt(2), -$random->nextBoundedInt(2), -1 + $random->nextBoundedInt(2));
			}

			return true;
		}
	}
}
