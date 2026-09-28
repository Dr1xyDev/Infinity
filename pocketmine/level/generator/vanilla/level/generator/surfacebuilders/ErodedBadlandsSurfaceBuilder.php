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

namespace pocketmine\level\generator\vanilla\level\generator\surfacebuilders;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\BlockFactory;
use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\block\utils\ColorBlockMetaHelper;
use pocketmine\level\generator\vanilla\level\biome\Biome;
use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\utils\Random;
use function abs;
use function ceil;
use function cos;
use function max;
use function min;
use const M_PI;

class ErodedBadlandsSurfaceBuilder extends BadlandsSurfaceBuilder {

	public function buildSurface(Random $random, Chunk $chunk, Biome $biome, int $x, int $z, int $startHeight, float $noise, Block $defaultBlock, Block $defaultFluid, int $seaLevel, int $seed, SurfaceBuilderConfig $config) : void{
		$d0 = 0.0;
		$d1 = min(abs($noise), $this->pillarNoise->getValue($x * 0.25, $z * 0.25, false) * 15.0);
		if ($d1 > 0.0) {
			$d2 = 0.001953125;
			$d3 = abs($this->pillarRoofNoise->getValue($x * 0.001953125, $z * 0.001953125, false));
			$d0 = $d1 * $d1 * 2.5;
			$d4 = ceil($d3 * 50.0) + 14.0;
			if ($d0 > $d4) {
				$d0 = $d4;
			}

			$d0 = $d0 + 64.0;
		}

		$i1 = $x & Chunk::COORD_MASK;
		$i = $z & Chunk::COORD_MASK;
		$blockstate3 = BlockFactory::get(BlockIds::TERRACOTTA, ColorBlockMetaHelper::WHITE);
		$isurfacebuilderconfig = $biome->getGenerationSettings()->getSurfaceBuilder()->getConfig();
		$blockstate4 = $isurfacebuilderconfig->getUnder();
		$blockstate = $isurfacebuilderconfig->getTop();
		$blockstate1 = $blockstate4;
		$j = (int) ($noise / 3.0 + 3.0 + $random->nextFloat() * 0.25);
		$flag = cos($noise / 3.0 * M_PI) > 0.0;
		$k = -1;
		$flag1 = false;;

		for($l = max($startHeight, (int) $d0 + 1); $l >= 0; --$l) {
			$blockstatechunk = BlockFactory::fromFullBlock($chunk->getFullBlock($i1, $l, $i));

			if ($blockstatechunk->getId() === BlockIds::AIR && $l < (int) $d0) {
				$chunk->setFullBlock($i1, $l, $i, $defaultBlock->getFullId());
			}

			$blockstate2 = $blockstatechunk;
			if ($blockstatechunk->getId() === BlockIds::AIR) {
				$k = -1;
			} elseif ($blockstatechunk->isSameType($defaultBlock)) {
				if ($k == -1) {
					$flag1 = false;
					if ($j <= 0) {
						$blockstate3 = BlockFactory::get(BlockIds::AIR);
						$blockstate1 = $defaultBlock;
					} elseif ($l >= $seaLevel - 4 && $l <= $seaLevel + 1) {
						$blockstate3 = BlockFactory::get(BlockIds::TERRACOTTA, ColorBlockMetaHelper::WHITE);
						$blockstate1 = $blockstate4;
					}

					if ($l < $seaLevel && $blockstate3->getId() === BlockIds::AIR) {
						$blockstate3 = $defaultFluid;
					}

					$k = $j + max(0, $l - $seaLevel);
					if ($l >= $seaLevel - 1) {
						if ($l <= $seaLevel + 3 + $j) {
							$chunk->setFullBlock($i1, $l, $i, $blockstate->getFullId());
							$flag1 = true;
						} else {
							if ($l >= 64 && $l <= 127) {
								if ($flag) {
									$blockstate5 = BlockFactory::get(BlockIds::HARDENED_CLAY);
								} else {
									$blockstate5 = $this->getColorBlock($x, $l, $z);
								}
							} else {
								$blockstate5 = BlockFactory::get(BlockIds::TERRACOTTA, ColorBlockMetaHelper::ORANGE);
							}

							$chunk->setFullBlock($i1, $l, $i, $blockstate5->getFullId());
						}
					} else {
						$chunk->setFullBlock($i1, $l, $i, $blockstate1->getFullId());
						$block = $blockstate1;
						if ($block->getId() === BlockIds::TERRACOTTA) {
							$chunk->setFullBlock($i1, $l, $i, BlockFactory::get(BlockIds::TERRACOTTA, ColorBlockMetaHelper::ORANGE)->getFullId());
						}
					}
				} elseif ($k > 0) {
					--$k;
					if ($flag1) {
						$chunk->setFullBlock($i1, $l, $i, BlockFactory::get(BlockIds::TERRACOTTA, ColorBlockMetaHelper::ORANGE)->getFullId());
					} else {
						$chunk->setFullBlock($i1, $l, $i, $this->getColorBlock($x, $l, $z)->getFullId());
					}
				}
			}
		}
	}
}
