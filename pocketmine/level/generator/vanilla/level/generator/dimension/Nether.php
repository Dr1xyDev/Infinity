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

use pocketmine\level\generator\vanilla\block\BlockFactory;
use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\level\biome\BiomeFactory;
use pocketmine\level\generator\vanilla\level\biome\BiomeIds;
use pocketmine\level\generator\vanilla\level\ChunkManager;
use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\level\generator\cave\LegacyNetherCaveGenerator;
use pocketmine\level\generator\vanilla\level\generator\Generator;
use pocketmine\level\generator\vanilla\level\generator\noise\synth\PerlinNoise;
use pocketmine\level\generator\vanilla\level\generator\structure\ChunkStructureWorld;
use pocketmine\level\generator\vanilla\level\generator\structure\fortress\MapGenNetherFortress;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\Random;
use function cos;
use function intdiv;
use function pi;

class Nether extends Generator{

	private PerlinNoise $lPerlinNoise1;
	private PerlinNoise $lPerlinNoise2;
	private PerlinNoise $perlinNoise1;
	private PerlinNoise $perlinNoise2;
	private PerlinNoise $perlinNoise3;
	private PerlinNoise $scaleNoise;
	private PerlinNoise $depthNoise;

	protected BiomeFactory $biomeFactory;
	protected LegacyNetherCaveGenerator $caveGenerator;
	protected MapGenNetherFortress $fortresses;

	public function init(ChunkManager $level, Random $random) : void{
		parent::init($level, $random);

		$this->lPerlinNoise1 = new PerlinNoise($random, 16);
		$this->lPerlinNoise2 = new PerlinNoise($random, 16);
		$this->perlinNoise1 = new PerlinNoise($random, 8);
		$this->perlinNoise2 = new PerlinNoise($random, 4);
		$this->perlinNoise3 = new PerlinNoise($random, 4);
		$this->scaleNoise = new PerlinNoise($random, 10);
		$this->depthNoise = new PerlinNoise($random, 16);

		$this->biomeFactory = BiomeFactory::getInstance();
		$this->caveGenerator = new LegacyNetherCaveGenerator();
		$this->fortresses = new MapGenNetherFortress($level->getSeed());
	}

	public function getFortresses() : MapGenNetherFortress{
		return $this->fortresses;
	}

	public function generateChunk(int $chunkX, int $chunkZ) : void{
		$this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed());

		$chunk = $this->level->getChunk($chunkX, $chunkZ);

		for($x = 0; $x < Chunk::EDGE_LENGTH; ++$x){
			for($z = 0; $z < Chunk::EDGE_LENGTH; ++$z){
				$chunk->setBiomeId($x, $z, BiomeIds::HELL);
			}
		}

		$this->prepareHeights($chunk, $chunkX, $chunkZ);
		$this->buildSurfaces($chunk, $chunkX, $chunkZ);
		$this->caveGenerator->apply($chunk, $chunkX, $chunkZ, $this->level->getSeed());

		$structureWorld = new ChunkStructureWorld($chunk, $this->getMaxBuildHeight() - 1);
		$this->fortresses->generate($structureWorld, $structureWorld->getBoundingBox(), $chunkX, $chunkZ);
	}

	public function populateChunk(int $chunkX, int $chunkZ) : void{
		$this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed());

		$this->biomeFactory->get(BiomeIds::HELL)->generateFeatures($this->level, $this, $this->random, new Vector3($chunkX << Chunk::COORD_BIT_SIZE, 0, $chunkZ << Chunk::COORD_BIT_SIZE));
	}

	public function getSeaLevel() : int{
		return 32;
	}

	public function getMaxBuildHeight() : int{
		return 128;
	}

	private function prepareHeights(Chunk $chunk, int $chunkX, int $chunkZ) : void{
		$noiseBuffer = [];
		$this->getHeights($noiseBuffer, $chunkX * Chunk::COORD_BIT_SIZE, 0, $chunkZ * Chunk::COORD_BIT_SIZE);

		$netherrack = BlockFactory::get(BlockIds::NETHERRACK)->getFullId();
		$lava = BlockFactory::get(BlockIds::LAVA)->getFullId();
		$air = 0;

		for($xc = 0; $xc < 4; ++$xc){
			for($zc = 0; $zc < 4; ++$zc){
				for($yc = 0; $yc < 16; ++$yc){
					$s0 = $noiseBuffer[(($xc + 0) * 5 + ($zc + 0)) * 17 + ($yc + 0)];
					$s1 = $noiseBuffer[(($xc + 0) * 5 + ($zc + 1)) * 17 + ($yc + 0)];
					$s2 = $noiseBuffer[(($xc + 1) * 5 + ($zc + 0)) * 17 + ($yc + 0)];
					$s3 = $noiseBuffer[(($xc + 1) * 5 + ($zc + 1)) * 17 + ($yc + 0)];

					$s0a = ($noiseBuffer[(($xc + 0) * 5 + ($zc + 0)) * 17 + ($yc + 1)] - $s0) * 0.125;
					$s1a = ($noiseBuffer[(($xc + 0) * 5 + ($zc + 1)) * 17 + ($yc + 1)] - $s1) * 0.125;
					$s2a = ($noiseBuffer[(($xc + 1) * 5 + ($zc + 0)) * 17 + ($yc + 1)] - $s2) * 0.125;
					$s3a = ($noiseBuffer[(($xc + 1) * 5 + ($zc + 1)) * 17 + ($yc + 1)] - $s3) * 0.125;

					for($y = 0; $y < 8; ++$y){
						$_s0 = $s0;
						$_s1 = $s1;
						$_s0a = ($s2 - $s0) * 0.25;
						$_s1a = ($s3 - $s1) * 0.25;

						for($x = 0; $x < 4; ++$x){
							$xGlobal = $xc * 4 + $x;
							$val = $_s0;
							$vala = ($_s1 - $_s0) * 0.25;

							for($z = 0; $z < 4; ++$z){
								$yGlobal = $yc * 8 + $y;
								$zGlobal = $zc * 4 + $z;
								$block = $air;

								if($yGlobal < $this->getSeaLevel()){
									$block = $lava;
								}
								if($val > 0.0){
									$block = $netherrack;
								}

								if($block !== $air){
									$chunk->setFullBlock($xGlobal, $yGlobal, $zGlobal, $block);
								}

								$val += $vala;
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

	private function buildSurfaces(Chunk $chunk, int $chunkX, int $chunkZ) : void{
		$chunkBlockX = $chunkX << Chunk::COORD_BIT_SIZE;
		$chunkBlockZ = $chunkZ << Chunk::COORD_BIT_SIZE;

		$sandBuffer = [];
		$this->perlinNoise2->getRegion($sandBuffer, $chunkBlockX, 0.0, $chunkBlockZ, 16, 1, 16, 1.0, 1.0, 1.0);
		$gravelBuffer = [];
		$this->perlinNoise2->getRegion($gravelBuffer, $chunkBlockX, 109.0, $chunkBlockZ, 16, 1, 16, 1.0, 1.0, 1.0);
		$depthBuffer = [];
		$this->perlinNoise3->getRegion($depthBuffer, $chunkBlockX, 0.0, $chunkBlockZ, 16, 1, 16, 2.0, 1.0, 2.0);

		$netherrack = BlockFactory::get(BlockIds::NETHERRACK)->getFullId();
		$gravel = BlockFactory::get(BlockIds::GRAVEL)->getFullId();
		$soulSand = BlockFactory::get(BlockIds::SOUL_SAND)->getFullId();
		$lava = BlockFactory::get(BlockIds::LAVA)->getFullId();
		$bedrock = BlockFactory::get(BlockIds::BEDROCK)->getFullId();
		$air = 0;
		$waterHeight = $this->getSeaLevel();

		for($x = 0; $x < Chunk::EDGE_LENGTH; ++$x){
			for($z = 0; $z < Chunk::EDGE_LENGTH; ++$z){
				$index = $x + $z * Chunk::EDGE_LENGTH;
				$sand = ($sandBuffer[$index] + $this->random->nextFloat() * 0.2) > 0.0;
				$isGravel = ($gravelBuffer[$index] + $this->random->nextFloat() * 0.2) > 0.0;
				$runDepth = (int) ($depthBuffer[$index] / 3.0 + 3.0 + $this->random->nextFloat() * 0.25);
				$run = -1;
				$top = $netherrack;
				$material = $netherrack;

				for($y = 127; $y >= 0; --$y){
					if($y >= 127 - $this->random->nextBoundedInt(5) || $y <= $this->random->nextBoundedInt(5)){
						$chunk->setFullBlock($x, $y, $z, $bedrock);
						continue;
					}

					$oldBlock = $chunk->getFullBlock($x, $y, $z);
					if($oldBlock === $air){
						$run = -1;
					}elseif($oldBlock === $netherrack){
						if($run === -1){
							if($runDepth <= 0){
								$top = $air;
								$material = $netherrack;
							}elseif($y >= $waterHeight - 4 && $y <= $waterHeight + 1){
								$top = $netherrack;
								$material = $netherrack;
								if($isGravel){
									$top = $gravel;
								}
								if($sand){
									$top = $soulSand;
									$material = $soulSand;
								}
							}

							if($y < $waterHeight && $top === $air){
								$top = $lava;
							}

							$run = $runDepth;
							$chunk->setFullBlock($x, $y, $z, $y >= $waterHeight - 1 ? $top : $material);
						}elseif($run > 0){
							--$run;
							$chunk->setFullBlock($x, $y, $z, $material);
						}
					}
				}
			}
		}
	}

	private function getHeights(array &$noiseBuffer, int $x, int $y, int $z) : void{
		$scaleRegion = [];
		$this->scaleNoise->getRegion($scaleRegion, $x, $y, $z, 5, 1, 5, 1.0, 0.0, 1.0);
		$depthRegion = [];
		$this->depthNoise->getRegion($depthRegion, $x, $y, $z, 5, 1, 5, 100.0, 0.0, 100.0);

		$noiseRegionPrimary = [];
		$this->perlinNoise1->getRegion($noiseRegionPrimary, $x, $y, $z, 5, 17, 5, 8.55515, 34.2206, 8.55515);
		$noiseRegionA = [];
		$this->lPerlinNoise1->getRegion($noiseRegionA, $x, $y, $z, 5, 17, 5, 684.412, 2053.236, 684.412);
		$noiseRegionB = [];
		$this->lPerlinNoise2->getRegion($noiseRegionB, $x, $y, $z, 5, 17, 5, 684.412, 2053.236, 684.412);

		$yOffs = [];
		for($yy = 0; $yy < 17; ++$yy){
			$yOffs[$yy] = cos($yy * pi() * 6.0 / 17) * 2.0;
			$dd = (float) $yy;
			if($yy > intdiv(17, 2)){
				$dd = (float) (16 - $yy);
			}
			if($dd < 4.0){
				$dd = 4.0 - $dd;
				$yOffs[$yy] -= $dd * $dd * $dd * 10.0;
			}
		}

		$p = 0;
		for($xx = 0; $xx < 5; ++$xx){
			for($zz = 0; $zz < 5; ++$zz){
				$floating = 0.0;
				for($yy = 0; $yy < 17; ++$yy){
					$bb = $noiseRegionA[$p] / 512.0;
					$cc = $noiseRegionB[$p] / 512.0;
					$v = ($noiseRegionPrimary[$p] / 10.0 + 1.0) / 2.0;

					if($v < 0.0){
						$val = $bb;
					}elseif($v > 1.0){
						$val = $cc;
					}else{
						$val = $bb + ($cc - $bb) * $v;
					}

					$val -= $yOffs[$yy];

					if($yy > 13){
						$slide = ($yy - 13) / 3.0;
						$val = $val * (1.0 - $slide) + -10.0 * $slide;
					}

					if($yy < $floating){
						$slide = ($floating - $yy) / 4.0;
						if($slide < 0.0){
							$slide = 0.0;
						}elseif($slide > 1.0){
							$slide = 1.0;
						}
						$val = $val * (1.0 - $slide) + -10.0 * $slide;
					}

					$noiseBuffer[$p] = $val;
					++$p;
				}
			}
		}
	}
}
