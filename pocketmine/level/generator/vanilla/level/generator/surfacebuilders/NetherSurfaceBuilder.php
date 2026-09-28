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
use pocketmine\level\generator\vanilla\level\biome\Biome;
use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\level\generator\noise\synth\PerlinSimplexNoise;
use pocketmine\level\generator\vanilla\utils\Random;

class NetherSurfaceBuilder extends SurfaceBuilder {

	protected ?int $seed = null;

	protected ?PerlinSimplexNoise $noise = null;

	public function buildSurface(Random $random, Chunk $chunk, Biome $biome, int $x, int $z, int $startHeight, float $noise, Block $defaultBlock, Block $defaultFluid, int $seaLevel, int $seed, SurfaceBuilderConfig $config) : void{
		$i = $seaLevel;
		$j = $x & Chunk::COORD_MASK;
		$k = $z & Chunk::COORD_MASK;
		$flag = $this->noise->getValue3D($x * 0.03125, $z * 0.03125, 0.0) * 75.0 + $random->nextFloat() > 0.0;
		$flag1 = $this->noise->getValue3D($x * 0.03125, 109.0, $z * 0.03125) * 75.0 + $random->nextFloat() > 0.0;
		$l = (int) ($noise / 3.0 + 3.0 + $random->nextFloat() * 0.25);
		$i1 = -1;
		$blockstate = $config->getTop();
		$blockstate1 = $config->getUnder();

		for ($j1 = 127; $j1 >= 0; --$j1) {
			$blockstate2 = BlockFactory::fromFullBlock($chunk->getFullBlock($j, $j1, $k));

			if ($blockstate2->getId() === BlockIds::AIR) {
				$i1 = -1;
			} elseif ($blockstate2->isSameType($defaultBlock)) {
				if ($i1 == -1) {
					$flag2 = false;
					if ($l <= 0) {
						$flag2 = true;
						$blockstate1 = $config->getUnder();
					} elseif ($j1 >= $i - 4 && $j1 <= $i + 1) {
						$blockstate = $config->getTop();
						$blockstate1 = $config->getUnder();
						if ($flag1) {
							$blockstate = BlockFactory::get(BlockIds::GRAVEL);
							$blockstate1 = $config->getUnder();
						}

						if ($flag) {
							$blockstate = BlockFactory::get(BlockIds::SOUL_SAND);
							$blockstate1 = BlockFactory::get(BlockIds::SOUL_SAND);
						}
					}

					if ($j1 < $i && $flag2) {
						$blockstate = $defaultFluid;
					}

					$i1 = $l;
					if ($j1 >= $i - 1) {
						$chunk->setFullBlock($j, $j1, $k, $blockstate->getFullId());
					} else {
						$chunk->setFullBlock($j, $j1, $k, $blockstate1->getFullId());
					}
				} elseif ($i1 > 0) {
					--$i1;
					$chunk->setFullBlock($j, $j1, $k, $blockstate1->getFullId());
				}
			}
		}
	}

	public function setSeed(int $seed) : void{
		if ($this->seed !== $seed || $this->noise === null) {
			$random = new Random($seed);
			$this->noise = new PerlinSimplexNoise($random, 4);
		}

		$this->seed = $seed;
	}
}
