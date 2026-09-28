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
use pocketmine\level\generator\vanilla\level\generator\placement\MiscOverworldPlacements;
use pocketmine\level\generator\vanilla\level\generator\placement\VegetationPlacements;
use pocketmine\level\generator\vanilla\level\generator\surfacebuilders\ConfiguredSurfaceBuilderFactory;

class BiomeMaker {

	public static function makeGiantTaigaBiome(float $depth, float $scale, float $temperature, bool $isSpruceVariant) : Biome {
		$biomeGenerationSettingsBuilder = (new BiomeGenerationSettingsBuilder())->withSurfaceBuilder(ConfiguredSurfaceBuilderFactory::getInstance()->get(ConfiguredSurfaceBuilderFactory::GIANT_TREE_TAIGA));
		DefaultBiomeFeatures::withStrongholdAndMineshaft($biomeGenerationSettingsBuilder);
		//$biomeGenerationSettingsBuilder.withStructure(StructureFeatures.RUINED_PORTAL);
		DefaultBiomeFeatures::withLavaAndWaterLakes($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withMonsterRoom($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withForestRocks($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withLargeFern($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withCommonOverworldBlocks($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withOverworldOres($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withDisks($biomeGenerationSettingsBuilder);
		$biomeGenerationSettingsBuilder->withFeature(GenerationStageDecoration::VEGETAL_DECORATION, DefaultBiomeFeatures::getFeatureFromName($isSpruceVariant ? VegetationPlacements::TREES_GIANT_SPRUCE : VegetationPlacements::TREES_GIANT));
		DefaultBiomeFeatures::withDefaultFlowers($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withGiantTaigaGrassVegetation($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withNormalMushroomGeneration($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withSugarCaneAndPumpkins($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withLavaAndWaterSprings($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withFrozenTopLayer($biomeGenerationSettingsBuilder);
		return (new BiomeBuilder())
			->precipitation(RainType::RAIN)
			->category(BiomeCategory::TAIGA)
			->depth($depth)
			->scale($scale)
			->temperature($temperature)
			->downfall(0.8)
			->withGenerationSettings($biomeGenerationSettingsBuilder->build())
			->build();
	}

	public static function makeBirchForestBiome(float $depth, float $scale, bool $isTallVariant) : Biome {
		$biomeGenerationSettingsBuilder = (new BiomeGenerationSettingsBuilder())->withSurfaceBuilder(ConfiguredSurfaceBuilderFactory::getInstance()->get(ConfiguredSurfaceBuilderFactory::GRASS));
		DefaultBiomeFeatures::withStrongholdAndMineshaft($biomeGenerationSettingsBuilder);
		//$biomeGenerationSettingsBuilder.withStructure(StructureFeatures.RUINED_PORTAL);
		DefaultBiomeFeatures::withLavaAndWaterLakes($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withMonsterRoom($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withAllForestFlowerGeneration($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withCommonOverworldBlocks($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withOverworldOres($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withDisks($biomeGenerationSettingsBuilder);
		if ($isTallVariant) {
			DefaultBiomeFeatures::withTallBirches($biomeGenerationSettingsBuilder);
		} else {
			DefaultBiomeFeatures::withBirchTrees($biomeGenerationSettingsBuilder);
		}

		DefaultBiomeFeatures::withDefaultFlowers($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withForestGrass($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withNormalMushroomGeneration($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withSugarCaneAndPumpkins($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withLavaAndWaterSprings($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withFrozenTopLayer($biomeGenerationSettingsBuilder);
		return (new BiomeBuilder())
			->precipitation(RainType::RAIN)
			->category(BiomeCategory::FOREST)
			->depth($depth)
			->scale($scale)
			->temperature(0.6)
			->downfall(0.6)
			->withGenerationSettings($biomeGenerationSettingsBuilder->build())
			->build();
	}

	public static function makeJungleBiome() : Biome {
		return self::makeGenericJungleBiome(0.1, 0.2);
	}

	public static function makeJungleEdgeBiome() : Biome {
		return self::makeTropicalBiome(0.1, 0.2, 0.8, false, true, false);
	}

	public static function makeModifiedJungleEdgeBiome() : Biome {
		return self::makeTropicalBiome(0.2, 0.4, 0.8, false, true, true);
	}

	public static function makeModifiedJungleBiome() : Biome {
		return self::makeTropicalBiome(0.2, 0.4, 0.9, false, false, true);
	}

	public static function makeJungleHillsBiome() : Biome {
		return self::makeGenericJungleBiome(0.45, 0.3);
	}

	private static function makeGenericJungleBiome(float $depth, float $scale) : Biome {
		return self::makeTropicalBiome($depth, $scale, 0.9, false, false, false);
	}

	private static function makeGenericBambooBiome(float $depth, float $scale) : Biome {
		return self::makeTropicalBiome($depth, $scale, 0.9, true, false, false);
	}

	private static function makeTropicalBiome(float $depth, float $scale, float $downfall, bool $hasOnlyBambooVegetation, bool $isEdgeBiome, bool $isModified) : Biome {
		$biomeGenerationSettingsBuilder = (new BiomeGenerationSettingsBuilder())->withSurfaceBuilder(ConfiguredSurfaceBuilderFactory::getInstance()->get(ConfiguredSurfaceBuilderFactory::GRASS));
		if (!$isEdgeBiome && !$isModified) {
			//$biomeGenerationSettingsBuilder.withStructure(StructureFeatures.JUNGLE_PYRAMID);
		}

		DefaultBiomeFeatures::withStrongholdAndMineshaft($biomeGenerationSettingsBuilder);
		//$biomeGenerationSettingsBuilder.withStructure(StructureFeatures.RUINED_PORTAL_JUNGLE);
		DefaultBiomeFeatures::withLavaAndWaterLakes($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withMonsterRoom($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withCommonOverworldBlocks($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withOverworldOres($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withDisks($biomeGenerationSettingsBuilder);
		if ($hasOnlyBambooVegetation) {
			DefaultBiomeFeatures::withBambooVegetation($biomeGenerationSettingsBuilder);
		} else {
			if (!$isEdgeBiome && !$isModified) {
				DefaultBiomeFeatures::withLightBambooVegetation($biomeGenerationSettingsBuilder);
			}

			if ($isEdgeBiome) {
				DefaultBiomeFeatures::withJungleEdgeTrees($biomeGenerationSettingsBuilder);
			} else {
				DefaultBiomeFeatures::withJungleTrees($biomeGenerationSettingsBuilder);
			}
		}

		DefaultBiomeFeatures::withWarmFlowers($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withJungleGrass($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withNormalMushroomGeneration($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withSugarCaneAndPumpkins($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withLavaAndWaterSprings($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withMelonPatchesAndVines($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withFrozenTopLayer($biomeGenerationSettingsBuilder);
		return (new BiomeBuilder())
			->precipitation(RainType::RAIN)
			->category(BiomeCategory::JUNGLE)
			->depth($depth)
			->scale($scale)
			->temperature(0.95)
			->downfall($downfall)
			->withGenerationSettings($biomeGenerationSettingsBuilder->build())
			->build();
	}

	public static function makeMountainBiome(float $depth, float $scale, string $surfaceBuilder, bool $isEdgeBiome) : Biome {
		$biomeGenerationSettingsBuilder = (new BiomeGenerationSettingsBuilder())->withSurfaceBuilder(ConfiguredSurfaceBuilderFactory::getInstance()->get($surfaceBuilder));
		DefaultBiomeFeatures::withStrongholdAndMineshaft($biomeGenerationSettingsBuilder);
		//$biomeGenerationSettingsBuilder.withStructure(StructureFeatures.RUINED_PORTAL_MOUNTAIN);
		DefaultBiomeFeatures::withLavaAndWaterLakes($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withMonsterRoom($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withCommonOverworldBlocks($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withOverworldOres($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withDisks($biomeGenerationSettingsBuilder);
		if ($isEdgeBiome) {
			DefaultBiomeFeatures::withMountainEdgeTrees($biomeGenerationSettingsBuilder);
		} else {
			DefaultBiomeFeatures::withMountainTrees($biomeGenerationSettingsBuilder);
		}

		DefaultBiomeFeatures::withDefaultFlowers($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withBadlandsGrass($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withNormalMushroomGeneration($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withSugarCaneAndPumpkins($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withLavaAndWaterSprings($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withEmeraldOre($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withInfestedStone($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withFrozenTopLayer($biomeGenerationSettingsBuilder);
		return (new BiomeBuilder())
			->precipitation(RainType::RAIN)
			->category(BiomeCategory::EXTREME_HILLS)
			->depth($depth)
			->scale($scale)
			->temperature(0.2)
			->downfall(0.3)
			->withGenerationSettings($biomeGenerationSettingsBuilder->build())
			->build();
	}

	public static function makeDesertBiome(float $depth, float $scale, bool $hasVillageAndOutpost, bool $hasDesertPyramid, bool $hasFossils) : Biome {
		$biomeGenerationSettingsBuilder = (new BiomeGenerationSettingsBuilder())->withSurfaceBuilder(ConfiguredSurfaceBuilderFactory::getInstance()->get(ConfiguredSurfaceBuilderFactory::DESERT));
		if ($hasVillageAndOutpost) {
			//$biomeGenerationSettingsBuilder.withStructure(StructureFeatures.VILLAGE_DESERT);
			//$biomeGenerationSettingsBuilder.withStructure(StructureFeatures.PILLAGER_OUTPOST);
		}

		if ($hasDesertPyramid) {
			//$biomeGenerationSettingsBuilder.withStructure(StructureFeatures.DESERT_PYRAMID);
		}

		if ($hasFossils) {
			DefaultBiomeFeatures::withFossils($biomeGenerationSettingsBuilder);
		}

		DefaultBiomeFeatures::withStrongholdAndMineshaft($biomeGenerationSettingsBuilder);
		//$biomeGenerationSettingsBuilder->withStructure(StructureFeatures.RUINED_PORTAL_DESERT);
		DefaultBiomeFeatures::withLavaLakes($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withMonsterRoom($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withCommonOverworldBlocks($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withOverworldOres($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withDisks($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withDefaultFlowers($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withBadlandsGrass($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withDesertDeadBushes($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withNormalMushroomGeneration($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withDesertVegetation($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withLavaAndWaterSprings($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withDesertWells($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withFrozenTopLayer($biomeGenerationSettingsBuilder);
		return (new BiomeBuilder())
			->precipitation(RainType::NONE)
			->category(BiomeCategory::DESERT)
			->depth($depth)
			->scale($scale)
			->temperature(2.0)
			->downfall(0.0)
			->withGenerationSettings($biomeGenerationSettingsBuilder->build())
			->build();
	}

	public static function makePlainsBiome(bool $isSunflowerVariant) : Biome {
		$biomeGenerationSettingsBuilder = (new BiomeGenerationSettingsBuilder())->withSurfaceBuilder(ConfiguredSurfaceBuilderFactory::getInstance()->get(ConfiguredSurfaceBuilderFactory::GRASS));
		if (!$isSunflowerVariant) {
			//$biomeGenerationSettingsBuilder.withStructure(StructureFeatures.VILLAGE_PLAINS).withStructure(StructureFeatures.PILLAGER_OUTPOST);
		}

		DefaultBiomeFeatures::withStrongholdAndMineshaft($biomeGenerationSettingsBuilder);
		//$biomeGenerationSettingsBuilder.withStructure(StructureFeatures.RUINED_PORTAL);
		DefaultBiomeFeatures::withLavaAndWaterLakes($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withMonsterRoom($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withNoiseTallGrass($biomeGenerationSettingsBuilder);
		if ($isSunflowerVariant) {
			$biomeGenerationSettingsBuilder->withFeature(GenerationStageDecoration::VEGETAL_DECORATION, DefaultBiomeFeatures::getFeatureFromName(VegetationPlacements::PATCH_SUNFLOWER));
		}

		DefaultBiomeFeatures::withCommonOverworldBlocks($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withOverworldOres($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withDisks($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withPlainGrassVegetation($biomeGenerationSettingsBuilder);
		if ($isSunflowerVariant) {
			$biomeGenerationSettingsBuilder->withFeature(GenerationStageDecoration::VEGETAL_DECORATION, DefaultBiomeFeatures::getFeatureFromName(VegetationPlacements::PATCH_SUGAR_CANE));
		}

		DefaultBiomeFeatures::withNormalMushroomGeneration($biomeGenerationSettingsBuilder);
		if ($isSunflowerVariant) {
			$biomeGenerationSettingsBuilder->withFeature(GenerationStageDecoration::VEGETAL_DECORATION, DefaultBiomeFeatures::getFeatureFromName(VegetationPlacements::PATCH_PUMPKIN));
		} else {
			DefaultBiomeFeatures::withSugarCaneAndPumpkins($biomeGenerationSettingsBuilder);
		}

		DefaultBiomeFeatures::withLavaAndWaterSprings($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withFrozenTopLayer($biomeGenerationSettingsBuilder);
		return (new BiomeBuilder())
			->precipitation(RainType::RAIN)
			->category(BiomeCategory::PLAINS)
			->depth(0.125)
			->scale(0.05)
			->temperature(0.8)
			->downfall(0.4)
			->withGenerationSettings($biomeGenerationSettingsBuilder->build())
			->build();
	}

	public static function makeMushroomBiome(float $depth, float $scale) : Biome {
		$biomeGenerationSettingsBuilder = (new BiomeGenerationSettingsBuilder())->withSurfaceBuilder(ConfiguredSurfaceBuilderFactory::getInstance()->get(ConfiguredSurfaceBuilderFactory::MYCELIUM));
		DefaultBiomeFeatures::withStrongholdAndMineshaft($biomeGenerationSettingsBuilder);
		//$biomeGenerationSettingsBuilder.withStructure(StructureFeatures.RUINED_PORTAL);
		DefaultBiomeFeatures::withLavaAndWaterLakes($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withMonsterRoom($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withCommonOverworldBlocks($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withOverworldOres($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withDisks($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withMushroomBiomeVegetation($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withNormalMushroomGeneration($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withSugarCaneAndPumpkins($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withLavaAndWaterSprings($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withFrozenTopLayer($biomeGenerationSettingsBuilder);
		return (new BiomeBuilder())
			->precipitation(RainType::RAIN)
			->category(BiomeCategory::MUSHROOM)
			->depth($depth)
			->scale($scale)
			->temperature(0.9)
			->downfall(1.0)
			->withGenerationSettings($biomeGenerationSettingsBuilder->build())
			->build();
	}

	public static function makeGenericSavannaBiome(float $depth, float $scale, float $temperature, bool $isHighland, bool $isShatteredSavanna) : Biome {
		$biomeGenerationSettingsBuilder = (new BiomeGenerationSettingsBuilder())->withSurfaceBuilder(ConfiguredSurfaceBuilderFactory::getInstance()->get($isShatteredSavanna ? ConfiguredSurfaceBuilderFactory::SHATTERED_SAVANNA : ConfiguredSurfaceBuilderFactory::GRASS));
		if (!$isHighland && !$isShatteredSavanna) {
			//$biomeGenerationSettingsBuilder.withStructure(StructureFeatures.VILLAGE_SAVANNA).withStructure(StructureFeatures.PILLAGER_OUTPOST);
		}

		DefaultBiomeFeatures::withStrongholdAndMineshaft($biomeGenerationSettingsBuilder);
		//$biomeGenerationSettingsBuilder.withStructure(isHighland ? StructureFeatures.RUINED_PORTAL_MOUNTAIN : StructureFeatures.RUINED_PORTAL);
		DefaultBiomeFeatures::withLavaAndWaterLakes($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withMonsterRoom($biomeGenerationSettingsBuilder);
		if (!$isShatteredSavanna) {
			DefaultBiomeFeatures::withTallGrass($biomeGenerationSettingsBuilder);
		}

		DefaultBiomeFeatures::withCommonOverworldBlocks($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withOverworldOres($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withDisks($biomeGenerationSettingsBuilder);
		if ($isShatteredSavanna) {
			DefaultBiomeFeatures::withShatteredSavannaTrees($biomeGenerationSettingsBuilder);
			DefaultBiomeFeatures::withDefaultFlowers($biomeGenerationSettingsBuilder);
			DefaultBiomeFeatures::withNormalGrassPatch($biomeGenerationSettingsBuilder);
		} else {
			DefaultBiomeFeatures::withSavannaTrees($biomeGenerationSettingsBuilder);
			DefaultBiomeFeatures::withWarmFlowers($biomeGenerationSettingsBuilder);
			DefaultBiomeFeatures::withSavannaGrass($biomeGenerationSettingsBuilder);
		}

		DefaultBiomeFeatures::withNormalMushroomGeneration($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withSugarCaneAndPumpkins($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withLavaAndWaterSprings($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withFrozenTopLayer($biomeGenerationSettingsBuilder);
		return (new BiomeBuilder())
			->precipitation(RainType::NONE)
			->category(BiomeCategory::SAVANNA)
			->depth($depth)
			->scale($scale)
			->temperature($temperature)
			->downfall(0.0)
			->withGenerationSettings($biomeGenerationSettingsBuilder->build())
			->build();
	}

	public static function makeSavannaPlateauBiome() : Biome{
		return self::makeGenericSavannaBiome(1.5, 0.025, 1.0, true, false);
	}

	private static function makeGenericBadlandsBiome(string $surfaceBuilder, float $depth, float $scale, bool $isHighland, bool $hasOakTrees) : Biome {
		$biomeGenerationSettingsBuilder = (new BiomeGenerationSettingsBuilder())->withSurfaceBuilder(ConfiguredSurfaceBuilderFactory::getInstance()->get($surfaceBuilder));
		DefaultBiomeFeatures::withBadlandsStructures($biomeGenerationSettingsBuilder);
		// $biomeGenerationSettingsBuilder.withStructure(isHighland ? StructureFeatures.RUINED_PORTAL_MOUNTAIN : StructureFeatures.RUINED_PORTAL);
		DefaultBiomeFeatures::withLavaAndWaterLakes($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withMonsterRoom($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withCommonOverworldBlocks($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withOverworldOres($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withExtraGoldOre($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withDisks($biomeGenerationSettingsBuilder);
		if ($hasOakTrees) {
			DefaultBiomeFeatures::withBadlandsOakTrees($biomeGenerationSettingsBuilder);
		}

		DefaultBiomeFeatures::withBadlandsGrassAndBush($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withNormalMushroomGeneration($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withBadlandsVegetation($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withLavaAndWaterSprings($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withFrozenTopLayer($biomeGenerationSettingsBuilder);
		return (new BiomeBuilder())
			->precipitation(RainType::NONE)
			->category(BiomeCategory::MESA)
			->depth($depth)
			->scale($scale)
			->temperature(2.0)
			->downfall(0.0)
			->withGenerationSettings($biomeGenerationSettingsBuilder->build())
			->build();
	}

	public static function makeBadlandsBiome(float $depth, float $scale, bool $isHighland) : Biome{
		return self::makeGenericBadlandsBiome(ConfiguredSurfaceBuilderFactory::BADLANDS, $depth, $scale, $isHighland, false);
	}

	public static function makeWoodedBadlandsPlateauBiome(float $depth, float $scale) : Biome{
		return self::makeGenericBadlandsBiome(ConfiguredSurfaceBuilderFactory::WOODED_BADLANDS, $depth, $scale, true, true);
	}

	public static function makeErodedBadlandsBiome() : Biome{
		return self::makeGenericBadlandsBiome(ConfiguredSurfaceBuilderFactory::ERODED_BADLANDS, 0.1, 0.2, true, false);
	}

	private static function makeGenericOceanBiome(bool $isDeepVariant, BiomeGenerationSettingsBuilder $generationSettingsBuilder) : Biome {
		return (new BiomeBuilder())
			->precipitation(RainType::RAIN)
			->category(BiomeCategory::OCEAN)
			->depth($isDeepVariant ? -1.8 : -1.0)
			->scale(0.1)
			->temperature(0.5)
			->downfall(0.5)
			->withGenerationSettings($generationSettingsBuilder->build())
			->build();
	}

	private static function getOceanGenerationSettingsBuilder(string $surfaceBuilder, bool $hasOceanMonument, bool $isWarmOcean, bool $isDeepVariant) : BiomeGenerationSettingsBuilder {
		$biomeGenerationSettingsBuilder = (new BiomeGenerationSettingsBuilder())->withSurfaceBuilder(ConfiguredSurfaceBuilderFactory::getInstance()->get($surfaceBuilder));
		//StructureFeature tructurefeature = $isWarmOcean ? StructureFeatures.OCEAN_RUIN_WARM : StructureFeatures.OCEAN_RUIN_COLD;
		if ($isDeepVariant) {
			if ($hasOceanMonument) {
				//$biomeGenerationSettingsBuilder.withStructure(StructureFeatures.MONUMENT);
			}

			DefaultBiomeFeatures::withOceanStructures($biomeGenerationSettingsBuilder);
			//$biomeGenerationSettingsBuilder.withStructure(structurefeature);
		} else {
			//$biomeGenerationSettingsBuilder.withStructure(structurefeature);
			if ($hasOceanMonument) {
				//$biomeGenerationSettingsBuilder.withStructure(StructureFeatures.MONUMENT);
			}

			DefaultBiomeFeatures::withOceanStructures($biomeGenerationSettingsBuilder);
		}

		//$biomeGenerationSettingsBuilder.withStructure(StructureFeatures.RUINED_PORTAL_OCEAN);
		DefaultBiomeFeatures::withLavaAndWaterLakes($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withMonsterRoom($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withCommonOverworldBlocks($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withOverworldOres($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withDisks($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withTreesInWater($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withDefaultFlowers($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withBadlandsGrass($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withNormalMushroomGeneration($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withSugarCaneAndPumpkins($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withLavaAndWaterSprings($biomeGenerationSettingsBuilder);
		return $biomeGenerationSettingsBuilder;
	}

	public static function makeOceanBiome(bool $isDeepVariant) : Biome {
		$biomeGenerationSettingsBuilder = self::getOceanGenerationSettingsBuilder(ConfiguredSurfaceBuilderFactory::GRASS, $isDeepVariant, true, false);
		//$biomeGenerationSettingsBuilder.withFeature(GenerationStage.Decoration.VEGETAL_DECORATION, isDeepVariant ? Features.SEAGRASS_DEEP : Features.SEAGRASS_NORMAL);
		DefaultBiomeFeatures::withSimpleSeagrass($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withColdKelp($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withFrozenTopLayer($biomeGenerationSettingsBuilder);
		return self::makeGenericOceanBiome($isDeepVariant, $biomeGenerationSettingsBuilder);
	}

	public static function makeFrozenOceanBiome() : Biome {
		$biomeGenerationSettingsBuilder = (new BiomeGenerationSettingsBuilder())->withSurfaceBuilder(ConfiguredSurfaceBuilderFactory::getInstance()->get(ConfiguredSurfaceBuilderFactory::FROZEN_OCEAN));
		//$biomeGenerationSettingsBuilder.withStructure(StructureFeatures.OCEAN_RUIN_COLD);

		DefaultBiomeFeatures::withOceanStructures($biomeGenerationSettingsBuilder);
		//$biomeGenerationSettingsBuilder.withStructure(StructureFeatures.RUINED_PORTAL_OCEAN);
		DefaultBiomeFeatures::withLavaAndWaterLakes($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withIcebergs($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withMonsterRoom($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withBlueIce($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withCommonOverworldBlocks($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withOverworldOres($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withDisks($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withTreesInWater($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withDefaultFlowers($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withBadlandsGrass($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withNormalMushroomGeneration($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withSugarCaneAndPumpkins($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withLavaAndWaterSprings($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withFrozenTopLayer($biomeGenerationSettingsBuilder);
		return (new BiomeBuilder())
			->precipitation(RainType::SNOW)
			->category(BiomeCategory::OCEAN)
			->depth(-1.0)
			->scale(0.1)
			->temperature(0.0)
			->downfall(0.5)
			->withGenerationSettings($biomeGenerationSettingsBuilder->build())
			->build();
	}

	private static function makeGenericForestBiome(float $depth, float $scale, bool $isFlowerForestVariant) : Biome {
		$biomeGenerationSettingsBuilder = (new BiomeGenerationSettingsBuilder())->withSurfaceBuilder(ConfiguredSurfaceBuilderFactory::getInstance()->get(ConfiguredSurfaceBuilderFactory::GRASS));
		DefaultBiomeFeatures::withStrongholdAndMineshaft($biomeGenerationSettingsBuilder);
		//$biomeGenerationSettingsBuilder.withStructure(StructureFeatures.RUINED_PORTAL);
		DefaultBiomeFeatures::withLavaAndWaterLakes($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withMonsterRoom($biomeGenerationSettingsBuilder);
		if ($isFlowerForestVariant) {
			$biomeGenerationSettingsBuilder->withFeature(GenerationStageDecoration::VEGETAL_DECORATION, DefaultBiomeFeatures::getFeatureFromName(VegetationPlacements::FOREST_FLOWER_VEGETATION_COMMON));
		} else {
			DefaultBiomeFeatures::withAllForestFlowerGeneration($biomeGenerationSettingsBuilder);
		}

		DefaultBiomeFeatures::withCommonOverworldBlocks($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withOverworldOres($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withDisks($biomeGenerationSettingsBuilder);
		if ($isFlowerForestVariant) {
			$biomeGenerationSettingsBuilder->withFeature(GenerationStageDecoration::VEGETAL_DECORATION, DefaultBiomeFeatures::getFeatureFromName(VegetationPlacements::FOREST_FLOWER_TREES));
			$biomeGenerationSettingsBuilder->withFeature(GenerationStageDecoration::VEGETAL_DECORATION, DefaultBiomeFeatures::getFeatureFromName(VegetationPlacements::FLOWER_FOREST));
			DefaultBiomeFeatures::withBadlandsGrass($biomeGenerationSettingsBuilder);
		} else {
			DefaultBiomeFeatures::withForestBirchTrees($biomeGenerationSettingsBuilder);
			DefaultBiomeFeatures::withDefaultFlowers($biomeGenerationSettingsBuilder);
			DefaultBiomeFeatures::withForestGrass($biomeGenerationSettingsBuilder);
		}

		DefaultBiomeFeatures::withNormalMushroomGeneration($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withSugarCaneAndPumpkins($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withLavaAndWaterSprings($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withFrozenTopLayer($biomeGenerationSettingsBuilder);
		return (new BiomeBuilder())
			->precipitation(RainType::RAIN)
			->category(BiomeCategory::FOREST)
			->depth($depth)
			->scale($scale)
			->temperature(0.7)
			->downfall(0.8)
			->withGenerationSettings($biomeGenerationSettingsBuilder->build())
			->build();
	}

	public static function makeForestBiome(float $depth, float $scale) : Biome {
		return self::makeGenericForestBiome($depth, $scale, false);
	}

	public static function makeFlowerForestBiome() : Biome {
		return self::makeGenericForestBiome(0.1, 0.4, true);
	}

	public static function makeTaigaBiome(float $depth, float $scale, bool $isSnowyVariant, bool $hasVillageAndOutpost, bool $hasIgloos) : Biome {
		$f = $isSnowyVariant ? -0.5 : 0.25;
		$biomeGenerationSettingsBuilder = (new BiomeGenerationSettingsBuilder())->withSurfaceBuilder(ConfiguredSurfaceBuilderFactory::getInstance()->get(ConfiguredSurfaceBuilderFactory::GRASS));
		if ($hasVillageAndOutpost) {
			//$biomeGenerationSettingsBuilder.withStructure(StructureFeatures.VILLAGE_TAIGA);
			//$biomeGenerationSettingsBuilder.withStructure(StructureFeatures.PILLAGER_OUTPOST);
		}

		if ($hasIgloos) {
			//$biomeGenerationSettingsBuilder.withStructure(StructureFeatures.IGLOO);
		}

		DefaultBiomeFeatures::withStrongholdAndMineshaft($biomeGenerationSettingsBuilder);
		//$biomeGenerationSettingsBuilder.withStructure(isMountainVariant ? StructureFeatures.RUINED_PORTAL_MOUNTAIN : StructureFeatures.RUINED_PORTAL);
		DefaultBiomeFeatures::withLavaAndWaterLakes($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withMonsterRoom($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withLargeFern($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withCommonOverworldBlocks($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withOverworldOres($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withDisks($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withTaigaVegetation($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withDefaultFlowers($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withTaigaGrassVegetation($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withNormalMushroomGeneration($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withSugarCaneAndPumpkins($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withLavaAndWaterSprings($biomeGenerationSettingsBuilder);

		DefaultBiomeFeatures::withFrozenTopLayer($biomeGenerationSettingsBuilder);
		return (new BiomeBuilder())
			->precipitation(RainType::RAIN)
			->category(BiomeCategory::FOREST)
			->depth($depth)
			->scale($scale)
			->temperature($f)
			->downfall($isSnowyVariant ? 0.4 : 0.8)
			->withGenerationSettings($biomeGenerationSettingsBuilder->build())
			->build();
	}

	public static function makeDarkForestBiome(float $depth, float $scale, bool $isHillsVariant) : Biome {
		$biomeGenerationSettingsBuilder = (new BiomeGenerationSettingsBuilder())->withSurfaceBuilder(ConfiguredSurfaceBuilderFactory::getInstance()->get(ConfiguredSurfaceBuilderFactory::GRASS));
		//$biomeGenerationSettingsBuilder.withStructure(StructureFeatures.MANSION);
		DefaultBiomeFeatures::withStrongholdAndMineshaft($biomeGenerationSettingsBuilder);
		//$biomeGenerationSettingsBuilder.withStructure(StructureFeatures.RUINED_PORTAL);
		DefaultBiomeFeatures::withLavaAndWaterLakes($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withMonsterRoom($biomeGenerationSettingsBuilder);
		$biomeGenerationSettingsBuilder->withFeature(GenerationStageDecoration::VEGETAL_DECORATION, DefaultBiomeFeatures::getFeatureFromName($isHillsVariant ? VegetationPlacements::DARK_FOREST_VEGETATION_RED : VegetationPlacements::DARK_FOREST_VEGETATION_BROWN));
		DefaultBiomeFeatures::withAllForestFlowerGeneration($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withCommonOverworldBlocks($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withOverworldOres($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withDisks($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withDefaultFlowers($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withForestGrass($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withNormalMushroomGeneration($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withSugarCaneAndPumpkins($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withLavaAndWaterSprings($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withFrozenTopLayer($biomeGenerationSettingsBuilder);
		return (new BiomeBuilder())
			->precipitation(RainType::RAIN)
			->category(BiomeCategory::FOREST)
			->depth($depth)
			->scale($scale)
			->temperature(0.7)
			->downfall(0.8)
			->withGenerationSettings($biomeGenerationSettingsBuilder->build())
			->build();
	}

	public static function makeGenericSwampBiome(float $depth, float $scale, bool $isHillsVariant) : Biome {
		$biomeGenerationSettingsBuilder = (new BiomeGenerationSettingsBuilder())->withSurfaceBuilder(ConfiguredSurfaceBuilderFactory::getInstance()->get(ConfiguredSurfaceBuilderFactory::SWAMP));

		if (!$isHillsVariant) {
			//$biomeGenerationSettingsBuilder.withStructure(StructureFeatures.SWAMP_HUT);
		}

		//$biomeGenerationSettingsBuilder.withStructure(StructureFeatures.MINESHAFT);
		//$biomeGenerationSettingsBuilder.withStructure(StructureFeatures.RUINED_PORTAL_SWAMP);
		if (!$isHillsVariant) {
			DefaultBiomeFeatures::withFossils($biomeGenerationSettingsBuilder);
		}

		DefaultBiomeFeatures::withLavaAndWaterLakes($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withMonsterRoom($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withCommonOverworldBlocks($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withOverworldOres($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withClayDisks($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withSwampVegetation($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withNormalMushroomGeneration($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withSwampSugarcaneAndPumpkin($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withLavaAndWaterSprings($biomeGenerationSettingsBuilder);
		if ($isHillsVariant) {
			DefaultBiomeFeatures::withFossils($biomeGenerationSettingsBuilder);
		} else {
			//$biomeGenerationSettingsBuilder->withFeature(GenerationStageDecoration::VEGETAL_DECORATION, DefaultBiomeFeatures::getPlacementFromName(VegetationPlacements::SEAGRASS_SWAMP));
		}

		DefaultBiomeFeatures::withFrozenTopLayer($biomeGenerationSettingsBuilder);
		return (new BiomeBuilder())
			->precipitation(RainType::RAIN)
			->category(BiomeCategory::SWAMP)
			->depth($depth)
			->scale($scale)
			->temperature(0.8)
			->downfall(0.9)
			->withGenerationSettings($biomeGenerationSettingsBuilder->build())
			->build();
	}

	public static function makeSnowyBiome(float $depth, float $scale, bool $isIceSpikesBiome, bool $isMountainVariant) : Biome {
		$biomeGenerationSettingsBuilder = (new BiomeGenerationSettingsBuilder())->withSurfaceBuilder(ConfiguredSurfaceBuilderFactory::getInstance()->get($isIceSpikesBiome ? ConfiguredSurfaceBuilderFactory::ICE_SPIKES : ConfiguredSurfaceBuilderFactory::GRASS));

		if (!$isIceSpikesBiome && !$isMountainVariant) {
			//$biomeGenerationSettingsBuilder.withStructure(StructureFeatures.VILLAGE_SNOWY).withStructure(StructureFeatures.IGLOO);
		}

		DefaultBiomeFeatures::withStrongholdAndMineshaft($biomeGenerationSettingsBuilder);
		if (!$isIceSpikesBiome && !$isMountainVariant) {
			//$biomeGenerationSettingsBuilder.withStructure(StructureFeatures.PILLAGER_OUTPOST);
		}

		//$biomeGenerationSettingsBuilder.withStructure(isMountainVariant ? StructureFeatures.RUINED_PORTAL_MOUNTAIN : StructureFeatures.RUINED_PORTAL);
		DefaultBiomeFeatures::withLavaAndWaterLakes($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withMonsterRoom($biomeGenerationSettingsBuilder);
		if ($isIceSpikesBiome) {
			$biomeGenerationSettingsBuilder->withFeature(GenerationStageDecoration::SURFACE_STRUCTURES, DefaultBiomeFeatures::getFeatureFromName(MiscOverworldPlacements::ICE_SPIKE));
			$biomeGenerationSettingsBuilder->withFeature(GenerationStageDecoration::SURFACE_STRUCTURES, DefaultBiomeFeatures::getFeatureFromName(MiscOverworldPlacements::ICE_PATCH));
		}

		DefaultBiomeFeatures::withCommonOverworldBlocks($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withOverworldOres($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withDisks($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withSnowySpruces($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withDefaultFlowers($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withBadlandsGrass($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withNormalMushroomGeneration($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withSugarCaneAndPumpkins($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withLavaAndWaterSprings($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withFrozenTopLayer($biomeGenerationSettingsBuilder);
		return (new BiomeBuilder())
			->precipitation(RainType::SNOW)
			->category(BiomeCategory::ICY)
			->depth($depth)
			->scale($scale)
			->temperature(0.0)
			->downfall(0.5)
			->withGenerationSettings($biomeGenerationSettingsBuilder->build())
			->build();
	}

	public static function makeRiverBiome(float $depth, float $scale, float $temperature, bool $isSnowy) : Biome {
		$biomeGenerationSettingsBuilder = (new BiomeGenerationSettingsBuilder())->withSurfaceBuilder(ConfiguredSurfaceBuilderFactory::getInstance()->get(ConfiguredSurfaceBuilderFactory::GRASS));
		// $biomeGenerationSettingsBuilder.withStructure(StructureFeatures.MINESHAFT);
		//$biomeGenerationSettingsBuilder.withStructure(StructureFeatures.RUINED_PORTAL);
		DefaultBiomeFeatures::withLavaAndWaterLakes($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withMonsterRoom($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withCommonOverworldBlocks($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withOverworldOres($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withDisks($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withTreesInWater($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withDefaultFlowers($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withBadlandsGrass($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withNormalMushroomGeneration($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withSugarCaneAndPumpkins($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withLavaAndWaterSprings($biomeGenerationSettingsBuilder);
		if (!$isSnowy) {
			//$biomeGenerationSettingsBuilder->withFeature(GenerationStage.Decoration.VEGETAL_DECORATION, Features.SEAGRASS_RIVER);
		}

		DefaultBiomeFeatures::withFrozenTopLayer($biomeGenerationSettingsBuilder);
		return (new BiomeBuilder())
			->precipitation($isSnowy ? RainType::SNOW : RainType::RAIN)
			->category(BiomeCategory::RIVER)
			->depth($depth)
			->scale($scale)
			->temperature($temperature)
			->downfall(0.5)
			->withGenerationSettings($biomeGenerationSettingsBuilder->build())
			->build();
	}

	public static function makeGenericBeachBiome(float $depth, float $scale, float $temperature, float $downfall, bool $isColdBiome, bool $isStoneVariant) : Biome {
		$biomeGenerationSettingsBuilder = (new BiomeGenerationSettingsBuilder())->withSurfaceBuilder(ConfiguredSurfaceBuilderFactory::getInstance()->get($isStoneVariant ? ConfiguredSurfaceBuilderFactory::STONE : ConfiguredSurfaceBuilderFactory::DESERT));
		if ($isStoneVariant) {
			DefaultBiomeFeatures::withStrongholdAndMineshaft($biomeGenerationSettingsBuilder);
		} else {
			//$biomeGenerationSettingsBuilder->withStructure(StructureFeatures.MINESHAFT);
			//$biomeGenerationSettingsBuilder->withStructure(StructureFeatures.BURIED_TREASURE);
			//$biomeGenerationSettingsBuilder->withStructure(StructureFeatures.SHIPWRECK_BEACHED);
		}

		//$biomeGenerationSettingsBuilder->withStructure($isStoneVariant ? StructureFeatures.RUINED_PORTAL_MOUNTAIN : StructureFeatures.RUINED_PORTAL);
		DefaultBiomeFeatures::withLavaAndWaterLakes($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withMonsterRoom($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withCommonOverworldBlocks($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withOverworldOres($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withDisks($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withDefaultFlowers($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withBadlandsGrass($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withNormalMushroomGeneration($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withSugarCaneAndPumpkins($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withLavaAndWaterSprings($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withFrozenTopLayer($biomeGenerationSettingsBuilder);

		return (new BiomeBuilder())
			->precipitation($isColdBiome ? RainType::SNOW : RainType::RAIN)
			->category($isStoneVariant ? BiomeCategory::NONE : BiomeCategory::BEACH)
			->depth($depth)
			->scale($scale)
			->temperature($temperature)
			->downfall($downfall)
			->withGenerationSettings($biomeGenerationSettingsBuilder->build())
			->build();
	}

	public static function makeHellBiome() : Biome {
		$biomeGenerationSettingsBuilder = (new BiomeGenerationSettingsBuilder())->withSurfaceBuilder(ConfiguredSurfaceBuilderFactory::getInstance()->get(ConfiguredSurfaceBuilderFactory::NETHER));
		DefaultBiomeFeatures::withNetherDefaultVegetation($biomeGenerationSettingsBuilder);
		DefaultBiomeFeatures::withCommonNetherBlocks($biomeGenerationSettingsBuilder);

		return (new BiomeBuilder())
			->precipitation(RainType::NONE)
			->category(BiomeCategory::NETHER)
			->depth(0.1)
			->scale(0.2)
			->temperature(2.0)
			->downfall(0.0)
			->withGenerationSettings($biomeGenerationSettingsBuilder->build())
			->build();
	}
}
