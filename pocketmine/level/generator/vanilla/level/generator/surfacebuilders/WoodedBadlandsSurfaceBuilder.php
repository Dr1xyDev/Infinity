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
use pocketmine\level\generator\vanilla\block\Dirt;
use pocketmine\level\generator\vanilla\block\utils\ColorBlockMetaHelper;
use pocketmine\level\generator\vanilla\level\biome\Biome;
use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\utils\Random;
use function cos;
use function max;
use const M_PI;

class WoodedBadlandsSurfaceBuilder extends BadlandsSurfaceBuilder {

	public function buildSurface(Random $random, Chunk $chunk, Biome $biome, int $x, int $z, int $startHeight, float $noise, Block $defaultBlock, Block $defaultFluid, int $seaLevel, int $seed, SurfaceBuilderConfig $config) : void{
		$i = $x & Chunk::COORD_MASK;
		$j = $z & Chunk::COORD_MASK;
		$blockState = BlockFactory::get(BlockIds::TERRACOTTA, ColorBlockMetaHelper::WHITE);
		$isurfaceBuilderConfig = $biome->getGenerationSettings()->getSurfaceBuilder()->getConfig();
		$blockState1 = $isurfaceBuilderConfig->getUnder();
		$blockState2 = $isurfaceBuilderConfig->getTop();
		$blockState3 = $blockState1;
		$k = (int) ($noise / 3.0 + 3.0 + $random->nextFloat() * 0.25);
		$flag = cos($noise / 3.0 * M_PI) > 0.0;
		$l = -1;
		$flag1 = false;
		$i1 = 0;

		for ($j1 = $startHeight; $j1 >= 0; --$j1) {
			if ($i1 < 15) {
				$blockState4 = BlockFactory::fromFullBlock($chunk->getFullBlock($i, $j1, $j));
				if ($blockState4->getId() === BlockIds::AIR) {
					$l = -1;
				} elseif ($blockState4->isSameType($defaultBlock)) {
					if ($l == -1) {
						$flag1 = false;
						if ($k <= 0) {
							$blockState = BlockFactory::get(BlockIds::AIR);
							$blockState3 = $defaultBlock;
						} elseif ($j1 >= $seaLevel - 4 && $j1 <= $seaLevel + 1) {
							$blockState = BlockFactory::get(BlockIds::TERRACOTTA, ColorBlockMetaHelper::WHITE);
							$blockState3 = $blockState1;
						}

						if ($j1 < $seaLevel && $blockState->getId() === BlockIds::AIR) {
							$blockState = $defaultFluid;
						}

						$l = $k + max(0, $j1 - $seaLevel);
						if ($j1 >= $seaLevel - 1) {

							if ($j1 > 86 + $k * 2) {
								if ($flag) {
									$chunk->setFullBlock($i, $j1, $j, BlockFactory::get(BlockIds::DIRT, Dirt::TYPE_COARSE)->getFullId());
								} else {
									$chunk->setFullBlock($i, $j1, $j, BlockFactory::get(BlockIds::GRASS)->getFullId());
								}
							} elseif ($j1 > $seaLevel + 3 + $k) {
								if ($j1 >= 64 && $j1 <= 127) {
									if ($flag) {
										$blockState5 = BlockFactory::get(BlockIds::HARDENED_CLAY);
									} else {
										$blockState5 = $this->getColorBlock($x, $j1, $z);
									}
								} else {
									$blockState5 = BlockFactory::get(BlockIds::TERRACOTTA, ColorBlockMetaHelper::ORANGE);
								}

								$chunk->setFullBlock($i, $j1, $j, $blockState5->getFullId());
							} else {
								$chunk->setFullBlock($i, $j1, $j, $blockState2->getFullId());
								$flag1 = true;
							}
						} else {
							$chunk->setFullBlock($i, $j1, $j, $blockState3->getFullId());
							if ($blockState3->getId() === BlockIds::TERRACOTTA && $blockState3->getDamage() === ColorBlockMetaHelper::WHITE) {
								$chunk->setFullBlock($i, $j1, $j, BlockFactory::get(BlockIds::TERRACOTTA, ColorBlockMetaHelper::ORANGE)->getFullId());
							}
						}
					} elseif ($l > 0) {
						--$l;
						if ($flag1) {
							$chunk->setFullBlock($i, $j1, $j, BlockFactory::get(BlockIds::TERRACOTTA, ColorBlockMetaHelper::ORANGE)->getFullId());
						} else {
							$chunk->setFullBlock($i, $j1, $j, $this->getColorBlock($x, $j1, $z)->getFullId());
						}
					}

					++$i1;
				}
			}
		}
	}
}
