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

namespace pocketmine\level\generator\vanilla\level\generator\cave;

use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\utils\Random;
use function cos;
use function floor;
use function intdiv;
use function max;
use function min;
use function sin;
use const M_PI;

class LegacyOverworldCaveGenerator{
	private const SEARCH_RADIUS = 8;
	private const MAX_CARVE_Y = 120;

	public function apply(Chunk $chunk, int $chunkX, int $chunkZ, int $worldSeed) : void{
		$random = new Random($worldSeed);
		$xScale = intdiv($random->nextInt(), 2) * 2 + 1;
		$zScale = intdiv($random->nextInt(), 2) * 2 + 1;

		for($sourceChunkX = $chunkX - self::SEARCH_RADIUS; $sourceChunkX <= $chunkX + self::SEARCH_RADIUS; ++$sourceChunkX){
			for($sourceChunkZ = $chunkZ - self::SEARCH_RADIUS; $sourceChunkZ <= $chunkZ + self::SEARCH_RADIUS; ++$sourceChunkZ){
				$random->setSeed(($sourceChunkX * $xScale + $sourceChunkZ * $zScale) ^ $worldSeed);
				$this->addFeature($chunk, $random, $chunkX, $chunkZ, $sourceChunkX, $sourceChunkZ);
			}
		}
	}

	private function addFeature(Chunk $chunk, Random $random, int $targetChunkX, int $targetChunkZ, int $sourceChunkX, int $sourceChunkZ) : void{
		$rand1 = $random->nextBoundedInt(40) + 1;
		$rand2 = $random->nextBoundedInt($rand1) + 1;
		$caves = $random->nextBoundedInt($rand2);
		if($random->nextBoundedInt(15) !== 0){
			$caves = 0;
		}

		for($cave = 0; $cave < $caves; ++$cave){
			$caveZ = $sourceChunkZ * Chunk::EDGE_LENGTH + $random->nextBoundedInt(Chunk::EDGE_LENGTH);
			$caveY = $random->nextBoundedInt($random->nextBoundedInt(120) + 8);
			$caveX = $sourceChunkX * Chunk::EDGE_LENGTH + $random->nextBoundedInt(Chunk::EDGE_LENGTH);

			$tunnels = 1;
			if($random->nextBoundedInt(4) === 0){
				$this->addRoom($chunk, $random, $targetChunkX, $targetChunkZ, (float) $caveX, (float) $caveY, (float) $caveZ);
				$tunnels += $random->nextBoundedInt(4);
			}

			for($i = 0; $i < $tunnels; ++$i){
				$yaw = $random->nextFloat() * (M_PI * 2.0);
				$pitch = (($random->nextFloat() - 0.5) * 2.0) / 8.0;
				$thickness = $random->nextFloat() * 2.0 + $random->nextFloat();
				$this->addTunnel($chunk, $random, $targetChunkX, $targetChunkZ, (float) $caveX, (float) $caveY, (float) $caveZ, $thickness, $yaw, $pitch, 0, 0, 1.0);
			}
		}
	}

	private function addRoom(Chunk $chunk, Random $random, int $targetChunkX, int $targetChunkZ, float $x, float $y, float $z) : void{
		$this->addTunnel($chunk, $random, $targetChunkX, $targetChunkZ, $x, $y, $z, 1.0 + $random->nextFloat() * 6.0, 0.0, 0.0, -1, -1, 0.5);
	}

	private function addTunnel(Chunk $chunk, Random $random, int $targetChunkX, int $targetChunkZ, float $x, float $y, float $z, float $thickness, float $yaw, float $pitch, int $step, int $distance, float $yScale) : void{
		$chunkMidX = (float) ($targetChunkX * Chunk::EDGE_LENGTH + 8);
		$chunkMidZ = (float) ($targetChunkZ * Chunk::EDGE_LENGTH + 8);
		$yawMomentum = 0.0;
		$pitchMomentum = 0.0;
		$tunnelRandom = new Random($random->nextInt());

		if($distance <= 0){
			$maxDistance = self::SEARCH_RADIUS * Chunk::EDGE_LENGTH - Chunk::EDGE_LENGTH;
			$distance = $maxDistance - $tunnelRandom->nextBoundedInt(intdiv($maxDistance, 4));
		}

		$singleStep = false;
		if($step === -1){
			$step = intdiv($distance, 2);
			$singleStep = true;
		}

		$splitPoint = $tunnelRandom->nextBoundedInt(intdiv($distance, 2)) + intdiv($distance, 4);
		$steep = $tunnelRandom->nextBoundedInt(6) === 0;

		for(;$step < $distance; ++$step){
			$radius = 1.5 + sin($step * M_PI / $distance) * $thickness;
			$verticalRadius = $radius * $yScale;

			$cosPitch = cos($pitch);
			$x += cos($yaw) * $cosPitch;
			$y += sin($pitch);
			$z += sin($yaw) * $cosPitch;

			$pitch *= $steep ? 0.92 : 0.7;
			$pitch += $pitchMomentum * 0.1;
			$yaw += $yawMomentum * 0.1;
			$pitchMomentum *= 0.9;
			$yawMomentum *= 0.75;
			$pitchMomentum += $this->nextGaussian($tunnelRandom) * $tunnelRandom->nextFloat() * 2.0;
			$yawMomentum += $this->nextGaussian($tunnelRandom) * $tunnelRandom->nextFloat() * 4.0;

			if(!$singleStep && $step === $splitPoint && $thickness > 1.0){
				$branchThickness = $tunnelRandom->nextFloat() * 0.5 + 0.5;
				$this->addTunnel($chunk, $tunnelRandom, $targetChunkX, $targetChunkZ, $x, $y, $z, $branchThickness, $yaw - M_PI / 2.0, $pitch / 3.0, $step, $distance, 1.0);
				$this->addTunnel($chunk, $tunnelRandom, $targetChunkX, $targetChunkZ, $x, $y, $z, $tunnelRandom->nextFloat() * 0.5 + 0.5, $yaw + M_PI / 2.0, $pitch / 3.0, $step, $distance, 1.0);
				return;
			}

			if(!$singleStep && $tunnelRandom->nextBoundedInt(4) === 0){
				continue;
			}

			$remaining = (float) ($distance - $step);
			$deltaX = $x - $chunkMidX;
			$deltaZ = $z - $chunkMidZ;
			$maxReach = $thickness + 18.0;
			if($deltaX * $deltaX + $deltaZ * $deltaZ - $remaining * $remaining > $maxReach * $maxReach){
				return;
			}

			if(
				$x < $chunkMidX - 16.0 - $radius * 2.0 ||
				$z < $chunkMidZ - 16.0 - $radius * 2.0 ||
				$x > $chunkMidX + 16.0 + $radius * 2.0 ||
				$z > $chunkMidZ + 16.0 + $radius * 2.0
			){
				continue;
			}

			$x0 = max((int) floor($x - $radius) - $targetChunkX * Chunk::EDGE_LENGTH - 1, 0);
			$x1 = min((int) floor($x + $radius) - $targetChunkX * Chunk::EDGE_LENGTH + 1, Chunk::EDGE_LENGTH);
			$y0 = max((int) floor($y - $verticalRadius) - 1, 1);
			$y1 = min((int) floor($y + $verticalRadius) + 1, self::MAX_CARVE_Y);
			$z0 = max((int) floor($z - $radius) - $targetChunkZ * Chunk::EDGE_LENGTH - 1, 0);
			$z1 = min((int) floor($z + $radius) - $targetChunkZ * Chunk::EDGE_LENGTH + 1, Chunk::EDGE_LENGTH);

			$detectedWater = false;
			for($carveX = $x0; !$detectedWater && $carveX < $x1; ++$carveX){
				for($carveZ = $z0; !$detectedWater && $carveZ < $z1; ++$carveZ){
					for($carveY = $y1 + 1; !$detectedWater && $carveY >= $y0 - 1; --$carveY){
						$blockId = $chunk->getBlockId($carveX, $carveY, $carveZ);
						if($blockId === BlockIds::FLOWING_WATER || $blockId === BlockIds::STILL_WATER){
							$chunk->setBlockId($carveX, $carveY, $carveZ, BlockIds::FLOWING_WATER);
							$detectedWater = true;
						}

						if($carveY !== $y0 - 1 && $carveX !== $x0 && $carveX !== $x1 - 1 && $carveZ !== $z0 && $carveZ !== $z1 - 1){
							$carveY = $y0;
						}
					}
				}
			}

			if($detectedWater){
				continue;
			}

			for($carveX = $x0; $carveX < $x1; ++$carveX){
				$normX = (($carveX + $targetChunkX * Chunk::EDGE_LENGTH + 0.5) - $x) / $radius;

				for($carveZ = $z0; $carveZ < $z1; ++$carveZ){
					$normZ = (($carveZ + $targetChunkZ * Chunk::EDGE_LENGTH + 0.5) - $z) / $radius;
					if($normX * $normX + $normZ * $normZ >= 1.0){
						continue;
					}

					$hasGrass = false;
					for($carveY = $y1 - 1; $carveY >= $y0; --$carveY){
						$normY = (($carveY + 0.5) - $y) / $verticalRadius;
						if($normY <= -0.7 || $normX * $normX + $normY * $normY + $normZ * $normZ >= 1.0){
							continue;
						}

						$blockId = $chunk->getBlockId($carveX, $carveY, $carveZ);
						$aboveId = $chunk->getBlockId($carveX, $carveY + 1, $carveZ);
						if($blockId === BlockIds::GRASS){
							$hasGrass = true;
						}

						if(!$this->isDiggable($blockId, $aboveId)){
							continue;
						}

						if($carveY < 10){
							$chunk->setBlockId($carveX, $carveY, $carveZ, BlockIds::STILL_LAVA);
						}else{
							if($this->thinSand($chunk, $carveX, $carveY, $carveZ)){
								$sandData = $chunk->getBlockData($carveX, $carveY + 1, $carveZ);
								$chunk->setBlockId($carveX, $carveY + 1, $carveZ, $sandData === 1 ? BlockIds::RED_SANDSTONE : BlockIds::SANDSTONE);
							}

							$chunk->setBlockId($carveX, $carveY, $carveZ, BlockIds::AIR);
							if($hasGrass && $carveY > 0 && $chunk->getBlockId($carveX, $carveY - 1, $carveZ) === BlockIds::DIRT){
								$chunk->setBlockId($carveX, $carveY - 1, $carveZ, BlockIds::GRASS);
							}
						}
					}
				}
			}

			if($singleStep){
				break;
			}
		}
	}

	private function thinSand(Chunk $chunk, int $x, int $y, int $z) : bool{
		return
			$y < $chunk->getHeightMap($x, $z) - 3 &&
			$y + 3 < Chunk::MAX_SUBCHUNKS * Chunk::EDGE_LENGTH &&
			$chunk->getBlockId($x, $y + 1, $z) === BlockIds::SAND &&
			$chunk->getBlockId($x, $y + 2, $z) === BlockIds::SAND &&
			$chunk->getBlockId($x, $y + 3, $z) === BlockIds::SAND;
	}

	private function isDiggable(int $blockId, int $aboveId) : bool{
		return $blockId === BlockIds::STONE ||
			$blockId === BlockIds::DIRT ||
			$blockId === BlockIds::GRASS ||
			$blockId === BlockIds::HARDENED_CLAY ||
			$blockId === BlockIds::STAINED_CLAY ||
			$blockId === BlockIds::SANDSTONE ||
			$blockId === BlockIds::RED_SANDSTONE ||
			$blockId === BlockIds::MYCELIUM ||
			$blockId === BlockIds::PODZOL ||
			($blockId === BlockIds::SAND && $aboveId !== BlockIds::STILL_WATER);
	}

	private function nextGaussian(Random $random) : float{
		return $random->nextFloat() - $random->nextFloat();
	}
}
