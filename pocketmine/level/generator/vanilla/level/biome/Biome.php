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

namespace pocketmine\level\generator\vanilla\level\biome;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\block\Water;
use pocketmine\level\generator\vanilla\entity\CreatureType;
use pocketmine\level\generator\vanilla\level\ChunkManager;
use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\level\generator\GenerationStageDecoration;
use pocketmine\level\generator\vanilla\level\generator\Generator;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\Random;
use function count;

class Biome {

	public static function getBiome(int $id) : Biome{
		return BiomeFactory::getInstance()->get($id);
	}

	private int $id = BiomeIds::OCEAN;

	public function __construct( //TODO: mobSpawnInfo
		protected BiomeClimate $climate,
		protected BiomeCategory $category,
		protected float $depth,
		protected float $scale,
		protected BiomeGenerationSettings $biomeGenerationSettings
	){}

	public function getId() : int {
		return $this->id;
	}

	public function setId(int $id) : void {
		$this->id = $id;
	}

	public function getPrecipitation() : RainType {
		return $this->climate->precipitation;
	}

	public function isHighHumidity() : bool {
		return $this->getDownfall() > 0.85;
	}

	public function doesWaterFreeze(ChunkManager $level, Vector3 $water, bool $mustBeAtEdge = true) : bool {
		if ($this->getTemperature() < 0.15) {
			$chunk = $level->getChunk($water->getFloorX() >> Chunk::COORD_BIT_SIZE, $water->getFloorZ() >> Chunk::COORD_BIT_SIZE);
			if ($water->getY() >= 0 && $water->getY() < 256 && $chunk->getBlockLight($water->getFloorX() & Chunk::COORD_MASK, $water->getFloorY(), $water->getFloorZ() & Chunk::COORD_MASK) < 10) {
				$block = $level->getBlockAt($water->getFloorX(), $water->getFloorY(), $water->getFloorZ());
				if ($block instanceof Water) {
					if (!$mustBeAtEdge) {
						return true;
					}

					$west = $water->west();
					$east = $water->east();
					$north = $water->north();
					$south = $water->south();

					$flag = $level->getBlockAt($west->getFloorX(), $west->getFloorY(), $west->getFloorZ()) instanceof Water &&
						$level->getBlockAt($east->getFloorX(), $east->getFloorY(), $east->getFloorZ()) instanceof Water &&
						$level->getBlockAt($north->getFloorX(), $north->getFloorY(), $north->getFloorZ()) instanceof Water &&
						$level->getBlockAt($south->getFloorX(), $south->getFloorY(), $south->getFloorZ()) instanceof Water;

					if (!$flag) {
						return true;
					}
				}
			}

		}
		return false;
	}

	public function doesSnowGenerate(ChunkManager $level, Vector3 $pos) : bool{
		if ($this->getTemperature() < 0.15) {
			$chunk = $level->getChunk($pos->getFloorX() >> Chunk::COORD_BIT_SIZE, $pos->getFloorZ() >> Chunk::COORD_BIT_SIZE);
			if ($pos->getY() >= 0 && $pos->getY() < 256 && $chunk->getBlockLight($pos->getFloorX() & Chunk::COORD_MASK, $pos->getFloorY(), $pos->getFloorZ() & Chunk::COORD_MASK) < 10) {
				$blockState = $level->getBlockAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());

				$down = $pos->down();
				$downBlock = $level->getBlockAt($down->getFloorX(), $down->getFloorY(), $down->getFloorZ());
				if ($blockState->getId() === BlockIds::AIR && ($downBlock->isSolid() || ($downBlock->getId() === $blockState->getId() && $downBlock->getDamage() === 7))) {
					return true;
				}
			}
		}

		return false;
	}

	public function getGenerationSettings() : BiomeGenerationSettings {
		return $this->biomeGenerationSettings;
	}

	public function generateFeatures(ChunkManager $level, Generator $generator, Random $random, Vector3 $pos) : void{
		$list = $this->biomeGenerationSettings->getFeatures();
		$stageCount = count(GenerationStageDecoration::cases());
		$listCount = count($list);

		for($i = 0; $i < $stageCount; ++$i) {
			$featureIndex = 0;
			if ($listCount > $i) {
				foreach ($list[$i] as $feature) {
					$feature->place($level, $generator, $random, $pos);
					++$featureIndex;
				}
			}
		}
	}

	public function buildSurface(Random $random, Chunk $chunk, int $x, int $z, int $startHeight, float $noise, Block $defaultBlock, Block $defaultFluid, int $seaLevel, int $seed) : void {
		$configuredSurfaceBuilder = $this->biomeGenerationSettings->getSurfaceBuilder();
		$configuredSurfaceBuilder->setSeed($seed);
		$configuredSurfaceBuilder->buildSurface($random, $chunk, $this, $x, $z, $startHeight, $noise, $defaultBlock, $defaultFluid, $seaLevel, $seed);
	}

	public function getDepth() : float {
		return $this->depth;
	}

	public function getDownfall() : float {
		return $this->climate->downfall;
	}

	public function getScale() : float {
		return $this->scale;
	}
	public function getTemperature() : float{
		return $this->climate->temperature;
	}

	public function getTempCategory() : TempCategory {
		if ($this->getCategory() === BiomeCategory::OCEAN) {
			return TempCategory::OCEAN;
		}

		if ($this->getTemperature() < 0.2) {
			return TempCategory::COLD;
		}

		return $this->getTemperature() < 1.0 ? TempCategory::MEDIUM : TempCategory::WARM;
	}

	public function getCategory() : BiomeCategory {
		return $this->category;
	}

	public function getSpawnableList(CreatureType $creatureType) : array{
		return []; //TODO:
	}

	public function getSpawningChance() : float{
		return 0.1;
	}
}
