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

use pocketmine\level\generator\vanilla\block\BlockFactory;
use pocketmine\level\generator\vanilla\block\BlockIds;
use function abs;
use function ceil;

class IceSpikeFeature extends Feature {

	public function place(FeaturePlaceContext $context) : bool
	{
		$pos = $context->origin();
		$level = $context->level();
		$rand = $context->random();

		while($pos->y > 2 && $level->getBlockAt($pos->x, $pos->y, $pos->z)->getId() === BlockIds::AIR){
			$pos = $pos->down();
		}

		if ($level->getBlockAt($pos->x, $pos->y, $pos->z)->getId() !== BlockIds::SNOW_BLOCK) {
			return false;
		}

		$pos = $pos->up($rand->nextBoundedInt(4));
		$i = $rand->nextBoundedInt(4) + 7;
		$j = (int) ($i / 4) + $rand->nextBoundedInt(2);

		if ($j > 1 && $rand->nextBoundedInt(60) === 0) {
			$pos = $pos->up(10 + $rand->nextBoundedInt(30));
		}

		for ($k = 0; $k < $i; ++$k) {
			$f = (1.0 - (float) $k / (float) $i) * (float) $j;
			$l = (int) ceil($f);

			for ($i1 = -$l; $i1 <= $l; ++$i1) {
				$f1 = abs($i1) - 0.25;

				for ($j1 = -$l; $j1 <= $l; ++$j1) {
					$f2 = abs($j1) - 0.25;

					$condition1 = ($i1 === 0 && $j1 === 0) || !($f1 * $f1 + $f2 * $f2 > $f * $f);
					$condition2 = ($i1 !== -$l && $i1 !== $l && $j1 !== -$l && $j1 !== $l) || !($rand->nextFloat() > 0.75);

					if ($condition1 && $condition2) {
						$targetPos = $pos->add($i1, $k, $j1);
						$block = $level->getBlockAt($targetPos->x, $targetPos->y, $targetPos->z);
						$blockId = $block->getId();

						if ($blockId === BlockIds::AIR || self::isDirt($block) || $blockId === BlockIds::SNOW_BLOCK || $blockId === BlockIds::ICE) {
							$this->setBlock($level, $targetPos, BlockFactory::get(BlockIds::PACKED_ICE));
						}

						if ($k !== 0 && $l > 1) {
							$targetPosDown = $pos->add($i1, -$k, $j1);
							$blockDown = $level->getBlockAt($targetPosDown->x, $targetPosDown->y, $targetPosDown->z);
							$blockIdDown = $blockDown->getId();

							if ($blockIdDown === BlockIds::AIR || self::isDirt($blockDown) || $blockIdDown === BlockIds::SNOW_BLOCK || $blockIdDown === BlockIds::ICE) {
								$this->setBlock($level, $targetPosDown, BlockFactory::get(BlockIds::PACKED_ICE));
							}
						}
					}
				}
			}
		}

		$k1 = $j - 1;
		if ($k1 < 0) {
			$k1 = 0;
		} elseif ($k1 > 1) {
			$k1 = 1;
		}

		for ($l1 = -$k1; $l1 <= $k1; ++$l1) {
			for ($i2 = -$k1; $i2 <= $k1; ++$i2) {
				$blockpos = $pos->add($l1, -1, $i2);
				$j2 = 50;
				if (abs($l1) === 1 && abs($i2) === 1) {
					$j2 = $rand->nextBoundedInt(5);
				}

				while ($blockpos->y > 50) {
					$blockstate1 = $level->getBlockAt($blockpos->x, $blockpos->y, $blockpos->z);
					$blockId1 = $blockstate1->getId();

					if ($blockId1 !== BlockIds::AIR && !self::isDirt($blockstate1) && $blockId1 !== BlockIds::SNOW_BLOCK && $blockId1 !== BlockIds::ICE && $blockId1 !== BlockIds::PACKED_ICE) {
						break;
					}

					$this->setBlock($level, $blockpos, BlockFactory::get(BlockIds::PACKED_ICE));
					$blockpos = $blockpos->down();

					$j2--;
					if ($j2 <= 0) {
						$blockpos = $blockpos->down($rand->nextBoundedInt(5) + 1);
						$j2 = $rand->nextBoundedInt(5);
					}
				}
			}
		}

		return true;
	}
}
