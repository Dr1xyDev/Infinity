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
use pocketmine\level\generator\vanilla\block\Sand;
use pocketmine\level\generator\vanilla\level\biome\Biome;
use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\Random;
use function max;

class DefaultSurfaceBuilder extends SurfaceBuilder{

	public function buildSurface(Random $random, Chunk $chunk, Biome $biome, int $x, int $z, int $startHeight, float $noise, Block $defaultBlock, Block $defaultFluid, int $seaLevel, int $seed, SurfaceBuilderConfig $config) : void{
		$blockState = $config->getTop();
		$blockState1 = $config->getUnder();
		$run = -1;
		$runDepth = (int) ($noise / 3.0 + 3.0 + $random->nextFloat() * 0.25);
		$xx = $x & Chunk::COORD_MASK;
		$yy = $z & Chunk::COORD_MASK;

		$sandStone = BlockFactory::get(BlockIds::SANDSTONE);
		$redSandStone = BlockFactory::get(BlockIds::RED_SANDSTONE);
		for ($y = $startHeight; $y >= 0; --$y) {
			$mutable = new Vector3($xx, $y, $yy);
			$blockState2 = BlockFactory::fromFullBlock($chunk->getFullBlock($mutable->getFloorX(), $mutable->getFloorY(), $mutable->getFloorZ()));
			if ($blockState2->getId() === BlockIds::AIR) {
				$run = -1;
			} elseif ($blockState2->isSameType($defaultBlock)) {
				if ($run == -1) {
					if ($runDepth <= 0) {
						$blockState = BlockFactory::get(BlockIds::AIR);
						$blockState1 = $defaultBlock;
					} elseif ($y >= $seaLevel - 4 && $y <= $seaLevel + 1) {
						$blockState = $config->getTop();
						$blockState1 = $config->getUnder();
					}

					if ($y < $seaLevel && $blockState->getId() === BlockIds::AIR) {
						if ($biome->getTemperature() < 0.15) {
							$blockState = BlockFactory::get(BlockIds::ICE);
						} else {
							$blockState = $defaultFluid;
						}

						$mutable = new Vector3($xx, $y, $yy);
					}

					$run = $runDepth;
					if ($y >= $seaLevel - 1) {
						$chunk->setFullBlock($mutable->getFloorX(), $mutable->getFloorY(), $mutable->getFloorZ(), $blockState->getFullId());
					} elseif ($y < $seaLevel - 7 - $runDepth) {
						$blockState = BlockFactory::get(BlockIds::AIR);
						$blockState1 = $defaultBlock;
						$chunk->setFullBlock($mutable->getFloorX(), $mutable->getFloorY(), $mutable->getFloorZ(), $config->getUnderWater()->getFullId());
					} else {
						$chunk->setFullBlock($mutable->getFloorX(), $mutable->getFloorY(), $mutable->getFloorZ(), $blockState1->getFullId());
					}
				} elseif ($run > 0) {
					--$run;
					$chunk->setFullBlock($mutable->getFloorX(), $mutable->getFloorY(), $mutable->getFloorZ(), $blockState1->getFullId());
					if ($run == 0 && $blockState1->isSameType(BlockFactory::get(BlockIds::SAND)) && $runDepth > 1) {
						$run = $random->nextBoundedInt(4) + max(0, $y - 63);
						$blockState1 = $blockState1->isSameType(BlockFactory::get(BlockIds::SAND, Sand::TYPE_RED)) ? $redSandStone : $sandStone;
					}
				}
			}
		}
	}
}
