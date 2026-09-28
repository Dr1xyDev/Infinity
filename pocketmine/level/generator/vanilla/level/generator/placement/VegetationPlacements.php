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

namespace pocketmine\level\generator\vanilla\level\generator\placement;

use pocketmine\level\generator\vanilla\level\generator\feature\blocksupport\PlantSupport;
use pocketmine\level\generator\vanilla\level\generator\feature\FeatureFactory;
use pocketmine\level\generator\vanilla\level\generator\feature\FeatureSpread;
use pocketmine\level\generator\vanilla\level\generator\feature\TreeFeatures;
use pocketmine\level\generator\vanilla\level\generator\feature\VegetationFeatures;
use pocketmine\level\generator\vanilla\level\generator\verticalanchor\VerticalAnchor;
use pocketmine\level\generator\vanilla\utils\valueproviders\ClampedInt;
use pocketmine\level\generator\vanilla\utils\valueproviders\ConstantInt;
use pocketmine\level\generator\vanilla\utils\valueproviders\UniformInt;

final class VegetationPlacements{

	public const VINES = "vines";

	public const PATCH_FIRE = "patch_fire";
	public const PATCH_SOUL_FIRE = "patch_soul_fire";
	public const PATCH_CRIMSON_ROOTS = "patch_crimson_roots";
	public const PATCH_SUNFLOWER = "patch_sunflower";
	public const PATCH_PUMPKIN = "patch_pumpkin";
	public const PATCH_GRASS_PLAIN = "patch_grass_plain";
	public const PATCH_GRASS_FOREST = "patch_grass_forest";
	public const PATCH_GRASS_BADLANDS = "patch_grass_badlands";
	public const PATCH_GRASS_SAVANNA = "patch_grass_savanna";
	public const PATCH_GRASS_NORMAL = "patch_grass_normal";
	public const PATCH_GRASS_TAIGA_2 = "patch_grass_taiga_2";
	public const PATCH_GRASS_TAIGA = "patch_grass_taiga";
	public const PATCH_GRASS_JUNGLE = "patch_grass_jungle";
	public const PATCH_DEAD_BUSH_2 = "patch_dead_bush_2";
	public const PATCH_DEAD_BUSH = "patch_dead_bush";
	public const PATCH_DEAD_BUSH_BADLANDS = "patch_dead_bush_badlands";
	public const PATCH_MELON = "patch_melon";
	public const PATCH_BERRY_SPARSE = "patch_berry_sparse";
	public const PATCH_BERRY_DECORATED = "patch_berry_decorated";
	public const PATCH_WATERLILY = "PATCH_WATERLILY";
	public const PATCH_TALL_GRASS_2 = "patch_tall_grass_2";
	public const PATCH_TALL_GRASS = "patch_tall_grass";
	public const PATCH_LARGE_FERN = "patch_large_fern";
	public const PATCH_CACTUS_DESERT = "patch_cactus_desert";
	public const PATCH_CACTUS_DECORATED = "patch_cactus_decorated";
	public const PATCH_SUGAR_CANE_SWAMP = "patch_sugar_cane_swamp";
	public const PATCH_SUGAR_CANE_DESERT = "patch_sugar_cane_desert";
	public const PATCH_SUGAR_CANE_BADLANDS = "patch_sugar_cane_badlands";
	public const PATCH_SUGAR_CANE = "patch_sugar_cane";

	public const BROWN_MUSHROOM_NETHER = "brown_mushroom_nether";
	public const RED_MUSHROOM_NETHER = "red_mushroom_nether";
	public const BROWN_MUSHROOM_NORMAL = "brown_mushroom_normal";
	public const RED_MUSHROOM_NORMAL = "red_mushroom_normal";
	public const BROWN_MUSHROOM_TAIGA = "brown_mushroom_taiga";
	public const RED_MUSHROOM_TAIGA = "red_mushroom_taiga";
	public const BROWN_MUSHROOM_GIANT = "brown_mushroom_giant";
	public const RED_MUSHROOM_GIANT = "red_mushroom_giant";
	public const BROWN_MUSHROOM_SWAMP = "brown_mushroom_swamp";
	public const RED_MUSHROOM_SWAMP = "red_mushroom_swamp";

	public const FLOWER_WARM = "flower_warm";
	public const FLOWER_DEFAULT = "flower_default";
	public const FLOWER_FOREST = "flower_forest";
	public const FLOWER_SWAMP = "flower_swamp";
	public const FLOWER_PLAIN_DECORATED = "flower_plain_decorated";

	public const FOREST_FLOWER_VEGETATION_COMMON = "forest_flower_vegetation_common";
	public const FOREST_FLOWER_VEGETATION = "forest_flower_vegetation";
	public const DARK_FOREST_VEGETATION_BROWN = "dark_forest_vegetation_brown";
	public const DARK_FOREST_VEGETATION_RED = "dark_forest_vegetation_red";
	public const OAK_BADLANDS = "oak_badlands";
	public const SPRUCE_SNOWY = "spruce_snowy";
	public const SWAMP_TREE = "swamp_tree";
	public const FOREST_FLOWER_TREES = "forest_flower_trees";
	public const TAIGA_VEGETATION = "taiga_vegetation";
	public const TREES_SHATTERED_SAVANNA = "trees_shattered_savanna";
	public const TREES_SAVANNA = "trees_savanna";
	public const BIRCH_TALL = "birch_tall";
	public const TREES_BIRCH = "trees_birch";
	public const TREES_MOUNTAIN_EDGE = "trees_mountain_edge";
	public const TREES_MOUNTAIN = "trees_mountain";
	public const TREES_WATER = "trees_water";
	public const BIRCH_OTHER = "birch_other";
	public const PLAIN_VEGETATION = "plain_vegetation";
	public const TREES_JUNGLE_EDGE = "trees_jungle_edge";
	public const TREES_GIANT_SPRUCE = "trees_giant_spruce";
	public const TREES_GIANT = "trees_giant";
	public const TREES_JUNGLE = "trees_jungle";
	public const BAMBOO_VEGETATION = "bamboo_vegetation";
	public const MUSHROOM_FIELD_VEGETATION = "mushroom_field_vegetation";

	private function __construct(){
		//NOOP
	}

	public static function bootstrap(PlacementFactory $placementFactory) : void {
		$features = FeatureFactory::getInstance();

		$placementFactory->register(self::PATCH_FIRE, $features->get(VegetationFeatures::PATCH_FIRE), [
			new FirePlacement(new FeatureSpread(10))
		]);
		$placementFactory->register(self::PATCH_SOUL_FIRE, $features->get(VegetationFeatures::PATCH_SOUL_FIRE), [
			new FirePlacement(new FeatureSpread(10))
		]);
		$placementFactory->register(self::PATCH_CRIMSON_ROOTS, $features->get(VegetationFeatures::PATCH_CRIMSON_ROOTS), [
			HeightRangePlacement::uniform(VerticalAnchor::bottom(), VerticalAnchor::absolute(128))
		]);
		$placementFactory->register(self::PATCH_SUNFLOWER, $features->get(VegetationFeatures::PATCH_SUNFLOWER), self::defaultSpawnPlantWithRarity(3));
		$placementFactory->register(self::PATCH_PUMPKIN, $features->get(VegetationFeatures::PATCH_PUMPKIN), self::defaultSpawnPlantWithRarity(300));
		$placementFactory->register(self::PATCH_GRASS_PLAIN, $features->get(VegetationFeatures::PATCH_GRASS), [
			new NoiseThresholdCountPlacement(-0.8, 5, 10),
			new InSquarePlacement(),
			new HeightmapPlacement(HeightmantType::SOLID)
		]);
		$placementFactory->register(self::PATCH_GRASS_FOREST, $features->get(VegetationFeatures::PATCH_GRASS), self::worldSurfaceSquaredWithCount(2));
		$placementFactory->register(self::PATCH_GRASS_BADLANDS, $features->get(VegetationFeatures::PATCH_GRASS), [
			new InSquarePlacement(),
			new HeightmapPlacement(HeightmantType::SOLID)
		]);
		$placementFactory->register(self::PATCH_GRASS_SAVANNA, $features->get(VegetationFeatures::PATCH_GRASS), self::worldSurfaceSquaredWithCount(20));
		$placementFactory->register(self::PATCH_GRASS_NORMAL, $features->get(VegetationFeatures::PATCH_GRASS), self::worldSurfaceSquaredWithCount(5));
		$placementFactory->register(self::PATCH_GRASS_TAIGA_2, $features->get(VegetationFeatures::PATCH_GRASS), [
			new InSquarePlacement(),
			new HeightmapPlacement(HeightmantType::SOLID)
		]);
		$placementFactory->register(self::PATCH_GRASS_TAIGA, $features->get(VegetationFeatures::PATCH_GRASS), self::worldSurfaceSquaredWithCount(7));
		$placementFactory->register(self::PATCH_GRASS_JUNGLE, $features->get(VegetationFeatures::PATCH_GRASS), self::worldSurfaceSquaredWithCount(25));
		$placementFactory->register(self::PATCH_DEAD_BUSH_2, $features->get(VegetationFeatures::PATCH_DEAD_BUSH), self::worldSurfaceSquaredWithCount(2));
		$placementFactory->register(self::PATCH_DEAD_BUSH, $features->get(VegetationFeatures::PATCH_DEAD_BUSH), [
			new InSquarePlacement(),
			new HeightmapPlacement(HeightmantType::SOLID)
		]);
		$placementFactory->register(self::PATCH_DEAD_BUSH_BADLANDS, $features->get(VegetationFeatures::PATCH_DEAD_BUSH), self::worldSurfaceSquaredWithCount(20));
		$placementFactory->register(self::PATCH_MELON, $features->get(VegetationFeatures::PATCH_MELON), self::defaultSpawnPlantWithRarity(6));
		$placementFactory->register(self::PATCH_BERRY_SPARSE, $features->get(VegetationFeatures::PATCH_BERRY_BUSH), [
			new InSquarePlacement(),
			new HeightmapPlacement(HeightmantType::SOLID)
		]);
		$placementFactory->register(self::PATCH_BERRY_DECORATED, $features->get(VegetationFeatures::PATCH_BERRY_BUSH), self::defaultSpawnPlantWithRarity(12));
		$placementFactory->register(self::PATCH_WATERLILY, $features->get(VegetationFeatures::PATCH_WATERLILY), self::worldSurfaceSquaredWithCount(4));
		$placementFactory->register(self::PATCH_TALL_GRASS_2, $features->get(VegetationFeatures::PATCH_TALL_GRASS), [
			new NoiseThresholdCountPlacement(-0.8, 0, 7),
			new RarityFilter(32),
			new InSquarePlacement(),
			new HeightmapPlacement(HeightmantType::SOLID)
		]);
		$placementFactory->register(self::PATCH_TALL_GRASS, $features->get(VegetationFeatures::PATCH_TALL_GRASS), self::defaultSpawnPlantWithRarity(5));
		$placementFactory->register(self::PATCH_LARGE_FERN, $features->get(VegetationFeatures::PATCH_LARGE_FERN), self::defaultSpawnPlantWithRarity(5));
		$placementFactory->register(self::PATCH_CACTUS_DESERT, $features->get(VegetationFeatures::PATCH_CACTUS), self::defaultSpawnPlantWithRarity(6));
		$placementFactory->register(self::PATCH_CACTUS_DECORATED, $features->get(VegetationFeatures::PATCH_CACTUS), self::defaultSpawnPlantWithRarity(13));
		$placementFactory->register(self::PATCH_SUGAR_CANE_SWAMP, $features->get(VegetationFeatures::PATCH_SUGAR_CANE), self::defaultSpawnPlantWithRarity(3));
		$placementFactory->register(self::PATCH_SUGAR_CANE_DESERT, $features->get(VegetationFeatures::PATCH_SUGAR_CANE), [
			new InSquarePlacement(),
			new HeightmapPlacement(HeightmantType::SOLID)
		]);
		$placementFactory->register(self::PATCH_SUGAR_CANE_BADLANDS, $features->get(VegetationFeatures::PATCH_SUGAR_CANE), self::defaultSpawnPlantWithRarity(5));
		$placementFactory->register(self::PATCH_SUGAR_CANE, $features->get(VegetationFeatures::PATCH_SUGAR_CANE), self::defaultSpawnPlantWithRarity(6));

		$patchBrownMushroom = $features->get(VegetationFeatures::PATCH_BROWN_MUSHROOM);
		$patchRedMushroom = $features->get(VegetationFeatures::PATCH_RED_MUSHROOM);

		$netherModify = [
			new RarityFilter(2),
			new InSquarePlacement(),
			HeightRangePlacement::uniform(VerticalAnchor::bottom(), VerticalAnchor::top())
		];

		$placementFactory->register(self::BROWN_MUSHROOM_NETHER, $patchBrownMushroom, $netherModify);
		$placementFactory->register(self::RED_MUSHROOM_NETHER, $patchRedMushroom, $netherModify);
		$placementFactory->register(self::BROWN_MUSHROOM_NORMAL, $patchBrownMushroom, self::getMushroomPlacement(256, null));
		$placementFactory->register(self::RED_MUSHROOM_NORMAL, $patchRedMushroom, self::getMushroomPlacement(512, null));
		$placementFactory->register(self::BROWN_MUSHROOM_TAIGA, $patchBrownMushroom, self::getMushroomPlacement(4, null));
		$placementFactory->register(self::RED_MUSHROOM_TAIGA, $patchRedMushroom, self::getMushroomPlacement(256, null));
		$placementFactory->register(self::BROWN_MUSHROOM_GIANT, $patchBrownMushroom, self::getMushroomPlacement(4, new CountPlacement(ConstantInt::of(3))));
		$placementFactory->register(self::RED_MUSHROOM_GIANT, $patchRedMushroom, self::getMushroomPlacement(171, null));
		$placementFactory->register(self::BROWN_MUSHROOM_SWAMP, $patchRedMushroom, self::getMushroomPlacement(171, null));
		$placementFactory->register(self::RED_MUSHROOM_SWAMP, $patchRedMushroom, self::getMushroomPlacement(171, null));

		$placementFactory->register(self::FLOWER_WARM, $features->get(VegetationFeatures::FLOWER_DEFAULT), self::defaultSpawnPlantWithRarity(16));
		$placementFactory->register(self::FLOWER_DEFAULT, $features->get(VegetationFeatures::FLOWER_DEFAULT), self::defaultSpawnPlantWithRarity(32));
		$placementFactory->register(self::FLOWER_FOREST, $features->get(VegetationFeatures::FLOWER_FOREST), [
			new CountPlacement(ConstantInt::of(3)),
			new RarityFilter(2),
			new InSquarePlacement(),
			HeightRangePlacement::uniform(VerticalAnchor::bottom(), VerticalAnchor::top())
		]);
		$placementFactory->register(self::FLOWER_SWAMP, $features->get(VegetationFeatures::FLOWER_SWAMP), self::defaultSpawnPlantWithRarity(32));
		$placementFactory->register(self::FLOWER_PLAIN_DECORATED, $features->get(VegetationFeatures::FLOWER_PLAIN), [
			new NoiseThresholdCountPlacement(-0.8, 15, 4),
			new RarityFilter(32),
			new InSquarePlacement(),
			HeightRangePlacement::uniform(VerticalAnchor::bottom(), VerticalAnchor::top())
		]);

		$darkForestVegetationConfig = [
			new CountPlacement(ConstantInt::of(16)),
			new InSquarePlacement(),
			new SurfaceWaterDepthFilter(0),
			new HeightmapPlacement(HeightmantType::OCEAN_SOLID),
			new BlockSupportPlacement(new PlantSupport())
		];

		$placementFactory->register(self::FOREST_FLOWER_VEGETATION_COMMON, $features->get(VegetationFeatures::FOREST_FLOWER_VEGETATION), [
			new RarityFilter(7),
			new InSquarePlacement(),
			new HeightmapPlacement(HeightmantType::SOLID),
			new CountPlacement(ClampedInt::of(UniformInt::of(-1, 3), 0, 3)),
			new BlockSupportPlacement(new PlantSupport())
		]);
		$placementFactory->register(self::FOREST_FLOWER_VEGETATION, $features->get(VegetationFeatures::FOREST_FLOWER_VEGETATION), [
			new RarityFilter(7),
			new InSquarePlacement(),
			new HeightmapPlacement(HeightmantType::SOLID),
			new CountPlacement(ClampedInt::of(UniformInt::of(-3, 1), 0, 1)),
			new BlockSupportPlacement(new PlantSupport())
		]);
		$placementFactory->register(self::DARK_FOREST_VEGETATION_BROWN, $features->get(VegetationFeatures::DARK_FOREST_VEGETATION_BROWN), $darkForestVegetationConfig);
		$placementFactory->register(self::DARK_FOREST_VEGETATION_RED, $features->get(VegetationFeatures::DARK_FOREST_VEGETATION_RED), $darkForestVegetationConfig);
		$placementFactory->register(self::OAK_BADLANDS, $features->get(TreeFeatures::OAK), self::treePlacement(CountPlacement::countExtra(5, 0.1, 1)));
		$placementFactory->register(self::SPRUCE_SNOWY, $features->get(TreeFeatures::SPRUCE), self::treePlacement(CountPlacement::countExtra(0, 0.1, 1)));
		$placementFactory->register(self::SWAMP_TREE, $features->get(TreeFeatures::SWAMP_OAK), [
			CountPlacement::countExtra(2, 0.1, 1),
			new InSquarePlacement(),
			new SurfaceWaterDepthFilter(2),
			new HeightmapPlacement(HeightmantType::OCEAN_SOLID),
			new BlockSupportPlacement(new PlantSupport())
		]);
		$placementFactory->register(self::FOREST_FLOWER_TREES, $features->get(VegetationFeatures::FOREST_FLOWER_TREES), self::treePlacement(CountPlacement::countExtra(6, 0.1, 1)));
		$placementFactory->register(self::TAIGA_VEGETATION, $features->get(VegetationFeatures::TAIGA_VEGETATION), self::treePlacement(CountPlacement::countExtra(10, 0.1, 1)));
		$placementFactory->register(self::TREES_SHATTERED_SAVANNA, $features->get(VegetationFeatures::TREES_SAVANNA), self::treePlacement(CountPlacement::countExtra(2, 0.1, 1)));
		$placementFactory->register(self::TREES_SAVANNA, $features->get(VegetationFeatures::TREES_SAVANNA), self::treePlacement(CountPlacement::countExtra(1, 0.1, 1)));
		$placementFactory->register(self::BIRCH_TALL, $features->get(VegetationFeatures::BIRCH_TALL), self::treePlacement(CountPlacement::countExtra(10, 0.1, 1)));
		$placementFactory->register(self::TREES_BIRCH, $features->get(TreeFeatures::BIRCH_BEES_0002), self::treePlacement(CountPlacement::countExtra(10, 0.1, 1)));
		$placementFactory->register(self::TREES_MOUNTAIN_EDGE, $features->get(VegetationFeatures::TREES_MOUNTAIN), self::treePlacement(CountPlacement::countExtra(3, 0.1, 1)));
		$placementFactory->register(self::TREES_MOUNTAIN, $features->get(VegetationFeatures::TREES_MOUNTAIN), self::treePlacement(CountPlacement::countExtra(0, 0.1, 1)));
		$placementFactory->register(self::TREES_WATER, $features->get(VegetationFeatures::TREES_WATER), self::treePlacement(CountPlacement::countExtra(0, 0.1, 1)));
		$placementFactory->register(self::BIRCH_OTHER, $features->get(VegetationFeatures::BIRCH_OTHER), self::treePlacement(CountPlacement::countExtra(10, 0.1, 1)));
		$placementFactory->register(self::PLAIN_VEGETATION, $features->get(VegetationFeatures::PLAIN_VEGETATION), self::treePlacement(CountPlacement::countExtra(0, 0.05, 1)));
		$placementFactory->register(self::TREES_JUNGLE_EDGE, $features->get(VegetationFeatures::TREES_JUNGLE_EDGE), self::treePlacement(CountPlacement::countExtra(2, 0.1, 1)));
		$placementFactory->register(self::TREES_GIANT_SPRUCE, $features->get(VegetationFeatures::TREES_GIANT_SPRUCE), self::treePlacement(CountPlacement::countExtra(10, 0.1, 1)));
		$placementFactory->register(self::TREES_GIANT, $features->get(VegetationFeatures::TREES_GIANT), self::treePlacement(CountPlacement::countExtra(10, 0.1, 1)));
		$placementFactory->register(self::TREES_JUNGLE, $features->get(VegetationFeatures::TREES_JUNGLE), self::treePlacement(CountPlacement::countExtra(50, 0.1, 1)));
		$placementFactory->register(self::BAMBOO_VEGETATION, $features->get(VegetationFeatures::BAMBOO_VEGETATION), self::treePlacement(CountPlacement::countExtra(30, 0.1, 1)));
		$placementFactory->register(self::MUSHROOM_FIELD_VEGETATION, $features->get(VegetationFeatures::MUSHROOM_FIELD_VEGETATION), [
			new HeightmapPlacement(HeightmantType::SOLID),
			new InSquarePlacement()
		]);
	}

	private static function treePlacement(PlacementModifier $frequency) : array{
		return [
			$frequency,
			new InSquarePlacement(),
			new SurfaceWaterDepthFilter(0),
			new HeightmapPlacement(HeightmantType::OCEAN_SOLID),
			new BlockSupportPlacement(new PlantSupport())
		];
	}

	private static function getMushroomPlacement(int $rarity, ?PlacementModifier $prefix) : array{
		$builder = [];
		if ($prefix != null) {
			$builder[] = $prefix;
		}

		if ($rarity != 0) {
			$builder[] = new RarityFilter($rarity);
		}

		$builder[] = new InSquarePlacement();
		$builder[] = new HeightmapPlacement(HeightmantType::SOLID);
		return $builder;
	}

	private static function worldSurfaceSquaredWithCount(int $count) : array {
		return [
			new CountPlacement(ConstantInt::of($count)),
			new InSquarePlacement(),
			new HeightmapPlacement(HeightmantType::OCEAN_SOLID)
		];
	}

	private static function defaultSpawnPlantWithRarity(int $chance) : array {
		return [
			new RarityFilter($chance),
			new InSquarePlacement(),
			new HeightmapPlacement(HeightmantType::SOLID)
		];
	}
}
