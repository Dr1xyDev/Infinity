<?php

/*
 *
 *  _                       _           _ __  __ _
 * (_)                     (_)         | |  \/  (_)
 *  _ _ __ ___   __ _  __ _ _  ___ __ _| | \  / |_ _ __   ___
 * | | '_ ` _ \ / _` |/ _` | |/ __/ _` | | |\/| | | '_ \ / _ \
 * | | | | | | | (_| | (_| | | (_| (_| | | |  | | | | | |  __/
 * |_|_| |_| |_|\__,_|\__, |_|\___\__,_|_|_|  |_|_|_| |_|\___|
 *                     __/ |
 *                    |___/
 *
 * This program is a third party build by ImagicalMine.
 *
 * PocketMine is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author ImagicalMine Team
 * @link http://forums.imagicalcorp.ml/
 *
 *
*/

namespace pocketmine\level\generator\normal;

use pocketmine\block\Block;
use pocketmine\block\CoalOre;
use pocketmine\block\DiamondOre;
use pocketmine\block\Dirt;
use pocketmine\block\GoldOre;
use pocketmine\block\Gravel;
use pocketmine\block\IronOre;
use pocketmine\block\LapisOre;
use pocketmine\block\RedstoneOre;
use pocketmine\block\Stone;
use pocketmine\level\ChunkManager;
use pocketmine\level\format\generic\BaseChunk;
use pocketmine\level\generator\biome\Biome;
use pocketmine\level\generator\biome\BiomeSelector;
use pocketmine\level\generator\Generator;
use pocketmine\level\generator\noise\Simplex;
use pocketmine\level\generator\object\OreType;
use pocketmine\level\generator\populator\Cave;
use pocketmine\level\generator\populator\GroundCover;
use pocketmine\level\generator\populator\Ore;
use pocketmine\level\generator\populator\Populator;
use pocketmine\level\Level;
use pocketmine\math\Vector3 as Vector3;
use pocketmine\utils\Memis;
use pocketmine\utils\Random;

class Normal extends Generator{
	const NAME = "Normal";

	/** @var Populator[] */
	protected $populators = [];
	/** @var ChunkManager */
	protected $level;
	/** @var Random */
	protected $random;
	protected $waterHeight = 62;
	protected $bedrockDepth = 5;

	/** @var Populator[] */
	protected $generationPopulators = [];
	/** @var Simplex */
	protected $noiseBase;
	/** @var Simplex ruido de rios (compartido por las rutas nativa y PHP) */
	protected $riverNoise;

	/** @var BiomeSelector */
	protected $selector;

	private static $GAUSSIAN_KERNEL = null;
	private static $SMOOTH_SIZE = 2;

	/** @var \ReflectionProperty|null */
	private static $sectionBlocksProp = null;
	/** @var \ReflectionProperty|null */
	private static $flatBlocksProp = null;

	public function __construct(array $options = []){
		if(self::$GAUSSIAN_KERNEL === null){
			self::generateKernel();
		}
	}

	private static function generateKernel(){
		self::$GAUSSIAN_KERNEL = [];

		$bellSize = 1 / self::$SMOOTH_SIZE;
		$bellHeight = 2 * self::$SMOOTH_SIZE;

		for($sx = -self::$SMOOTH_SIZE; $sx <= self::$SMOOTH_SIZE; ++$sx){
			self::$GAUSSIAN_KERNEL[$sx + self::$SMOOTH_SIZE] = [];

			for($sz = -self::$SMOOTH_SIZE; $sz <= self::$SMOOTH_SIZE; ++$sz){
				$bx = $bellSize * $sx;
				$bz = $bellSize * $sz;
				self::$GAUSSIAN_KERNEL[$sx + self::$SMOOTH_SIZE][$sz + self::$SMOOTH_SIZE] = $bellHeight * exp(-($bx * $bx + $bz * $bz) / 2);
			}
		}
	}

	public function getName() : string{
		return self::NAME;
	}

	public function getWaterHeight() : int{
		return $this->waterHeight;
	}

	public function getSettings(){
		return [];
	}

	public function pickBiome($x, $z){
		$hash = $x * 2345803 ^ $z * 9236449 ^ $this->level->getSeed();
		//PHP 8.4: wrap to signed 64-bit to avoid int overflow to float (deprecated implicit float->int conversion on shift)
		$hash = fmod($hash * ($hash + 223), 18446744073709551616);
		if($hash >= 9223372036854775808){ $hash -= 18446744073709551616; }
		$hash = (int) $hash;
		$xNoise = $hash >> 20 & 3;
		$zNoise = $hash >> 22 & 3;
		if($xNoise == 3){
			$xNoise = 1;
		}
		if($zNoise == 3){
			$zNoise = 1;
		}

		return $this->selector->pickBiome($x + $xNoise - 1, $z + $zNoise - 1);
	}

	public function init(ChunkManager $level, Random $random){
		$this->level = $level;
		$this->random = $random;
		$this->random->setSeed($this->level->getSeed());
		$this->noiseBase = new Simplex($this->random, 4, 1 / 4, 1 / 32);
		$this->riverNoise = new Simplex($this->random, 2, 1 / 2, 1 / 128);
		$this->random->setSeed($this->level->getSeed());
		$this->selector = new BiomeSelector($this->random, function($temperature, $rainfall){
			if($rainfall < 0.25){
				if($temperature < 0.7){
					return Biome::OCEAN;
				}elseif($temperature < 0.85){
					return Biome::RIVER;
				}else{
					return Biome::SWAMP;
				}
			}elseif($rainfall < 0.60){
				if($temperature < 0.25){
					return Biome::ICE_PLAINS;
				}elseif($temperature < 0.75){
					return Biome::PLAINS;
				}else{
					return Biome::DESERT;
				}
			}elseif($rainfall < 0.80){
				if($temperature < 0.25){
					return Biome::TAIGA;
				}elseif($temperature < 0.75){
					return Biome::FOREST;
				}else{
					return Biome::BIRCH_FOREST;
				}
			}else{
				if($temperature < 0.25){
					return Biome::MOUNTAINS;
				}elseif($temperature < 0.70){
					return Biome::SMALL_MOUNTAINS;
				}else{
					return Biome::RIVER;
				}
			}
		}, Biome::getBiome(Biome::OCEAN));

		$this->selector->addBiome(Biome::getBiome(Biome::OCEAN));
		$this->selector->addBiome(Biome::getBiome(Biome::PLAINS));
		$this->selector->addBiome(Biome::getBiome(Biome::DESERT));
		$this->selector->addBiome(Biome::getBiome(Biome::MOUNTAINS));
		$this->selector->addBiome(Biome::getBiome(Biome::FOREST));
		$this->selector->addBiome(Biome::getBiome(Biome::TAIGA));
		$this->selector->addBiome(Biome::getBiome(Biome::SWAMP));
		$this->selector->addBiome(Biome::getBiome(Biome::RIVER));
		$this->selector->addBiome(Biome::getBiome(Biome::ICE_PLAINS));
		$this->selector->addBiome(Biome::getBiome(Biome::SMALL_MOUNTAINS));
		$this->selector->addBiome(Biome::getBiome(Biome::BIRCH_FOREST));

		$this->selector->recalculate();

		$cover = new GroundCover();
		$this->generationPopulators[] = $cover;

		$cave = new Cave();
		$this->populators[] = $cave;

		$ores = new Ore();
		$ores->setOreTypes([
			new OreType(new CoalOre(), 20, 16, 0, 128),
			new OreType(New IronOre(), 20, 8, 0, 64),
			new OreType(new RedstoneOre(), 8, 7, 0, 16),
			new OreType(new LapisOre(), 1, 6, 0, 32),
			new OreType(new GoldOre(), 2, 8, 0, 32),
			new OreType(new DiamondOre(), 1, 7, 0, 16),
			new OreType(new Dirt(), 20, 32, 0, 128),
			new OreType(new Stone(Stone::GRANITE), 20, 32, 0, 128),
			new OreType(new Stone(Stone::DIORITE), 20, 32, 0, 128),
			new OreType(new Stone(Stone::ANDESITE), 20, 32, 0, 128),
			new OreType(new Gravel(), 10, 16, 0, 128)
		]);
		$this->populators[] = $ores;
	}

	public function generateChunk($chunkX, $chunkZ){
		$this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed());

		$chunk = $this->level->getChunk($chunkX, $chunkZ);

		$region = [];
		for($sx = -self::$SMOOTH_SIZE; $sx <= 16 + self::$SMOOTH_SIZE; ++$sx){
			for($sz = -self::$SMOOTH_SIZE; $sz <= 16 + self::$SMOOTH_SIZE; ++$sz){
				$region[($sx + self::$SMOOTH_SIZE) * 20 + ($sz + self::$SMOOTH_SIZE)] = $this->pickBiome($chunkX * 16 + $sx, $chunkZ * 16 + $sz);
			}
		}

		$minSumCol = [];
		$maxSumCol = [];

		for($x = 0; $x < 16; ++$x){
			for($z = 0; $z < 16; ++$z){
				$minSum = 0;
				$maxSum = 0;
				$weightSum = 0;

				$biome = $region[($x + self::$SMOOTH_SIZE) * 20 + ($z + self::$SMOOTH_SIZE)];
				$chunk->setBiomeId($x, $z, $biome->getId());
				$color = [0, 0, 0];

				for($sx = -self::$SMOOTH_SIZE; $sx <= self::$SMOOTH_SIZE; ++$sx){
					for($sz = -self::$SMOOTH_SIZE; $sz <= self::$SMOOTH_SIZE; ++$sz){

						$weight = self::$GAUSSIAN_KERNEL[$sx + self::$SMOOTH_SIZE][$sz + self::$SMOOTH_SIZE];

						if($sx === 0 and $sz === 0){
							$adjacent = $biome;
						}else{
							$adjacent = $region[($x + $sx + self::$SMOOTH_SIZE) * 20 + ($z + $sz + self::$SMOOTH_SIZE)];
						}

						$minSum += ($adjacent->getMinElevation() - 1) * $weight;
						$maxSum += $adjacent->getMaxElevation() * $weight;
						$bColor = $adjacent->getColor();
						$color[0] += (($bColor >> 16) ** 2) * $weight;
						$color[1] += ((($bColor >> 8) & 0xff) ** 2) * $weight;
						$color[2] += (($bColor & 0xff) ** 2) * $weight;

						$weightSum += $weight;
					}
				}

				$minSum /= $weightSum;
				$maxSum /= $weightSum;

				$chunk->setBiomeColor($x, $z, sqrt($color[0] / $weightSum), sqrt($color[1] / $weightSum), sqrt($color[2] / $weightSum));

				$minSumCol[($z << 4) + $x] = $minSum;
				$maxSumCol[($z << 4) + $x] = $maxSum;
			}
		}		$layout = $chunk instanceof BaseChunk ? 0 : 1;

		/* Rios vanilla: campo 2D del ruido de rio con tasa 1 (mismo camino
		 * que Generator::getFastNoise2D para que nativo y PHP coincidan). */
		$riverField = Memis::getFastNoise2D($this->riverNoise, 16, 16, 1, $chunkX * 16, 0, $chunkZ * 16);
		$riverDepth = [];
		for($x = 0; $x < 16; ++$x){
			for($z = 0; $z < 16; ++$z){
				$rv = $riverField !== null ? $riverField[$x][$z] : $this->riverNoise->noise3D($chunkX * 16 + $x, 0, $chunkZ * 16 + $z);
				$bank = abs($rv);
				if($bank < 0.04){
					$w = 1.0;
				}elseif($bank < 0.10){
					$w = 1.0 - ($bank - 0.04) / 0.06;
				}else{
					$w = 0.0;
				}
				$depth = $w * max(2.0, $this->waterHeight - 58.0);
				/* el rio solo corta cerca del nivel del mar: en montanas muy
				 * altas se desvanece para no abrir canonos raros */
				$env = 1.0 - max(0.0, min(1.0, ($maxSumCol[($z << 4) + $x] - ($this->waterHeight + 24)) / 24.0));
				$riverDepth[($z << 4) + $x] = $depth * $env;
			}
		}

		/* Envolventes vanilla por columna (indice (z << 4) + x):
		 * base + colinas + montanas (crestas) + detalle fino */
		$envA = [];
		$envR = [];
		$envM = [];
		$envD = [];
		for($i = 0; $i < 256; ++$i){
			$mn = $minSumCol[$i];
			$mx = $maxSumCol[$i];
			/* superficie centrada en el punto medio del bioma: los llanos
			 * quedan sobre el nivel del mar, el oceano debajo y las montanas
			 * suben con crestas ridged hasta su maxElevation */
			$envA[$i] = ($mn + $mx) * 0.5;
			$envR[$i] = max(1.0, ($mx - $mn) * 0.30);
			$envM[$i] = max(0.0, ($mx - $mn) * 0.45);
			$envD[$i] = 1.6;
		}

		$native = Memis::vanillaTerrain($this->noiseBase, $chunkX, $chunkZ, $envA, $envR, $envM, $envD, $riverDepth, $this->waterHeight, $layout);
		if($native !== null){
			if($layout === 0){
				$providerClass = $chunk->getProvider();
				if($providerClass === null){
					$native = null;
				}else{
					$providerClass = ($providerClass instanceof \pocketmine\level\format\LevelProvider) ? \get_class($providerClass) : $providerClass;
					for($sy = 0; $sy < 8 and $native !== null; ++$sy){
						$section = $providerClass::createChunkSection($sy);
						try{
							$prop = self::$sectionBlocksProp ?? (self::$sectionBlocksProp = new \ReflectionProperty($section, "blocks"));
							$prop->setAccessible(true);
							$prop->setValue($section, substr($native, $sy * 4096, 4096));
						}catch(\ReflectionException $e){
							$native = null;
							break;
						}
						$chunk->setSection($sy, $section);
					}
				}
			}else{
				try{
					$prop = self::$flatBlocksProp ?? (self::$flatBlocksProp = new \ReflectionProperty(\get_class($chunk), "blocks"));
					$prop->setAccessible(true);
					$prop->setValue($chunk, $native);
				}catch(\ReflectionException $e){
					$native = null;
				}
			}
			if($native !== null){
				// la ruta nativa rellena solo terreno: los populators de generacion (ground cover: cesped)
				// deben correr igual que en la ruta PHP
				foreach($this->generationPopulators as $populator){
					$populator->populate($this->level, $chunkX, $chunkZ, $this->random);
				}
				return;
			}
		}

		/* Fallback PHP: el MISMO pipeline vanilla, byte-identico */
		$field = Generator::getFastNoise2D($this->noiseBase, 16, 16, 4, $chunkX * 16, 64, $chunkZ * 16);
		$fieldDetail = Generator::getFastNoise2D($this->noiseBase, 16, 16, 2, $chunkX * 16, 96, $chunkZ * 16);
		$fieldRidge = Generator::getFastNoise2D($this->noiseBase, 16, 16, 4, $chunkX * 16, 160, $chunkZ * 16);

		for($x = 0; $x < 16; ++$x){
			for($z = 0; $z < 16; ++$z){
				$i = ($z << 4) + $x;
				$r = 1.0 - $fieldRidge[$x][$z][0];
				if($r < 0.0){
					$r = 0.0;
				}
				$r *= $r;
				$height = $envA[$i] + $field[$x][$z] * $envR[$i]
					+ $fieldRidge[$x][$z] * $envM[$i] * $r
					+ $fieldDetail[$x][$z] * $envD[$i]
					- $riverDepth[$i];
				$hi = (int) $height;
				if($hi > 127){
					$hi = 127;
				}
				if($hi < 1){
					$hi = 1;
				}
				for($y = 1; $y <= $hi; ++$y){
					$chunk->setBlockId($x, $y, $z, Block::STONE);
				}
				$chunk->setBlockId($x, 0, $z, Block::BEDROCK);
				for($y = $hi + 1; $y <= $this->waterHeight; ++$y){
					$chunk->setBlockId($x, $y, $z, Block::STILL_WATER);
				}
			}
		}

		foreach($this->generationPopulators as $populator){
			$populator->populate($this->level, $chunkX, $chunkZ, $this->random);
		}
	}

	public function populateChunk($chunkX, $chunkZ){
		$this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed());
		foreach($this->populators as $populator){
			$populator->populate($this->level, $chunkX, $chunkZ, $this->random);
		}

		$chunk = $this->level->getChunk($chunkX, $chunkZ);
		$biome = Biome::getBiome($chunk->getBiomeId(7, 7));
		$biome->populateChunk($this->level, $chunkX, $chunkZ, $this->random);
	}

	public function getSpawn(){
		return new Vector3(127.5, 128, 127.5);
	}

}