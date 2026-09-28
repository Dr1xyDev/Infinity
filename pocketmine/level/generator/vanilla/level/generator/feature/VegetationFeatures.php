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

namespace pocketmine\level\generator\vanilla\level\generator\feature;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\BlockFactory;
use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\block\DoublePlant;
use pocketmine\level\generator\vanilla\block\Flower;
use pocketmine\level\generator\vanilla\block\TallGrass;
use pocketmine\level\generator\vanilla\level\generator\feature\blockplacer\ColumnBlockPlacer;
use pocketmine\level\generator\vanilla\level\generator\feature\blockplacer\DoublePlantBlockPlacer;
use pocketmine\level\generator\vanilla\level\generator\feature\blockplacer\SimpleBlockPlacer;
use pocketmine\level\generator\vanilla\level\generator\feature\blocksupport\CactusSupport;
use pocketmine\level\generator\vanilla\level\generator\feature\blocksupport\DeadBushSupport;
use pocketmine\level\generator\vanilla\level\generator\feature\blocksupport\DoublePlantSupport;
use pocketmine\level\generator\vanilla\level\generator\feature\blocksupport\PlantSupport;
use pocketmine\level\generator\vanilla\level\generator\feature\blocksupport\SugarSupport;
use pocketmine\level\generator\vanilla\level\generator\feature\blocksupport\WaterLilySupport;
use pocketmine\level\generator\vanilla\level\generator\feature\configurations\BlockClusterConfigurationBuilder;
use pocketmine\level\generator\vanilla\level\generator\feature\configurations\ConfiguredRandomFeatureList;
use pocketmine\level\generator\vanilla\level\generator\feature\configurations\MultipleRandomFeatureConfiguration;
use pocketmine\level\generator\vanilla\level\generator\feature\configurations\SingleRandomConfiguration;
use pocketmine\level\generator\vanilla\level\generator\feature\configurations\TwoFeatureChoiceConfiguration;
use pocketmine\level\generator\vanilla\level\generator\feature\stateproviders\BlockStateProvider;
use pocketmine\level\generator\vanilla\level\generator\feature\stateproviders\ForestFlowerStateProvider;
use pocketmine\level\generator\vanilla\level\generator\feature\stateproviders\PlainFlowerStateProvider;
use pocketmine\level\generator\vanilla\level\generator\feature\stateproviders\WeightedStateProvider;
use pocketmine\level\generator\vanilla\level\generator\placement\VegetationPlacements;

final class VegetationFeatures{

	public const PATCH_FIRE = "patch_fire";
	public const PATCH_SOUL_FIRE = "patch_soul_fire";
	public const PATCH_BROWN_MUSHROOM = "patch_brown_mushroom";
	public const PATCH_RED_MUSHROOM = "patch_red_mushroom";
	public const PATCH_CRIMSON_ROOTS = "patch_crimson_roots";
	public const PATCH_SUNFLOWER = "patch_sunflower";
	public const PATCH_PUMPKIN = "patch_pumpkin";
	public const PATCH_TAIGA_GRASS = "patch_taiga_grass";
	public const PATCH_BERRY_BUSH = "patch_berry_bush";
	public const PATCH_GRASS = "patch_grass";
	public const PATCH_GRASS_JUNGLE = "patch_grass_jungle";
	public const PATCH_DEAD_BUSH = "patch_dead_bush";
	public const PATCH_MELON = "patch_melon";
	public const PATCH_WATERLILY = "PATCH_WATERLILY";
	public const PATCH_TALL_GRASS = "patch_tall_grass";
	public const PATCH_LARGE_FERN = "patch_large_fern";
	public const PATCH_CACTUS = "patch_cactus";
	public const PATCH_SUGAR_CANE = "patch_sugar_cane";

	public const FLOWER_DEFAULT = "flower_default";
	public const FLOWER_FOREST = "flower_forest";
	public const FLOWER_SWAMP = "flower_swamp";
	public const FLOWER_PLAIN = "flower_plain";

	public const FOREST_FLOWER_VEGETATION = "forest_flower_vegetation";
	public const DARK_FOREST_VEGETATION_BROWN = "dark_forest_vegetation_brown";
	public const DARK_FOREST_VEGETATION_RED = "dark_forest_vegetation_red";
	public const FOREST_FLOWER_TREES = "forest_flower_trees";
	public const TAIGA_VEGETATION = "taiga_vegetation";
	public const TREES_SAVANNA = "trees_savanna";
	public const BIRCH_TALL = "birch_tall";
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

	public static function bootstrap(FeatureFactory $featureFactory) : void {
		$featureFactory->register(self::PATCH_FIRE, new RandomPatchFeature(self::createSimpleX2Patch(BlockFactory::get(BlockIds::FIRE))->tries(64)->whitelist([BlockFactory::get(BlockIds::NETHERRACK)])->build()));
		$featureFactory->register(self::PATCH_SOUL_FIRE, new RandomPatchFeature(self::createSimpleX2Patch(BlockFactory::get(BlockIds::SOUL_FIRE))->tries(64)->whitelist([BlockFactory::get(BlockIds::SOUL_SOIL)])->build()));
		$featureFactory->register(self::PATCH_BROWN_MUSHROOM, new RandomPatchFeature(self::createSimpleX2Patch(BlockFactory::get(BlockIds::BROWN_MUSHROOM))->tries(64)->build()));
		$featureFactory->register(self::PATCH_RED_MUSHROOM, new RandomPatchFeature(self::createSimpleX2Patch(BlockFactory::get(BlockIds::RED_MUSHROOM))->tries(64)->build()));
		$featureFactory->register(self::PATCH_CRIMSON_ROOTS, new RandomPatchFeature(self::createSimpleX2Patch(BlockFactory::get(BlockIds::CRIMSON_ROOTS))->tries(64)->build()));
		$featureFactory->register(self::PATCH_SUNFLOWER, new RandomPatchFeature(self::createSimpleDoublePlant(BlockFactory::get(BlockIds::DOUBLE_PLANT, DoublePlant::TYPE_SUNFLOWER))->tries(64)->build()));
		$featureFactory->register(self::PATCH_PUMPKIN, new RandomPatchFeature(self::createSimpleX2Patch(BlockFactory::get(BlockIds::PUMPKIN))->tries(64)->whitelist([BlockFactory::get(BlockIds::GRASS)])->build()));
		$featureFactory->register(self::PATCH_TAIGA_GRASS, new RandomPatchFeature(
			(new BlockClusterConfigurationBuilder(new WeightedStateProvider([BlockFactory::get(BlockIds::TALL_GRASS), BlockFactory::get(BlockIds::TALL_GRASS, TallGrass::TYPE_FERN), BlockFactory::get(BlockIds::TALL_GRASS, TallGrass::TYPE_FERN), BlockFactory::get(BlockIds::TALL_GRASS, TallGrass::TYPE_FERN), BlockFactory::get(BlockIds::TALL_GRASS, TallGrass::TYPE_FERN)]), new SimpleBlockPlacer(), new PlantSupport()))
				->tries(32)
				->build()
		));
		$featureFactory->register(self::PATCH_BERRY_BUSH, new RandomPatchFeature(
			self::createSimpleX2Patch(BlockFactory::get(BlockIds::SWEET_BERRY_BUSH))
				->tries(64)
				->whitelist([BlockFactory::get(BlockIds::GRASS)])
				->build()
		));
		$featureFactory->register(self::PATCH_GRASS, new RandomPatchFeature(
			self::createSimpleX2Patch(BlockFactory::get(BlockIds::TALL_GRASS))
				->tries(32)
				->build()
		));
		$featureFactory->register(self::PATCH_GRASS_JUNGLE, new RandomPatchFeature(
			(new BlockClusterConfigurationBuilder(new WeightedStateProvider([BlockFactory::get(BlockIds::TALL_GRASS), BlockFactory::get(BlockIds::TALL_GRASS), BlockFactory::get(BlockIds::TALL_GRASS), BlockFactory::get(BlockIds::TALL_GRASS, TallGrass::TYPE_FERN)]), new SimpleBlockPlacer(), new PlantSupport()))
				->blacklist([BlockFactory::get(BlockIds::PODZOL)])
				->tries(32)
				->build()
		));
		$featureFactory->register(self::PATCH_DEAD_BUSH, new RandomPatchFeature(
			(new BlockClusterConfigurationBuilder(BlockStateProvider::simple(BlockFactory::get(BlockIds::DEAD_BUSH)), new SimpleBlockPlacer(), new DeadBushSupport()))
				->tries(4)
				->build()
		));
		$featureFactory->register(self::PATCH_MELON, new RandomPatchFeature(
			self::createSimpleX2Patch(BlockFactory::get(BlockIds::MELON_BLOCK))
				->tries(64)
				->whitelist([BlockFactory::get(BlockIds::GRASS)])
				->replaceable()
				->build()
		));
		$featureFactory->register(self::PATCH_WATERLILY, new RandomPatchFeature(
			(new BlockClusterConfigurationBuilder(BlockStateProvider::simple(BlockFactory::get(BlockIds::LILY_PAD)), new SimpleBlockPlacer(), new WaterLilySupport()))
				->tries(10)
				->build()
		));
		$featureFactory->register(self::PATCH_TALL_GRASS, new RandomPatchFeature(
			self::createSimpleDoublePlant(BlockFactory::get(BlockIds::DOUBLE_PLANT, DoublePlant::TYPE_DOUBLE_TALLGRASS))
				->tries(64)
				->build()
		));
		$featureFactory->register(self::PATCH_LARGE_FERN, new RandomPatchFeature(
			self::createSimpleDoublePlant(BlockFactory::get(BlockIds::DOUBLE_PLANT, DoublePlant::TYPE_LARGE_FERN))
				->tries(64)
				->build()
		));
		$featureFactory->register(self::PATCH_CACTUS, new RandomPatchFeature(
			(new BlockClusterConfigurationBuilder(BlockStateProvider::simple(BlockFactory::get(BlockIds::CACTUS)), new ColumnBlockPlacer(1, 2), new CactusSupport()))
				->tries(10)
				->build()
		));
		$featureFactory->register(self::PATCH_SUGAR_CANE, new RandomPatchFeature(
			(new BlockClusterConfigurationBuilder(BlockStateProvider::simple(BlockFactory::get(BlockIds::SUGARCANE_BLOCK)), new ColumnBlockPlacer(2, 2), new SugarSupport()))
				->xSpread(4)
				->ySpread(0)
				->zSpread(4)
				->tries(20)
				->requiresWater()
				->build()
		));

		$featureFactory->register(self::FLOWER_DEFAULT, new FlowersFeature(
			(new BlockClusterConfigurationBuilder(new WeightedStateProvider([BlockFactory::get(BlockIds::POPPY), BlockFactory::get(BlockIds::POPPY), BlockFactory::get(BlockIds::DANDELION)]), new SimpleBlockPlacer(), new PlantSupport()))
				->tries(64)
				->build()
		));
		$featureFactory->register(self::FLOWER_FOREST, new FlowersFeature(
			(new BlockClusterConfigurationBuilder(new ForestFlowerStateProvider(), new SimpleBlockPlacer(), new PlantSupport()))
				->tries(64)
				->build()
		));
		$featureFactory->register(self::FLOWER_SWAMP, new FlowersFeature(
			(new BlockClusterConfigurationBuilder(BlockStateProvider::simple(BlockFactory::get(BlockIds::RED_FLOWER, Flower::TYPE_BLUE_ORCHID)), new SimpleBlockPlacer(), new PlantSupport()))
				->tries(64)
				->build()
		));
		$featureFactory->register(self::FLOWER_PLAIN, new FlowersFeature(
			(new BlockClusterConfigurationBuilder(new PlainFlowerStateProvider(), new SimpleBlockPlacer(), new PlantSupport()))
				->tries(64)
				->build()
		));

		$featureFactory->register(self::FOREST_FLOWER_VEGETATION, new SingleRandomFeature(new SingleRandomConfiguration([
			new RandomPatchFeature((new BlockClusterConfigurationBuilder(BlockStateProvider::simple(BlockFactory::get(BlockIds::DOUBLE_PLANT, DoublePlant::TYPE_LILAC)), new DoublePlantBlockPlacer(), new DoublePlantSupport()))->tries(64)->build()),
			new RandomPatchFeature((new BlockClusterConfigurationBuilder(BlockStateProvider::simple(BlockFactory::get(BlockIds::DOUBLE_PLANT, DoublePlant::TYPE_ROSE_BUSH)), new DoublePlantBlockPlacer(), new DoublePlantSupport()))->tries(64)->build()),
			new RandomPatchFeature((new BlockClusterConfigurationBuilder(BlockStateProvider::simple(BlockFactory::get(BlockIds::DOUBLE_PLANT, DoublePlant::TYPE_PEONY)), new DoublePlantBlockPlacer(), new DoublePlantSupport()))->tries(64)->build()),
			new FlowersFeature((new BlockClusterConfigurationBuilder(BlockStateProvider::simple(BlockFactory::get(BlockIds::RED_FLOWER, Flower::TYPE_LILY_OF_THE_VALLEY)), new SimpleBlockPlacer(), new PlantSupport()))->tries(64)->build())
		])));
		$featureFactory->register(self::DARK_FOREST_VEGETATION_BROWN, new MultipleWithChanceRandomFeature(new MultipleRandomFeatureConfiguration([
			new ConfiguredRandomFeatureList($featureFactory->get(TreeFeatures::HUGE_BROWN_MUSHROOM), 0.025),
			new ConfiguredRandomFeatureList($featureFactory->get(TreeFeatures::HUGE_RED_MUSHROOM), 0.05),
			new ConfiguredRandomFeatureList($featureFactory->get(TreeFeatures::DARK_OAK), 0.6666667),
			new ConfiguredRandomFeatureList($featureFactory->get(TreeFeatures::BIRCH), 0.2),
			new ConfiguredRandomFeatureList($featureFactory->get(TreeFeatures::FANCY_OAK), 0.1)
		], $featureFactory->get(TreeFeatures::OAK))));
		$featureFactory->register(self::DARK_FOREST_VEGETATION_RED, new MultipleWithChanceRandomFeature(new MultipleRandomFeatureConfiguration([
			new ConfiguredRandomFeatureList($featureFactory->get(TreeFeatures::HUGE_RED_MUSHROOM), 0.025),
			new ConfiguredRandomFeatureList($featureFactory->get(TreeFeatures::HUGE_BROWN_MUSHROOM), 0.05),
			new ConfiguredRandomFeatureList($featureFactory->get(TreeFeatures::DARK_OAK), 0.6666667),
			new ConfiguredRandomFeatureList($featureFactory->get(TreeFeatures::BIRCH), 0.2),
			new ConfiguredRandomFeatureList($featureFactory->get(TreeFeatures::FANCY_OAK), 0.1)
		], $featureFactory->get(TreeFeatures::OAK))));
		$featureFactory->register(self::FOREST_FLOWER_TREES, new MultipleWithChanceRandomFeature(new MultipleRandomFeatureConfiguration([
			new ConfiguredRandomFeatureList($featureFactory->get(TreeFeatures::BIRCH_BEES_002), 0.2),
			new ConfiguredRandomFeatureList($featureFactory->get(TreeFeatures::FANCY_OAK_BEES_002), 0.1)
		], $featureFactory->get(TreeFeatures::OAK_BEES_002))));
		$featureFactory->register(self::TAIGA_VEGETATION, new MultipleWithChanceRandomFeature(new MultipleRandomFeatureConfiguration([
			new ConfiguredRandomFeatureList($featureFactory->get(TreeFeatures::PINE), 0.33333334)
		], $featureFactory->get(TreeFeatures::SPRUCE))));
		$featureFactory->register(self::TREES_SAVANNA, new MultipleWithChanceRandomFeature(new MultipleRandomFeatureConfiguration([
			new ConfiguredRandomFeatureList($featureFactory->get(TreeFeatures::ACACIA), 0.8)
		], $featureFactory->get(TreeFeatures::OAK))));
		$featureFactory->register(self::BIRCH_TALL, new MultipleWithChanceRandomFeature(new MultipleRandomFeatureConfiguration([
			new ConfiguredRandomFeatureList($featureFactory->get(TreeFeatures::SUPER_BIRCH_BEES_0002), 0.5),
		], $featureFactory->get(TreeFeatures::BIRCH_BEES_0002))));
		$featureFactory->register(self::TREES_MOUNTAIN, new MultipleWithChanceRandomFeature(new MultipleRandomFeatureConfiguration([
			new ConfiguredRandomFeatureList($featureFactory->get(TreeFeatures::SPRUCE), 0.666),
			new ConfiguredRandomFeatureList($featureFactory->get(TreeFeatures::FANCY_OAK), 0.1)
		], $featureFactory->get(TreeFeatures::OAK))));
		$featureFactory->register(self::TREES_WATER, new MultipleWithChanceRandomFeature(new MultipleRandomFeatureConfiguration([
			new ConfiguredRandomFeatureList($featureFactory->get(TreeFeatures::FANCY_OAK), 0.1)
		], $featureFactory->get(TreeFeatures::OAK))));
		$featureFactory->register(self::BIRCH_OTHER, new MultipleWithChanceRandomFeature(new MultipleRandomFeatureConfiguration([
			new ConfiguredRandomFeatureList($featureFactory->get(TreeFeatures::BIRCH_BEES_0002), 0.2),
			new ConfiguredRandomFeatureList($featureFactory->get(TreeFeatures::FANCY_OAK_BEES_0002), 0.1)
		], $featureFactory->get(TreeFeatures::OAK_BEES_0002))));
		$featureFactory->register(self::PLAIN_VEGETATION, new MultipleWithChanceRandomFeature(new MultipleRandomFeatureConfiguration([
			new ConfiguredRandomFeatureList($featureFactory->get(TreeFeatures::FANCY_OAK_BEES_005), 0.33333334)
		], $featureFactory->get(TreeFeatures::OAK_BEES_005))));
		$featureFactory->register(self::TREES_JUNGLE_EDGE, new MultipleWithChanceRandomFeature(new MultipleRandomFeatureConfiguration([
			new ConfiguredRandomFeatureList($featureFactory->get(TreeFeatures::FANCY_OAK), 0.1),
			new ConfiguredRandomFeatureList($featureFactory->get(TreeFeatures::JUNGLE_BUSH), 0.5)
		], $featureFactory->get(TreeFeatures::JUNGLE_TREE))));
		$featureFactory->register(self::TREES_GIANT_SPRUCE, new MultipleWithChanceRandomFeature(new MultipleRandomFeatureConfiguration([
			new ConfiguredRandomFeatureList($featureFactory->get(TreeFeatures::MEGA_SPRUCE), 0.33333334),
			new ConfiguredRandomFeatureList($featureFactory->get(TreeFeatures::PINE), 0.33333334)
		], $featureFactory->get(TreeFeatures::SPRUCE))));
		$featureFactory->register(self::TREES_GIANT, new MultipleWithChanceRandomFeature(new MultipleRandomFeatureConfiguration([
			new ConfiguredRandomFeatureList($featureFactory->get(TreeFeatures::MEGA_SPRUCE), 0.025641026),
			new ConfiguredRandomFeatureList($featureFactory->get(TreeFeatures::MEGA_PINE), 0.30769232),
			new ConfiguredRandomFeatureList($featureFactory->get(TreeFeatures::PINE), 0.33333334)
		], $featureFactory->get(TreeFeatures::SPRUCE))));
		$featureFactory->register(self::TREES_JUNGLE, new MultipleWithChanceRandomFeature(new MultipleRandomFeatureConfiguration([
			new ConfiguredRandomFeatureList($featureFactory->get(TreeFeatures::FANCY_OAK), 0.1),
			new ConfiguredRandomFeatureList($featureFactory->get(TreeFeatures::JUNGLE_BUSH), 0.5),
			new ConfiguredRandomFeatureList($featureFactory->get(TreeFeatures::MEGA_JUNGLE_TREE), 0.33333334)
		], $featureFactory->get(TreeFeatures::JUNGLE_TREE))));
		$featureFactory->register(self::BAMBOO_VEGETATION, new MultipleWithChanceRandomFeature(new MultipleRandomFeatureConfiguration([
			new ConfiguredRandomFeatureList($featureFactory->get(TreeFeatures::FANCY_OAK), 0.05),
			new ConfiguredRandomFeatureList($featureFactory->get(TreeFeatures::JUNGLE_BUSH), 0.15),
			new ConfiguredRandomFeatureList($featureFactory->get(TreeFeatures::MEGA_JUNGLE_TREE), 0.7)
		], $featureFactory->get(VegetationPlacements::PATCH_GRASS_JUNGLE))));
		$featureFactory->register(self::MUSHROOM_FIELD_VEGETATION, new TwoFeatureChoiceFeature(new TwoFeatureChoiceConfiguration(
			$featureFactory->get(TreeFeatures::HUGE_RED_MUSHROOM), $featureFactory->get(TreeFeatures::HUGE_BROWN_MUSHROOM)
		)));
	}

	private static function createSimpleX2Patch(Block $block) : BlockClusterConfigurationBuilder {
		return (new BlockClusterConfigurationBuilder(BlockStateProvider::simple($block), new SimpleBlockPlacer(), new PlantSupport()));
	}

	private static function createSimpleDoublePlant(Block $block) : BlockClusterConfigurationBuilder {
		return (new BlockClusterConfigurationBuilder(BlockStateProvider::simple($block), new DoublePlantBlockPlacer(), new DoublePlantSupport()));
	}
}
