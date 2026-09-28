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

use pocketmine\level\generator\vanilla\level\generator\surfacebuilders\ConfiguredSurfaceBuilderFactory;
use pocketmine\level\generator\vanilla\utils\SingletonTrait;

class BiomeFactory {
	use SingletonTrait;

	/** @var Biome[] */
	private array $biomes = [];

	public function __construct(){
		$this->register(BiomeIds::OCEAN, BiomeMaker::makeOceanBiome(false));
		$this->register(BiomeIds::PLAINS, BiomeMaker::makePlainsBiome(false));
		$this->register(BiomeIds::DESERT, BiomeMaker::makeDesertBiome(0.125, 0.05, true, true, true));
		$this->register(BiomeIds::EXTREME_HILLS, BiomeMaker::makeMountainBiome(1.0, 0.5, ConfiguredSurfaceBuilderFactory::MOUNTAIN, false));
		$this->register(BiomeIds::FOREST, BiomeMaker::makeForestBiome(0.1, 0.2));
		$this->register(BiomeIds::TAIGA, BiomeMaker::makeTaigaBiome(0.2, 0.2, false, true, false));
		$this->register(BiomeIds::SWAMPLAND, BiomeMaker::makeGenericSwampBiome(-0.2, 0.1, false));
		$this->register(BiomeIds::RIVER, BiomeMaker::makeRiverBiome(-0.5, 0.0, 0.5, false));
		$this->register(BiomeIds::HELL, BiomeMaker::makeHellBiome());
		$this->register(BiomeIds::SKY, BiomeMaker::makeTheEndBiome());
		$this->register(BiomeIds::FROZEN_OCEAN, BiomeMaker::makeFrozenOceanBiome());
		$this->register(BiomeIds::FROZEN_RIVER, BiomeMaker::makeRiverBiome(-0.5, 0.0, 0.0, true));
		$this->register(BiomeIds::ICE_FLATS, BiomeMaker::makeSnowyBiome(0.125, 0.05, false, false));
		$this->register(BiomeIds::ICE_MOUNTAINS, BiomeMaker::makeSnowyBiome(0.45, 0.3, false, true));
		$this->register(BiomeIds::MUSHROOM_ISLAND, BiomeMaker::makeMushroomBiome(0.2, 0.3));
		$this->register(BiomeIds::MUSHROOM_ISLAND_SHORE, BiomeMaker::makeMushroomBiome(0.0, 0.025));
		$this->register(BiomeIds::BEACHES, BiomeMaker::makeGenericBeachBiome(0.0, 0.025, 0.8, 0.4, false, false));
		$this->register(BiomeIds::DESERT_HILLS, BiomeMaker::makeDesertBiome(0.45, 0.3, false, true, false));
		$this->register(BiomeIds::FOREST_HILLS, BiomeMaker::makeForestBiome(0.45, 0.3));
		$this->register(BiomeIds::TAIGA_HILLS, BiomeMaker::makeTaigaBiome(0.45, 0.3, false, false, false));
		$this->register(BiomeIds::SMALLER_EXTREME_HILLS, BiomeMaker::makeMountainBiome(0.8, 0.3, ConfiguredSurfaceBuilderFactory::GRASS, true));
		$this->register(BiomeIds::JUNGLE, BiomeMaker::makeJungleBiome());
		$this->register(BiomeIds::JUNGLE_HILLS, BiomeMaker::makeJungleHillsBiome());
		$this->register(BiomeIds::JUNGLE_EDGE, BiomeMaker::makeJungleEdgeBiome());
		$this->register(BiomeIds::DEEP_OCEAN, BiomeMaker::makeOceanBiome(true));
		$this->register(BiomeIds::STONE_BEACH, BiomeMaker::makeGenericBeachBiome(0.1, 0.8, 0.2, 0.3, false, true));
		$this->register(BiomeIds::COLD_BEACH, BiomeMaker::makeGenericBeachBiome(0.0, 0.025, 0.05, 0.3, true, false));
		$this->register(BiomeIds::BIRCH_FOREST, BiomeMaker::makeBirchForestBiome(0.1, 0.2, false));
		$this->register(BiomeIds::BIRCH_FOREST_HILLS, BiomeMaker::makeBirchForestBiome(0.45, 0.3, false));
		$this->register(BiomeIds::ROOFED_FOREST, BiomeMaker::makeDarkForestBiome(0.1, 0.2, false));
		$this->register(BiomeIds::TAIGA_COLD, BiomeMaker::makeTaigaBiome(0.2, 0.2, true, false, true));
		$this->register(BiomeIds::TAIGA_COLD_HILLS, BiomeMaker::makeTaigaBiome(0.45, 0.3, true, false, false));
		$this->register(BiomeIds::REDWOOD_TAIGA, BiomeMaker::makeGiantTaigaBiome(0.2, 0.2, 0.3, false));
		$this->register(BiomeIds::REDWOOD_TAIGA_HILLS, BiomeMaker::makeGiantTaigaBiome(0.45, 0.3, 0.3, false));
		$this->register(BiomeIds::EXTREME_HILLS_WITH_TREES, BiomeMaker::makeMountainBiome(1.0, 0.5, ConfiguredSurfaceBuilderFactory::GRASS, true));
		$this->register(BiomeIds::SAVANNA, BiomeMaker::makeGenericSavannaBiome(0.125, 0.05, 1.2, false, false));
		$this->register(BiomeIds::SAVANNA_ROCK, BiomeMaker::makeSavannaPlateauBiome());
		$this->register(BiomeIds::MESA, BiomeMaker::makeBadlandsBiome(0.1, 0.2, false));
		$this->register(BiomeIds::MESA_ROCK, BiomeMaker::makeWoodedBadlandsPlateauBiome(1.5, 0.025));
		$this->register(BiomeIds::MESA_CLEAR_ROCK, BiomeMaker::makeBadlandsBiome(1.5, 0.025, true));

		//mutated
		$this->register(BiomeIds::SUNFLOWER_PLAINS, BiomeMaker::makePlainsBiome(true));
		$this->register(BiomeIds::DESERT_LAKES, BiomeMaker::makeDesertBiome(0.225, 0.25, false, false, false));
		$this->register(BiomeIds::GRAVELLY_MOUNTAINS, BiomeMaker::makeMountainBiome(1.0, 0.5, ConfiguredSurfaceBuilderFactory::GRAVELLY_MOUNTAIN, false));
		$this->register(BiomeIds::FLOWER_FOREST, BiomeMaker::makeFlowerForestBiome());
		$this->register(BiomeIds::TAIGA_MOUNTAINS, BiomeMaker::makeTaigaBiome(0.3, 0.4, false, false, false));
		$this->register(BiomeIds::SWAMP_HILLS, BiomeMaker::makeGenericSwampBiome(-0.1, 0.3, true));
		$this->register(BiomeIds::ICE_SPIKES, BiomeMaker::makeSnowyBiome(0.425, 0.45000002, true, false));
		$this->register(BiomeIds::MODIFIED_JUNGLE, BiomeMaker::makeModifiedJungleBiome());
		$this->register(BiomeIds::MODIFIED_JUNGLE_EDGE, BiomeMaker::makeModifiedJungleEdgeBiome());
		$this->register(BiomeIds::TALL_BIRCH_FOREST, BiomeMaker::makeBirchForestBiome(0.2, 0.4, true));
		$this->register(BiomeIds::TALL_BIRCH_HILLS, BiomeMaker::makeBirchForestBiome(0.55, 0.5, true));
		$this->register(BiomeIds::DARK_FOREST_HILLS, BiomeMaker::makeDarkForestBiome(0.2, 0.4, true));
		$this->register(BiomeIds::SNOWY_TAIGA_MOUNTAINS, BiomeMaker::makeTaigaBiome(0.3, 0.4, true, false, false));
		$this->register(BiomeIds::GIANT_SPRUCE_TAIGA, BiomeMaker::makeGiantTaigaBiome(0.2, 0.2, 0.25, true));
		$this->register(BiomeIds::GIANT_SPRUCE_TAIGA_HILLS, BiomeMaker::makeGiantTaigaBiome(0.2, 0.2, 0.25, true));
		$this->register(BiomeIds::MODIFIED_GRAVELLY_MOUNTAINS, BiomeMaker::makeMountainBiome(1.0, 0.5, ConfiguredSurfaceBuilderFactory::GRAVELLY_MOUNTAIN, false));
		$this->register(BiomeIds::SHATTERED_SAVANNA, BiomeMaker::makeGenericSavannaBiome(0.3625, 1.225, 1.1, true, true));
		$this->register(BiomeIds::SHATTERED_SAVANNA_PLATEAU, BiomeMaker::makeGenericSavannaBiome(1.05, 1.2125001, 1.0, true, true));
		$this->register(BiomeIds::ERODED_BADLANDS, BiomeMaker::makeErodedBadlandsBiome());
		$this->register(BiomeIds::MODIFIED_WOODED_BADLANDS_PLATEAU, BiomeMaker::makeWoodedBadlandsPlateauBiome(0.45, 0.3));
		$this->register(BiomeIds::MODIFIED_BADLANDS_PLATEAU, BiomeMaker::makeBadlandsBiome(0.45, 0.3, true));
	}

	public function get(int $id) : Biome {
		return $this->biomes[$id] ?? $this->biomes[BiomeIds::PLAINS];
	}

	public function register(int $id, Biome $biome) : void {
		$this->biomes[$id] = $biome;
		$biome->setId($id);
	}
}
