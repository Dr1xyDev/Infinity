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
use pocketmine\level\generator\vanilla\block\Water;
use pocketmine\level\generator\vanilla\level\biome\Biome;
use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\level\generator\noise\synth\PerlinSimplexNoise;
use pocketmine\level\generator\vanilla\utils\Random;
use function abs;
use function ceil;
use function max;
use function min;

class FrozenOceanSurfaceBuilder extends SurfaceBuilder {

	protected ?int $seed = null;

	protected ?PerlinSimplexNoise $pillarNoise = null;
	protected ?PerlinSimplexNoise $pillarRoofNoise = null;

	public function buildSurface(Random $random, Chunk $chunk, Biome $biome, int $x, int $z, int $startHeight, float $noise, Block $defaultBlock, Block $defaultFluid, int $seaLevel, int $seed, SurfaceBuilderConfig $config) : void{
		$d0 = 0.0;
		$d1 = 0.0;

		$f = $biome->getTemperature();
		$d2 = min(abs($noise), $this->pillarNoise->getValue2D($x * 0.1, $z * 0.1) * 15.0);
		if ($d2 > 1.8) {
			$d4 = abs($this->pillarRoofNoise->getValue2D($x * 0.09765625, $z * 0.09765625));
			$d0 = $d2 * $d2 * 1.2;
			$d5 = ceil($d4 * 40.0) + 14.0;
			if ($d0 > $d5) {
				$d0 = $d5;
			}

			if ($f > 0.1) {
				$d0 -= 2.0;
			}

			if ($d0 > 2.0) {
				$d1 = $seaLevel - $d0 - 7.0;
				$d0 = $d0 + $seaLevel;
			} else {
				$d0 = 0.0;
			}
		}

		$l1 = $x & Chunk::COORD_MASK;
		$i = $z & Chunk::COORD_MASK;
		$isurfaceBuilderConfig = $biome->getGenerationSettings()->getSurfaceBuilder()->getConfig();
		$blockState = $isurfaceBuilderConfig->getUnder();
		$blockState4 = $isurfaceBuilderConfig->getTop();
		$blockState1 = $blockState;
		$blockState2 = $blockState4;
		$j = (int) ($noise / 3.0 + 3.0 + $random->nextFloat() * 0.25);
		$k = -1;
		$l = 0;
		$i1 = 2 + $random->nextBoundedInt(4);
		$j1 = $seaLevel + 18 + $random->nextBoundedInt(10);

		for ($k1 = max($startHeight, (int) $d0 + 1); $k1 >= 0; --$k1) {
			$blockstatechunk = BlockFactory::fromFullBlock($chunk->getFullBlock($l1, $k1, $i));

			if ($blockstatechunk->getId() === BlockIds::AIR && $k1 < (int) $d0 && $random->nextFloat() > 0.01) {
				$chunk->setFullBlock($l1, $k1, $i, BlockFactory::get(BlockIds::PACKED_ICE)->getFullId());
			} elseif ($blockstatechunk instanceof Water && $k1 > (int) $d1 && $k1 < $seaLevel && $d1 != 0.0 && $random->nextFloat() > 0.15) {
				$chunk->setFullBlock($l1, $k1, $i, BlockFactory::get(BlockIds::PACKED_ICE)->getFullId());
			}

			$blockstate3 = $blockstatechunk;
			if ($blockstate3->getId() === BlockIds::AIR) {
				$k = -1;
			} elseif (!$blockstate3->isSameType($defaultBlock)) {
				if ($blockstate3->getId() === BlockIds::PACKED_ICE && $l <= $i1 && $k1 > $j1) {
					$chunk->setFullBlock($l1, $k1, $i, BlockFactory::get(BlockIds::SNOW_BLOCK)->getFullId());
					++$l;
				}
			} elseif ($k == -1) {
				if ($j <= 0) {
					$blockState2 = BlockFactory::get(BlockIds::AIR);
					$blockState1 = $defaultBlock;
				} elseif ($k1 >= $seaLevel - 4 && $k1 <= $seaLevel + 1) {
					$blockState2 = $blockState4;
					$blockState1 = $blockState;
				}

				if ($k1 < $seaLevel && $blockState2->getId() === BlockIds::AIR) {
					if ($biome->getTemperature() < 0.15) {
						$blockState2 = BlockFactory::get(BlockIds::ICE);
					} else {
						$blockState2 = $defaultFluid;
					}
				}

				$k = $j;
				if ($k1 >= $seaLevel - 1) {
					$chunk->setFullBlock($l1, $k1, $i, $blockState2->getFullId());
				} elseif ($k1 < $seaLevel - 7 - $j) {
					$blockState2 = BlockFactory::get(BlockIds::AIR);
					$blockState1 = $defaultBlock;
					$chunk->setFullBlock($l1, $k1, $i, BlockFactory::get(BlockIds::GRAVEL)->getFullId());
				} else {
					$chunk->setFullBlock($l1, $k1, $i, $blockState1->getFullId());
				}
			} elseif ($k > 0) {
				--$k;
				$chunk->setFullBlock($l1, $k1, $i, $blockState1->getFullId());
				if ($k == 0 && $blockState1->isSameType(BlockFactory::get(BlockIds::SAND)) && $j > 1) {
					$k = $random->nextBoundedInt(4) + max(0, $k1 - 63);
					$blockState1 = $blockState1->isSameType(BlockFactory::get(BlockIds::SAND, Sand::TYPE_RED)) ?
						BlockFactory::get(BlockIds::RED_SANDSTONE) :
						BlockFactory::get(BlockIds::SANDSTONE);
				}
			}
		}
	}

	public function setSeed(int $seed) : void{
		if ($this->seed !== $seed || $this->pillarNoise === null || $this->pillarRoofNoise === null) {
			$random = new Random($seed);
			$this->pillarNoise = new PerlinSimplexNoise($random, 4);
			$this->pillarRoofNoise = new PerlinSimplexNoise($random, 1);
		}

		$this->seed = $seed;
	}
}
