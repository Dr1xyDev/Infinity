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

use pocketmine\level\generator\vanilla\level\generator\GenerationStageDecoration;
use pocketmine\level\generator\vanilla\level\generator\placement\CavePlacements;
use pocketmine\level\generator\vanilla\level\generator\placement\MiscOverworldPlacements;
use pocketmine\level\generator\vanilla\level\generator\placement\OrePlacements;
use pocketmine\level\generator\vanilla\level\generator\placement\PlacedFeature;
use pocketmine\level\generator\vanilla\level\generator\placement\PlacementFactory;
use pocketmine\level\generator\vanilla\level\generator\placement\VegetationPlacements;

final class DefaultBiomeFeatures {
	private function __construct(){
		//NOOP
	}

	public static function getFeatureFromName(string $name) : ?PlacedFeature {
		return PlacementFactory::getInstance()->get($name);
	}

	public static function withBadlandsStructures(BiomeGenerationSettingsBuilder $builder) : void {
		//$builder->withStructure(StructureFeatures.MINESHAFT_BADLANDS);
		//$builder->withStructure(StructureFeatures.STRONGHOLD);
	}

	public static function withStrongholdAndMineshaft(BiomeGenerationSettingsBuilder $builder) : void {
		//$builder->withStructure(StructureFeatures.MINESHAFT);
		//$builder->withStructure(StructureFeatures.STRONGHOLD);
	}

	public static function withOceanStructures(BiomeGenerationSettingsBuilder $builder) : void {
		//$builder->withStructure(StructureFeatures.MINESHAFT);
		//$builder->withStructure(StructureFeatures.SHIPWRECK);
	}

	public static function withLavaAndWaterLakes(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::LAKES, self::getFeatureFromName(MiscOverworldPlacements::LAKE_WATER));
		$builder->withFeature(GenerationStageDecoration::LAKES, self::getFeatureFromName(MiscOverworldPlacements::LAKE_LAVA));
	}

	public static function withLavaLakes(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::LAKES, self::getFeatureFromName(MiscOverworldPlacements::LAKE_LAVA));
	}

	public static function withMonsterRoom(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_STRUCTURES, self::getFeatureFromName(CavePlacements::MONSTER_ROOM));
	}

	public static function withCommonOverworldBlocks(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_ORES, self::getFeatureFromName(OrePlacements::ORE_DIRT));
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_ORES, self::getFeatureFromName(OrePlacements::ORE_GRAVEL));
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_ORES, self::getFeatureFromName(OrePlacements::ORE_GRANITE));
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_ORES, self::getFeatureFromName(OrePlacements::ORE_DIORITE));
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_ORES, self::getFeatureFromName(OrePlacements::ORE_ANDESITE));
	}

	public static function withOverworldOres(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_ORES, self::getFeatureFromName(OrePlacements::ORE_COAL));
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_ORES, self::getFeatureFromName(OrePlacements::ORE_IRON));
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_ORES, self::getFeatureFromName(OrePlacements::ORE_GOLD));
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_ORES, self::getFeatureFromName(OrePlacements::ORE_REDSTONE));
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_ORES, self::getFeatureFromName(OrePlacements::ORE_DIAMOND));
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_ORES, self::getFeatureFromName(OrePlacements::ORE_LAPIS));
	}

	public static function withExtraGoldOre(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_ORES, self::getFeatureFromName(OrePlacements::ORE_GOLD_EXTRA));
	}

	public static function withEmeraldOre(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_ORES, self::getFeatureFromName(OrePlacements::ORE_EMERALD));
	}

	public static function withInfestedStone(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(OrePlacements::ORE_INFESTED));
	}

	public static function withDisks(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_ORES, self::getFeatureFromName(MiscOverworldPlacements::DISK_SAND));
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_ORES, self::getFeatureFromName(MiscOverworldPlacements::DISK_CLAY));
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_ORES, self::getFeatureFromName(MiscOverworldPlacements::DISK_GRAVEL));
	}

	public static function withClayDisks(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_ORES, self::getFeatureFromName(MiscOverworldPlacements::DISK_CLAY));
	}

	public static function withForestRocks(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::LOCAL_MODIFICATIONS, self::getFeatureFromName(MiscOverworldPlacements::FOREST_ROCK));
	}

	public static function withLargeFern(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::LOCAL_MODIFICATIONS, self::getFeatureFromName(VegetationPlacements::PATCH_LARGE_FERN));
	}

	public static function withLightBambooVegetation(BiomeGenerationSettingsBuilder $builder) : void {
		//$builder->withFeature(GenerationStageDecoration::LOCAL_MODIFICATIONS, self::getPlacementFromName(PlacementFactory::BAMBOO_LIGHT));
	}

	public static function withBambooVegetation(BiomeGenerationSettingsBuilder $builder) : void {
		//$builder->withFeature(GenerationStageDecoration::LOCAL_MODIFICATIONS, self::getPlacementFromName(PlacementFactory::BAMBOO));
		$builder->withFeature(GenerationStageDecoration::LOCAL_MODIFICATIONS, self::getFeatureFromName(VegetationPlacements::BAMBOO_VEGETATION));
	}

	public static function withTaigaVegetation(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::LOCAL_MODIFICATIONS, self::getFeatureFromName(VegetationPlacements::TAIGA_VEGETATION));
	}

	public static function withTreesInWater(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::LOCAL_MODIFICATIONS, self::getFeatureFromName(VegetationPlacements::TREES_WATER));
	}

	public static function withBirchTrees(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::TREES_BIRCH));
	}

	public static function withForestBirchTrees(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::BIRCH_OTHER));
	}

	public static function withTallBirches(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::BIRCH_TALL));
	}

	public static function withSavannaTrees(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::TREES_SAVANNA));
	}

	public static function withShatteredSavannaTrees(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::TREES_SHATTERED_SAVANNA));
	}

	public static function withMountainTrees(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::TREES_MOUNTAIN));
	}

	public static function withMountainEdgeTrees(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::TREES_MOUNTAIN_EDGE));
	}

	public static function withJungleTrees(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::TREES_JUNGLE));
	}

	public static function withJungleEdgeTrees(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::TREES_JUNGLE_EDGE));
	}

	public static function withBadlandsOakTrees(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::OAK_BADLANDS));
	}

	public static function withSnowySpruces(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::SPRUCE_SNOWY));
	}

	public static function withJungleGrass(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::PATCH_GRASS_JUNGLE));
	}

	public static function withTallGrass(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::PATCH_TALL_GRASS));
	}

	public static function withNormalGrassPatch(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::PATCH_GRASS_NORMAL));
	}

	public static function withSavannaGrass(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::PATCH_GRASS_SAVANNA));
	}

	public static function withBadlandsGrassAndBush(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::PATCH_GRASS_BADLANDS));
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::PATCH_DEAD_BUSH_BADLANDS));
	}

	public static function withAllForestFlowerGeneration(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::FOREST_FLOWER_VEGETATION));
	}

	public static function withForestGrass(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::PATCH_GRASS_FOREST));
	}

	public static function withSwampVegetation(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::SWAMP_TREE));
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::FLOWER_SWAMP));
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::PATCH_GRASS_NORMAL));
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::PATCH_DEAD_BUSH));
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::PATCH_WATERLILY));
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::BROWN_MUSHROOM_SWAMP));
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::RED_MUSHROOM_SWAMP));
	}

	public static function withMushroomBiomeVegetation(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::MUSHROOM_FIELD_VEGETATION));
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::BROWN_MUSHROOM_TAIGA));
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::RED_MUSHROOM_TAIGA));
	}

	public static function withPlainGrassVegetation(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::PLAIN_VEGETATION));
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::FLOWER_PLAIN_DECORATED));
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::PATCH_GRASS_PLAIN));
	}

	public static function withDesertDeadBushes(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::PATCH_DEAD_BUSH_2));
	}

	public static function withGiantTaigaGrassVegetation(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::PATCH_GRASS_TAIGA));
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::PATCH_DEAD_BUSH));
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::BROWN_MUSHROOM_GIANT));
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::RED_MUSHROOM_GIANT));
	}

	public static function withDefaultFlowers(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::FLOWER_DEFAULT));
	}

	public static function withWarmFlowers(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::FLOWER_WARM));
	}

	public static function withBadlandsGrass(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::PATCH_GRASS_BADLANDS));
	}

	public static function withTaigaGrassVegetation(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::PATCH_GRASS_TAIGA_2));
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::BROWN_MUSHROOM_TAIGA));
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::RED_MUSHROOM_TAIGA));
	}

	public static function withNoiseTallGrass(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::PATCH_TALL_GRASS_2));
	}

	public static function withNormalMushroomGeneration(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::BROWN_MUSHROOM_NORMAL));
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::RED_MUSHROOM_NORMAL));
	}

	public static function withSugarCaneAndPumpkins(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::PATCH_SUGAR_CANE));
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::PATCH_PUMPKIN));
	}

	public static function withBadlandsVegetation(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::PATCH_SUGAR_CANE_BADLANDS));
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::PATCH_PUMPKIN));
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::PATCH_CACTUS_DECORATED));
	}

	public static function withMelonPatchesAndVines(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::PATCH_MELON));
		//$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getPlacementFromName(VegetationPlacements::VINES));
	}

	public static function withDesertVegetation(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::PATCH_SUGAR_CANE_DESERT));
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::PATCH_PUMPKIN));
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::PATCH_CACTUS_DESERT));
	}

	public static function withSwampSugarcaneAndPumpkin(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::PATCH_SUGAR_CANE_SWAMP));
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::PATCH_PUMPKIN));
	}

	public static function withDesertWells(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(MiscOverworldPlacements::DESERT_WELL));
	}

	public static function withFossils(BiomeGenerationSettingsBuilder $builder) : void {
		//$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getPlacementFromName(CavePlacements::FOSSIL));
	}

	public static function withColdKelp(BiomeGenerationSettingsBuilder $builder) : void {
		//TODO:
	}

	public static function withSimpleSeagrass(BiomeGenerationSettingsBuilder $builder) : void {
		//TODO:
	}

	public static function withWarmKelp(BiomeGenerationSettingsBuilder $builder) : void {
		//TODO:
	}

	public static function withLavaAndWaterSprings(BiomeGenerationSettingsBuilder $builder) : void {
		//$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getPlacementFromName(MiscOverworldPlacements::SPRING_WATER));
		//$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getPlacementFromName(MiscOverworldPlacements::SPRING_LAVA));
	}

	public static function withIcebergs(BiomeGenerationSettingsBuilder $builder) : void {
		//$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getPlacementFromName(MiscOverworldPlacements::ICEBERG_PACKED));
		//$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getPlacementFromName(MiscOverworldPlacements::ICEBERG_BLUE));
	}

	public static function withBlueIce(BiomeGenerationSettingsBuilder $builder) : void {
		//$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getPlacementFromName(MiscOverworldPlacements::BLUE_ICE));
	}

	public static function withFrozenTopLayer(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(MiscOverworldPlacements::FREEZE_TOP_LAYER));
	}

	public static function withCommonNetherBlocks(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(OrePlacements::ORE_GRAVEL_NETHER));
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(OrePlacements::ORE_SOUL_SAND));
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(OrePlacements::ORE_MAGMA));
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(OrePlacements::ORE_BLACKSTONE));
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(OrePlacements::ORE_GOLD_NETHER));
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(OrePlacements::ORE_QUARTZ_NETHER));
		DefaultBiomeFeatures::withDebrisOre($builder);
	}

	public static function withNetherDefaultVegetation(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::PATCH_FIRE));
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::BROWN_MUSHROOM_NETHER));
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(VegetationPlacements::RED_MUSHROOM_NETHER));
	}

	public static function withDebrisOre(BiomeGenerationSettingsBuilder $builder) : void {
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(OrePlacements::ORE_DEBRIS_LARGE));
		$builder->withFeature(GenerationStageDecoration::UNDERGROUND_DECORATION, self::getFeatureFromName(OrePlacements::ORE_DEBRIS_SMALL));
	}
}
