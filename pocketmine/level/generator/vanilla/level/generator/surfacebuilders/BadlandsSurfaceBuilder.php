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
use pocketmine\level\generator\vanilla\level\generator\noise\synth\PerlinSimplexNoise;
use pocketmine\level\generator\vanilla\utils\Random;
use function array_fill;
use function cos;
use function max;
use function round;
use const M_PI;

class BadlandsSurfaceBuilder extends SurfaceBuilder {

	protected ?int $seed = null;
	/** @var Block[]  */
	protected ?array $layerColors = null;

	protected ?PerlinSimplexNoise $pillarNoise = null;
	protected ?PerlinSimplexNoise $pillarRoofNoise = null;
	protected ?PerlinSimplexNoise $colorNoise = null;

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

		for ($y = $startHeight; $y >= 0; --$y) {
			if ($i1 < 15) {
				$blockState4 = BlockFactory::fromFullBlock($chunk->getFullBlock($i, $y, $j));
				if ($blockState4->getId() === BlockIds::AIR) {
					$l = -1;
				} elseif ($blockState4->isSameType($defaultBlock)) {
					if ($l == -1) {
						$flag1 = false;
						if ($k <= 0) {
							$blockState = BlockFactory::get(BlockIds::AIR);
							$blockState3 = $defaultBlock;
						} elseif ($y >= $seaLevel - 4 && $y <= $seaLevel + 1) {
							$blockState = BlockFactory::get(BlockIds::TERRACOTTA, ColorBlockMetaHelper::WHITE);
							$blockState3 = $blockState1;
						}

						if ($y < $seaLevel && $blockState->getId() === BlockIds::AIR) {
							$blockState = $defaultFluid;
						}

						$l = $k + max(0, $y - $seaLevel);
						if ($y >= $seaLevel - 1) {
							if ($y > $seaLevel + 3 + $k) {
								if ($y >= 64 && $y <= 127) {
									if ($flag) {
										$blockState5 = BlockFactory::get(BlockIds::HARDENED_CLAY);
									} else {
										$blockState5 = $this->getColorBlock($x, $y, $z);
									}
								} else {
									$blockState5 = BlockFactory::get(BlockIds::TERRACOTTA, ColorBlockMetaHelper::ORANGE);
								}

								$chunk->setFullBlock($i, $y, $j, $blockState5->getFullId());
							} else {
								$chunk->setFullBlock($i, $y, $j, $blockState2->getFullId());
								$flag1 = true;
							}
						} else {
							$chunk->setFullBlock($i, $y, $j, $blockState3->getFullId());
							$block = $blockState3;
							if ($block->getId() === BlockIds::TERRACOTTA) {
								$chunk->setFullBlock($i, $y, $j, BlockFactory::get(BlockIds::TERRACOTTA, ColorBlockMetaHelper::ORANGE)->getFullId());
							}
						}
					} elseif ($l > 0) {
						--$l;
						if ($flag1) {
							$chunk->setFullBlock($i, $y, $j, BlockFactory::get(BlockIds::TERRACOTTA, ColorBlockMetaHelper::ORANGE)->getFullId());
						} else {
							$chunk->setFullBlock($i, $y, $j, $this->getColorBlock($x, $y, $z)->getFullId());
						}
					}

					++$i1;
				}
			}
		}
	}

	public function setSeed(int $seed) : void{
		if ($this->seed !== $seed || $this->layerColors === null) {
			$this->generateLayers($seed);
		}

		if ($this->seed !== $seed || $this->pillarNoise === null || $this->pillarRoofNoise === null) {
			$random = new Random($seed);
			$this->pillarNoise = new PerlinSimplexNoise($random, 4);
			$this->pillarRoofNoise = new PerlinSimplexNoise($random, 1);
		}

		$this->seed = $seed;
	}

	private function generateLayers(int $seed) : void {
		$this->layerColors = array_fill(0, 64, BlockFactory::get(BlockIds::HARDENED_CLAY));

		$rand = new Random($seed);
		$this->colorNoise = new PerlinSimplexNoise($rand, 1);

		$orange = BlockFactory::get(BlockIds::TERRACOTTA, ColorBlockMetaHelper::ORANGE);
		for ($i = 0; $i < 64; $i++) {
			$i += $rand->nextBoundedInt(5) + 1;
			if ($i < 64) {
				$this->layerColors[$i] = $orange;
			}
		}

		$yellow = BlockFactory::get(BlockIds::TERRACOTTA, ColorBlockMetaHelper::YELLOW);
		$count = $rand->nextBoundedInt(4) + 2;
		for ($b = 0; $b < $count; $b++) {
			$height = $rand->nextBoundedInt(3) + 1;
			$start = $rand->nextBoundedInt(64);
			for ($h = 0; $start + $h < 64 && $h < $height; $h++) {
				$this->layerColors[$start + $h] = $yellow;
			}
		}

		$brown = BlockFactory::get(BlockIds::TERRACOTTA, ColorBlockMetaHelper::BROWN);
		$count = $rand->nextBoundedInt(4) + 2;
		for ($b = 0; $b < $count; $b++) {
			$height = $rand->nextBoundedInt(3) + 2;
			$start = $rand->nextBoundedInt(64);
			for ($h = 0; $start + $h < 64 && $h < $height; $h++) {
				$this->layerColors[$start + $h] = $brown;
			}
		}

		$red = BlockFactory::get(BlockIds::TERRACOTTA, ColorBlockMetaHelper::RED);
		$count = $rand->nextBoundedInt(4) + 2;
		for ($b = 0; $b < $count; $b++) {
			$height = $rand->nextBoundedInt(3) + 1;
			$start = $rand->nextBoundedInt(64);
			for ($h = 0; $start + $h < 64 && $h < $height; $h++) {
				$this->layerColors[$start + $h] = $red;
			}
		}

		$white = BlockFactory::get(BlockIds::TERRACOTTA, ColorBlockMetaHelper::WHITE);
		$lightGray = BlockFactory::get(BlockIds::TERRACOTTA, ColorBlockMetaHelper::LIGHT_GRAY);
		$count = $rand->nextBoundedInt(3) + 3;
		$offset = 0;
		for ($b = 0; $b < $count; $b++) {
			$offset += $rand->nextBoundedInt(16) + 4;
			if ($offset < 64) {
				$this->layerColors[$offset] = $white;
				if ($offset > 1 && $rand->nextBoolean()) {
					$this->layerColors[$offset - 1] = $lightGray;
				}
				if ($offset < 63 && $rand->nextBoolean()) {
					$this->layerColors[$offset + 1] = $lightGray;
				}
			}
		}
	}

	protected function getColorBlock(int $x, int $y, int $z) : Block {
		$offset = (int) round($this->colorNoise->getValue2D($x / 512.0, $z / 512.0) * 2.0);
		return $this->layerColors[($y + $offset + 64) % 64];
	}
}
