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
use pocketmine\level\generator\vanilla\level\biome\provider\EndBiomeProvider;
use pocketmine\level\generator\vanilla\level\ChunkManager;
use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\level\generator\EndIslandNoiseGenerator;
use pocketmine\level\generator\vanilla\level\generator\Generator;
use pocketmine\level\generator\vanilla\level\generator\noise\synth\PerlinNoise;
use pocketmine\level\generator\vanilla\level\generator\noise\synth\SimplexNoise;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\Random;
use function abs;
use function intdiv;
use function max;
use function min;
use function sqrt;

class TheEnd extends Generator implements EndIslandNoiseGenerator{

	private PerlinNoise $lPerlinNoise1;
	private PerlinNoise $lPerlinNoise2;
	private PerlinNoise $perlinNoise1;
	private SimplexNoise $islandNoise;

	protected BiomeFactory $biomeFactory;

	public function init(ChunkManager $level, Random $random) : void{
		parent::init($level, $random);

		$this->lPerlinNoise1 = new PerlinNoise($random, 16);
		$this->lPerlinNoise2 = new PerlinNoise($random, 16);
		$this->perlinNoise1 = new PerlinNoise($random, 8);
		$this->islandNoise = new SimplexNoise($random);

		$this->biomeFactory = BiomeFactory::getInstance();
	}

	public function generateChunk(int $chunkX, int $chunkZ) : void{
		$this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed());

		$chunk = $this->level->getChunk($chunkX, $chunkZ);

		for($x = 0; $x < Chunk::EDGE_LENGTH; ++$x){
			for($z = 0; $z < Chunk::EDGE_LENGTH; ++$z){
				$chunk->setBiomeId($x, $z, BiomeIds::SKY);
			}
		}

		$this->prepareHeights($chunk, $chunkX, $chunkZ);
		$this->buildSurfaces($chunk);
	}

	public function populateChunk(int $chunkX, int $chunkZ) : void{
		$this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed());

		BiomeFactory::getInstance()->get(BiomeIds::SKY)->generateFeatures($this->level, $this, $this->random, new Vector3($chunkX << Chunk::COORD_BIT_SIZE, 0, $chunkZ << Chunk::COORD_BIT_SIZE));
	}

	public function getSeaLevel() : int{
		return 0;
	}

	public function getMaxBuildHeight() : int{
		return 128;
	}

	public function getIslandNoise() : ?SimplexNoise{
		return $this->islandNoise;
	}

	private function prepareHeights(Chunk $chunk, int $chunkX, int $chunkZ) : void{
		$noiseBuffer = [];
		$this->getHeights($noiseBuffer, $chunkX * 2, 0, $chunkZ * 2);

		$endStone = BlockFactory::get(BlockIds::END_STONE)->getFullId();

		for($xc = 0; $xc < 2; ++$xc){
			for($zc = 0; $zc < 2; ++$zc){
				for($yc = 0; $yc < 32; ++$yc){
					$s0 = $noiseBuffer[(($xc + 0) * 3 + $zc + 0) * 33 + $yc + 0];
					$s1 = $noiseBuffer[(($xc + 0) * 3 + $zc + 1) * 33 + $yc + 0];
					$s2 = $noiseBuffer[(($xc + 1) * 3 + $zc + 0) * 33 + $yc + 0];
					$s3 = $noiseBuffer[(($xc + 1) * 3 + $zc + 1) * 33 + $yc + 0];

					$s0a = ($noiseBuffer[(($xc + 0) * 3 + $zc + 0) * 33 + $yc + 1] - $s0) * 0.25;
					$s1a = ($noiseBuffer[(($xc + 0) * 3 + $zc + 1) * 33 + $yc + 1] - $s1) * 0.25;
					$s2a = ($noiseBuffer[(($xc + 1) * 3 + $zc + 0) * 33 + $yc + 1] - $s2) * 0.25;
					$s3a = ($noiseBuffer[(($xc + 1) * 3 + $zc + 1) * 33 + $yc + 1] - $s3) * 0.25;

					for($y = 0; $y < 4; ++$y){
						$_s0 = $s0;
						$_s1 = $s1;
						$_s0a = ($s2 - $s0) * 0.125;
						$_s1a = ($s3 - $s1) * 0.125;

						for($x = 0; $x < 8; ++$x){
							$xGlobal = $xc * 8 + $x;
							$val = $_s0;
							$vala = ($_s1 - $_s0) * 0.125;

							for($z = 0; $z < 8; ++$z){
								if($val > 0.0){
									$chunk->setFullBlock($xGlobal, $yc * 4 + $y, $zc * 8 + $z, $endStone);
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

	private function buildSurfaces(Chunk $chunk) : void{
		$endStone = BlockFactory::get(BlockIds::END_STONE)->getFullId();

		for($x = 0; $x < Chunk::EDGE_LENGTH; ++$x){
			for($z = 0; $z < Chunk::EDGE_LENGTH; ++$z){
				$run = -1;

				for($y = 127; $y >= 0; --$y){
					$block = $chunk->getFullBlock($x, $y, $z);
					if($block === 0){
						$run = -1;
					}elseif($block === $endStone){
						if($run === -1){
							$run = 1;
							$chunk->setFullBlock($x, $y, $z, $endStone);
						}elseif($run > 0){
							--$run;
							$chunk->setFullBlock($x, $y, $z, $endStone);
						}
					}
				}
			}
		}
	}

	private function getHeights(array &$noiseBuffer, int $x, int $y, int $z) : void{
		$noiseRegionPrimary = [];
		$this->perlinNoise1->getRegion($noiseRegionPrimary, $x, $y, $z, 3, 33, 3, 17.1103, 4.277575, 17.1103);
		$noiseRegionA = [];
		$this->lPerlinNoise1->getRegion($noiseRegionA, $x, $y, $z, 3, 33, 3, 1368.824, 684.412, 1368.824);
		$noiseRegionB = [];
		$this->lPerlinNoise2->getRegion($noiseRegionB, $x, $y, $z, 3, 33, 3, 1368.824, 684.412, 1368.824);

		$chunkX = intdiv($x, 2);
		$chunkZ = intdiv($z, 2);

		$p = 0;
		for($xx = 0; $xx < 3; ++$xx){
			for($zz = 0; $zz < 3; ++$zz){
				$doffs = $this->getIslandHeightValue($chunkX, $chunkZ, $xx, $zz);
				for($yy = 0; $yy < 33; ++$yy){
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

					$val -= 8.0;
					$val += $doffs;

					$r = 2;
					if($yy > intdiv(33, 2) - $r){
						$slide = ($yy - (intdiv(33, 2) - $r)) / 64.0;
						$slide = max(0.0, min(1.0, $slide));
						$val = $val * (1.0 - $slide) + -3000.0 * $slide;
					}

					$r = 8;
					if($yy < $r){
						$slide = ($r - $yy) / ($r - 1.0);
						$val = $val * (1.0 - $slide) + -30.0 * $slide;
					}

					$noiseBuffer[$p] = $val;
					++$p;
				}
			}
		}
	}

	public function getIslandHeightValue(int $chunkX, int $chunkZ, int $subSectionX, int $subSectionZ) : float{
		$xd = (float) ($chunkX * 2 + $subSectionX);
		$zd = (float) ($chunkZ * 2 + $subSectionZ);
		$doffs = 100.0 - sqrt($xd * $xd + $zd * $zd) * 8.0;
		$doffs = max(-100.0, min(80.0, $doffs));

		for($xo = -12; $xo <= 12; ++$xo){
			for($zo = -12; $zo <= 12; ++$zo){
				$totalChunkX = $chunkX + $xo;
				$totalChunkZ = $chunkZ + $zo;
				if($totalChunkX * $totalChunkX + $totalChunkZ * $totalChunkZ > 4096 && EndBiomeProvider::getRandomNoise($this->islandNoise, $totalChunkX, $totalChunkZ) < -0.9){
					$islandSize = (float) (((abs($totalChunkX) * 3439 + abs($totalChunkZ) * 147) % 13) + 9);
					$xd = (float) ($subSectionX - $xo * 2);
					$zd = (float) ($subSectionZ - $zo * 2);

					$newDoffs = 100.0 - sqrt($xd * $xd + $zd * $zd) * $islandSize;
					$newDoffs = max(-100.0, min(80.0, $newDoffs));
					if($newDoffs > $doffs){
						$doffs = $newDoffs;
					}
				}
			}
		}

		return $doffs;
	}
}
