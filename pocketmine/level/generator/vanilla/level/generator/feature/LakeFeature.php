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
use pocketmine\level\generator\vanilla\block\Lava;
use pocketmine\level\generator\vanilla\block\Liquid;
use pocketmine\level\generator\vanilla\block\Water;
use pocketmine\level\generator\vanilla\level\biome\BiomeFactory;
use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\level\generator\feature\configurations\LakeConfiguration;
use function array_fill;

class LakeFeature extends Feature {

	public function __construct(
		public LakeConfiguration $config
	){}

	public function place(FeaturePlaceContext $context) : bool{
		$origin = $context->origin();
		$level = $context->level();
		$random = $context->random();
		$config = $this->config;

		while ($origin->getY() > 5 && $level->getBlockAt($origin->getFloorX(), $origin->getFloorY(), $origin->getFloorZ())->getId() === BlockIds::AIR) {
			$origin = $origin->down();
		}

		if ($origin->getY() <= 4) {
			return false;
		}

		$origin = $origin->down(4);
		$mask = array_fill(0, 2048, false);
		$count = $random->nextBoundedInt(4) + 4;

		for ($blobIndex = 0; $blobIndex < $count; $blobIndex++) {
			$sizeX = $random->nextFloat() * 6.0 + 3.0;
			$sizeY = $random->nextFloat() * 4.0 + 2.0;
			$sizeZ = $random->nextFloat() * 6.0 + 3.0;
			$centerX = $random->nextFloat() * (16.0 - $sizeX - 2.0) + 1.0 + $sizeX / 2.0;
			$centerY = $random->nextFloat() * (8.0 - $sizeY - 4.0) + 2.0 + $sizeY / 2.0;
			$centerZ = $random->nextFloat() * (16.0 - $sizeZ - 2.0) + 1.0 + $sizeZ / 2.0;

			for ($x = 1; $x < 15; $x++) {
				for ($z = 1; $z < 15; $z++) {
					for ($y = 1; $y < 7; $y++) {
						$dx = (($x - $centerX) / ($sizeX / 2.0));
						$dy = (($y - $centerY) / ($sizeY / 2.0));
						$dz = (($z - $centerZ) / ($sizeZ / 2.0));
						$distanceSq = $dx * $dx + $dy * $dy + $dz * $dz;

						if ($distanceSq < 1.0) {
							$idx = (($x * 16 + $z) * 8 + $y);
							$mask[$idx] = true;
						}
					}
				}
			}
		}

		$fluid = $config->fluid->getState($random, $origin);
		for ($x = 0; $x < 16; $x++) {
			for ($z = 0; $z < 16; $z++) {
				for ($y = 0; $y < 8; $y++) {
					$idx = (($x * 16 + $z) * 8 + $y);

					$adjacentToLake = (!$mask[$idx]) && (
							($x < 15 && $mask[((($x + 1) * 16 + $z) * 8 + $y)]) ||
							($x > 0 && $mask[((($x - 1) * 16 + $z) * 8 + $y)]) ||
							($z < 15 && $mask[(($x * 16 + ($z + 1)) * 8 + $y)]) ||
							($z > 0 && $mask[(($x * 16 + ($z - 1)) * 8 + $y)]) ||
							($y < 7 && $mask[(($x * 16 + $z) * 8 + ($y + 1))]) ||
							($y > 0 && $mask[(($x * 16 + $z) * 8 + ($y - 1))])
						);

					if ($adjacentToLake) {
						$posAdd = $origin->add($x, $y, $z);
						$state = $level->getBlockAt($posAdd->getFloorX(), $posAdd->getFloorY(), $posAdd->getFloorZ());

						if ($y >= 4 && $state instanceof Liquid) {
							return false;
						}

						if ($y < 4 && !$state->isSolid() && !$state->isSameType($fluid)) {
							return false;
						}
					}
				}
			}
		}

		for ($x = 0; $x < 16; $x++) {
			for ($z = 0; $z < 16; $z++) {
				for ($y = 0; $y < 8; $y++) {
					$idx = (($x * 16 + $z) * 8 + $y);
					if ($mask[$idx]) {
						$fluidPos = $origin->add($x, $y, $z);
						$level->setBlockAt($fluidPos->getFloorX(), $fluidPos->getFloorY(), $fluidPos->getFloorZ(), ($y >= 4) ? BlockFactory::get(BlockIds::AIR) : $fluid);
					}
				}
			}
		}

		for ($x = 0; $x < 16; $x++) {
			for ($z = 0; $z < 16; $z++) {
				for ($y = 4; $y < 8; $y++) {
					$idx = (($x * 16 + $z) * 8 + $y);
					if ($mask[$idx]) {
						$below = $origin->add($x, $y - 1, $z);

						$belowState = $level->getBlockAt($below->getFloorX(), $below->getFloorY(), $below->getFloorZ());
						$posAdd = $origin->add($x, $y, $z);
						$chunkAdd = $level->getChunk($posAdd->getFloorX() >> Chunk::COORD_BIT_SIZE, $posAdd->getFloorZ() >> Chunk::COORD_BIT_SIZE);
						if (self::isDirt($belowState) && $chunkAdd->getBlockSkyLight($posAdd->getFloorX(), $posAdd->getFloorY(), $posAdd->getFloorZ()) > 0) {
							$chunkBelow = $level->getChunk($below->getFloorX() >> Chunk::COORD_BIT_SIZE, $below->getFloorZ() >> Chunk::COORD_BIT_SIZE);
							$biome = BiomeFactory::getInstance()->get($chunkBelow->getBiomeId($below->getFloorX() & Chunk::COORD_MASK, $below->getFloorZ() & Chunk::COORD_MASK));
							$top = $biome->getGenerationSettings()->getSurfaceBuilder()->getConfig()->getTop();

							if ($top->getId() === BlockIds::MYCELIUM) {
								$level->setBlockAt($below->getFloorX(), $below->getFloorY(), $below->getFloorZ(), BlockFactory::get(BlockIds::MYCELIUM));
							} else {
								$level->setBlockAt($below->getFloorX(), $below->getFloorY(), $below->getFloorZ(), BlockFactory::get(BlockIds::GRASS));
							}
						}
					}
				}
			}
		}

		if ($fluid instanceof Lava) {
			for ($x = 0; $x < 16; $x++) {
				for ($z = 0; $z < 16; $z++) {
					for ($y = 0; $y < 8; $y++) {
						$idx = (($x * 16 + $z) * 8 + $y);

						$adjacentToLava = (!$mask[$idx]) && (
								($x < 15 && $mask[((($x + 1) * 16 + $z) * 8 + $y)]) ||
								($x > 0 && $mask[((($x - 1) * 16 + $z) * 8 + $y)]) ||
								($z < 15 && $mask[(($x * 16 + ($z + 1)) * 8 + $y)]) ||
								($z > 0 && $mask[(($x * 16 + ($z - 1)) * 8 + $y)]) ||
								($y < 7 && $mask[(($x * 16 + $z) * 8 + ($y + 1))]) ||
								($y > 0 && $mask[(($x * 16 + $z) * 8 + ($y - 1))])
							);

						if ($adjacentToLava && ($y < 4 || $random->nextBoundedInt(2) !== 0)) {
							$curPos = $origin->add($x, $y, $z);
							$curMat = $level->getBlockAt($curPos->getFloorX(), $curPos->getFloorY(), $curPos->getFloorZ());
							if ($curMat->isSolid()) {
								$level->setBlockAt($curPos->getFloorX(), $curPos->getFloorY(), $curPos->getFloorZ(), BlockFactory::get(BlockIds::STONE));
							}
						}
					}
				}
			}
		}

		if ($fluid instanceof Water) {
			for ($x = 0; $x < 16; $x++) {
				for ($z = 0; $z < 16; $z++) {
					$pos = $origin->add($x, 4, $z);
					$chunk = $level->getChunk($pos->getFloorX() >> Chunk::COORD_BIT_SIZE, $pos->getFloorZ() >> Chunk::COORD_BIT_SIZE);
					$biome = BiomeFactory::getInstance()->get($chunk->getBiomeId($pos->getFloorX() & Chunk::COORD_MASK, $pos->getFloorZ() & Chunk::COORD_MASK));
					if ($biome->doesWaterFreeze($level, $pos, false)) {
						$level->setBlockAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), BlockFactory::get(BlockIds::ICE));
					}
				}
			}
		}

		return true;
	}
}
