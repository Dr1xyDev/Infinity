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

namespace pocketmine\level\generator\vanilla\level\generator\dimension;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\BlockFactory;
use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\level\biome\BiomeFactory;
use pocketmine\level\generator\vanilla\level\ChunkManager;
use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\level\generator\cave\LegacyOverworldCaveGenerator;
use pocketmine\level\generator\vanilla\level\generator\Generator;
use pocketmine\level\generator\vanilla\level\generator\layer\Layer;
use pocketmine\level\generator\vanilla\level\generator\layer\LayerData;
use pocketmine\level\generator\vanilla\level\generator\layer\LayerUtils;
use pocketmine\level\generator\vanilla\level\generator\MathHelper;
use pocketmine\level\generator\vanilla\level\generator\noise\synth\PerlinNoise;
use pocketmine\level\generator\vanilla\level\generator\noise\synth\PerlinSimplexNoise;
use pocketmine\level\generator\vanilla\level\generator\structure\ChunkStructureWorld;
use pocketmine\level\generator\vanilla\level\generator\structure\mineshaft\MapGenMineshaft;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\Random;
use function sqrt;

class Overworld extends Generator {

	protected const int NOISE_SIZE_Y = 33;

	protected PerlinNoise $minLimitPerlinNoise;
	protected PerlinNoise $maxLimitPerlinNoise;
	protected PerlinNoise $mainPerlinNoise;
	protected PerlinSimplexNoise $surfaceNoise;
	protected PerlinNoise $depthNoise;

	/** @var float[] */
	protected array $biomeWeights = [];
	/** @var float[] */
	protected array $depthBuffer = [];

	protected Layer $biomeLayer;
	protected Layer $zoomedLayer;

	protected Block $defaultBlock;
	protected Block $defaultFluid;

	protected BiomeFactory $biomeFactory;
	protected LegacyOverworldCaveGenerator $caveGenerator;
	protected MapGenMineshaft $mineshafts;

	public function init(ChunkManager $level, Random $random) : void{
		parent::init($level, $random);

		$this->minLimitPerlinNoise = new PerlinNoise($random, 16);
		$this->maxLimitPerlinNoise = new PerlinNoise($random, 16);
		$this->mainPerlinNoise = new PerlinNoise($random, 8);
		$this->depthNoise = new PerlinNoise($random, 16);
		$this->surfaceNoise = new PerlinSimplexNoise($random, 4);

		for ($xb = -2; $xb <= 2; ++$xb) {
			for ($zb = -2; $zb <= 2; ++$zb) {
				$weight = 10.0 / sqrt($xb * $xb + $zb * $zb + 0.2);
				$this->biomeWeights[$xb + 2 + ($zb + 2) * 5] = $weight;
			}
		}

		[$this->biomeLayer, $this->zoomedLayer] = LayerUtils::getDefaultLayers($level->getSeed(), false);

		$this->defaultBlock = BlockFactory::get(BlockIds::STONE);
		$this->defaultFluid = BlockFactory::get(BlockIds::WATER);

		$this->biomeFactory = BiomeFactory::getInstance();
		$this->caveGenerator = new LegacyOverworldCaveGenerator();
		$this->mineshafts = new MapGenMineshaft($level->getSeed(), fn(int $x, int $z) : int => $this->getBiomeAt($x, $z), $this->getSeaLevel());
	}

	/**
	 * Biome at a block position, without generating the chunk
	 */
	public function getBiomeAt(int $x, int $z) : int{
		$layerData = new LayerData();
		$this->zoomedLayer->fillArea($layerData, $x, $z, 1, 1);
		return $layerData->parentArea[0];
	}

	public function getMineshafts() : MapGenMineshaft{
		return $this->mineshafts;
	}

	public function generateChunk(int $chunkX, int $chunkZ) : void{
		$this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed());

		$chunk = $this->level->getChunk($chunkX, $chunkZ);
		$this->prepareHeights($chunk, $chunkX, $chunkZ);

		$chunkStartBlockX = $chunkX << Chunk::COORD_BIT_SIZE;
		$chunkStartBlockZ = $chunkZ << Chunk::COORD_BIT_SIZE;

		$layerData = new LayerData();
		$this->zoomedLayer->fillArea($layerData, $chunkStartBlockX, $chunkStartBlockZ, Chunk::EDGE_LENGTH, Chunk::EDGE_LENGTH);

		for ($zOff = 0; $zOff < Chunk::EDGE_LENGTH; $zOff++) {
			for ($xOff = 0; $xOff < Chunk::EDGE_LENGTH; $xOff++) {
				$chunk->setBiomeId($xOff, $zOff, $layerData->parentArea[$zOff * Chunk::EDGE_LENGTH + $xOff]);
			}
		}

		$this->surfaceNoise->getRegion2D($this->depthBuffer, $chunkStartBlockX, $chunkStartBlockZ, Chunk::EDGE_LENGTH, Chunk::EDGE_LENGTH, 0.125, 0.125);

		$bedrock = BlockFactory::get(BlockIds::BEDROCK)->getFullId();
		for ($x = 0; $x < Chunk::EDGE_LENGTH; $x++) {
			for ($z = 0; $z < Chunk::EDGE_LENGTH; $z++) {
				for ($y = 4; $y >= 0; --$y) {
					if ($y <= $this->random->nextBoundedInt(5)) {
						$chunk->setFullBlock($x, $y, $z, $bedrock);
					}
				}

				$xx = $chunkStartBlockX + $x;
				$zz = $chunkStartBlockZ + $z;
				$maxY = $chunk->getHeightMap($x, $z) + 1;
				$this->biomeFactory->get($layerData->parentArea[$z * Chunk::EDGE_LENGTH + $x])->buildSurface($this->random, $chunk, $xx, $zz, $maxY, $this->depthBuffer[$z * Chunk::EDGE_LENGTH + $x], $this->defaultBlock, $this->defaultFluid, $this->getSeaLevel(), $this->level->getSeed());
			}
		}

		$this->caveGenerator->apply($chunk, $chunkX, $chunkZ, $this->level->getSeed());

		$structureWorld = new ChunkStructureWorld($chunk);
		$this->mineshafts->generate($structureWorld, $structureWorld->getBoundingBox(), $chunkX, $chunkZ);
	}

	public function populateChunk(int $chunkX, int $chunkZ) : void{
		$this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed());

		$chunk = $this->level->getChunk($chunkX, $chunkZ);
		$biome = $this->biomeFactory->get($chunk->getBiomeId(7, 7));
		$biome->generateFeatures($this->level, $this, $this->random, new Vector3($chunkX << Chunk::COORD_BIT_SIZE, 0, $chunkZ << Chunk::COORD_BIT_SIZE));
	}

	public function prepareHeights(Chunk $chunk, int $xOffs, int $zOffs) : void {
		$noiseBuffer = [];
		$biomes = [];

		$layerData = new LayerData();
		$this->biomeLayer->fillArea($layerData, $xOffs * Chunk::COORD_BIT_SIZE - 2, $zOffs * Chunk::COORD_BIT_SIZE - 2, 10, 10);

		for ($i = 0; $i < 10 * 10; $i++) {
			$biome = $this->biomeFactory->get($layerData->parentArea[$i]);
			$biomes[$i] = $biome;
		}

		$this->getHeights($noiseBuffer, $biomes, $xOffs * 4, 0, $zOffs * 4);

		$stoneId = BlockFactory::get(BlockIds::STONE)->getFullId();
		$waterId = BlockFactory::get(BlockIds::WATER)->getFullId();
		$airId = 0;

		for ($xc = 0; $xc < 4; $xc++) {
			$xCurr = $xc * 5;
			$xNext = ($xc + 1) * 5;

			for ($zc = 0; $zc < 4; $zc++) {
				$xzMinMin = ($xCurr + $zc) * 33;
				$xzMinMax = ($xCurr + $zc + 1) * 33;
				$xzMaxMin = ($xNext + $zc) * 33;
				$xzMaxMax = ($xNext + $zc + 1) * 33;

				for ($yc = 0; $yc < 32; $yc++) {
					$s0 = $noiseBuffer[$xzMinMin + $yc];
					$s1 = $noiseBuffer[$xzMinMax + $yc];
					$s2 = $noiseBuffer[$xzMaxMin + $yc];
					$s3 = $noiseBuffer[$xzMaxMax + $yc];

					$s0a = ($noiseBuffer[$xzMinMin + $yc + 1] - $s0) * 0.125;
					$s1a = ($noiseBuffer[$xzMinMax + $yc + 1] - $s1) * 0.125;
					$s2a = ($noiseBuffer[$xzMaxMin + $yc + 1] - $s2) * 0.125;
					$s3a = ($noiseBuffer[$xzMaxMax + $yc + 1] - $s3) * 0.125;

					for ($y = 0; $y < 8; $y++) {
						$yGlobal = $yc * 8 + $y;

						$_s0 = $s0;
						$_s1 = $s1;
						$_s0a = ($s2 - $s0) * 0.25;
						$_s1a = ($s3 - $s1) * 0.25;

						for ($x = 0; $x < Chunk::COORD_BIT_SIZE; $x++) {
							$xGlobal = $xc * Chunk::COORD_BIT_SIZE + $x;

							$val = $_s0;
							$vala = ($_s1 - $_s0) * 0.25;
							$val -= $vala;

							for ($z = 0; $z < Chunk::COORD_BIT_SIZE; $z++) {
								$zGlobal = $zc * Chunk::COORD_BIT_SIZE + $z;

								$val += $vala;

								if ($val > 0) {
									$chunk->setFullBlock($xGlobal, $yGlobal, $zGlobal, $stoneId);
								} elseif ($yGlobal < $this->getSeaLevel()) {
									$chunk->setFullBlock($xGlobal, $yGlobal, $zGlobal, $waterId);
								} else {
									$chunk->setFullBlock($xGlobal, $yGlobal, $zGlobal, $airId);
								}
							}
							$_s0 += $_s0a;
							$_s1 += $_s1a;
						}

						$s0 += $s0a;
						$s1 += $s1a;
						$s2 += $s2a;
						$s3 += $s3a;
					}
				}
			}
		}
	}

	/**
	 * @param float[] $noiseBuffer
	 * @param Biome[] $biomes
	 */
	public function getHeights(array &$noiseBuffer, array $biomes, int $x, int $y, int $z) : void{
		$depthRegion = [];
		$this->depthNoise->getRegion2D($depthRegion, $x, $z, 5, 5, 200.0, 200.0);

		$mainNoiseRegion = [];
		$this->mainPerlinNoise->getRegion($mainNoiseRegion, $x, $y, $z, 5, 33, 5, 8.55515, 4.277575, 8.55515);

		$minLimitRegion = [];
		$this->minLimitPerlinNoise->getRegion($minLimitRegion, $x, $y, $z, 5, 33, 5, 684.412, 855.515, 684.412);

		$maxLimitRegion = [];
		$this->maxLimitPerlinNoise->getRegion($maxLimitRegion, $x, $y, $z, 5, 33, 5, 684.412, 684.412, 684.412);

		$noiseBufIndex = 0;
		$pp = 0;

		for ($xx = 0; $xx < 5; $xx++) {
			for ($zz = 0; $zz < 5; $zz++) {
				$scaleSum = 0;
				$depthSum = 0;
				$weightSum = 0;

				$rr = 1;

				// [RH] Perform a 5x5 approximately-gaussian smoothing kernel in order to make biome
				//      height transitions less abrupt
				$middleBiome = $biomes[($xx + 2) + ($zz + 2) * 10];

				for ($xb = -$rr; $xb <= $rr; $xb++) {
					for ($zb = -$rr; $zb <= $rr; $zb++) {
						$b = $biomes[($xx + $xb + 2) + ($zz + $zb + 2) * 10];
						$depth = $b->getDepth();
						$scale = $b->getScale();
						// We should never have amplified biomes in Pocket Edition
						//if(generator == LevelType.amplified && depth > 0) {
						//    depth = 1f + depth * 2;
						//    scale = 1 + scale * 4f;
						//}
						$weight = $this->biomeWeights[$xb + 2 + ($zb + 2) * 5] / ($depth + 2);
						if ($b->getDepth() > $middleBiome->getDepth()) {
							$weight /= 2;
						}
						$scaleSum += $scale * $weight;
						$depthSum += $depth * $weight;
						$weightSum += $weight;
					}
				}

				$scaleSum /= $weightSum;
				$depthSum /= $weightSum;

				$scaleSum = $scaleSum * 0.9 + 0.1;
				$depthSum = ($depthSum * 4 - 1) / 8.0;

				$rdepth = ($depthRegion[$pp] / 8000.0);
				if ($rdepth < 0) {
					$rdepth = -$rdepth * 0.3;
				}
				$rdepth = $rdepth * 3.0 - 2.0;

				if ($rdepth < 0) {
					$rdepth /= 2;
					if ($rdepth < -1) {
						$rdepth = -1;
					}
					$rdepth /= 1.4;
					$rdepth /= 2;
				} else {
					if ($rdepth > 1) {
						$rdepth = 1;
					}
					$rdepth /= 8;
				}

				$pp++;

				$depth = $depthSum;
				$scale = $scaleSum;
				$depth += $rdepth * 0.2;
				$depth *= 1.0625;

				$yCenter = 8.5 + $depth * 4;

				for ($yy = 0; $yy < self::NOISE_SIZE_Y; $yy++) {
					$yOffs = (($yy - ($yCenter)) * 6) / $scale;

					if ($yOffs < 0) {
						$yOffs *= 4;
					}

					$blendMin = $minLimitRegion[$noiseBufIndex] / 256.0;
					$blendMax = $maxLimitRegion[$noiseBufIndex] / 512.0;

					$blendFactor = ($mainNoiseRegion[$noiseBufIndex] / 10 + 1) / 2;
					$val = MathHelper::clampedLerp($blendMin, $blendMax, $blendFactor) - $yOffs;

					// [RH] If we're in the 4 noise segments, decrease 'val' somewhat sharply in order
					//      to ensure that there aren't truncated mountain peaks at the top of the terrain.
					if ($yy > 29) {
						$slide = ($yy - 29) / 3;
						$val = $val * (1 - $slide) + -10 * $slide;
					}

					$noiseBuffer[$noiseBufIndex] = $val;
					$noiseBufIndex++;
				}
			}
		}
	}
}
