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

use pocketmine\level\generator\vanilla\block\BlockFactory;
use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\block\Dirt;
use pocketmine\level\generator\vanilla\level\generator\feature\configurations\BlockStateConfiguration;
use pocketmine\level\generator\vanilla\level\generator\feature\configurations\LakeConfiguration;
use pocketmine\level\generator\vanilla\level\generator\feature\configurations\NoneFeatureConfiguration;
use pocketmine\level\generator\vanilla\level\generator\feature\configurations\SphereReplaceConfiguration;
use pocketmine\level\generator\vanilla\level\generator\feature\stateproviders\BlockStateProvider;

final class MiscOverworldFeatures{

	public const ICE_SPIKE = "ice_spike";
	public const ICE_PATCH = "ice_patch";
	public const FOREST_ROCK = "forest_rock";
	public const ICEBERG_PACKED = "iceberg_packed";
	public const ICEBERG_BLUE = "iceberg_blue";
	public const BLUE_ICE = "blue_ice";
	public const LAKE_LAVA = "lake_lava";
	public const LAKE_WATER = "lake_water";
	public const DISK_CLAY = "disk_clay";
	public const DISK_GRAVEL = "disk_gravel";
	public const DISK_SAND = "disk_sand";
	public const FREEZE_TOP_LAYER = "freeze_top_layer";
	public const DISK_GRASS = "disk_grass";
	public const BONUS_CHEST = "bonus_chest";
	public const VOID_START_PLATFORM = "void_start_platform";
	public const DESERT_WELL = "desert_well";
	public const SPRING_LAVA_OVERWORLD = "spring_lava_overworld";
	public const SPRING_LAVA_FROZEN = "spring_lava_frozen";
	public const SPRING_WATER = "spring_water";

	private function __construct(){
		//NOOP
	}

	public static function bootstrap(FeatureFactory $featureFactory) : void {
		$featureFactory->register(self::ICE_SPIKE, new IceSpikeFeature(new NoneFeatureConfiguration()));
		$featureFactory->register(self::ICE_PATCH, new IcePathFeature(new SphereReplaceConfiguration(BlockFactory::get(BlockIds::PACKED_ICE), new FeatureSpread(2, 1), 1, [BlockFactory::get(BlockIds::DIRT), BlockFactory::get(BlockIds::GRASS), BlockFactory::get(BlockIds::PODZOL), BlockFactory::get(BlockIds::DIRT, Dirt::TYPE_COARSE), BlockFactory::get(BlockIds::MYCELIUM), BlockFactory::get(BlockIds::SNOW_BLOCK), BlockFactory::get(BlockIds::ICE)])));
		$featureFactory->register(self::FOREST_ROCK, new BlockBlobFeature(new BlockStateConfiguration(BlockFactory::get(BlockIds::MOSSY_COBBLESTONE))));

		$featureFactory->register(self::LAKE_LAVA, new LakeFeature(new LakeConfiguration(
			BlockStateProvider::simple(BlockFactory::get(BlockIds::LAVA))
		)));
		$featureFactory->register(self::LAKE_WATER, new LakeFeature(new LakeConfiguration(
			BlockStateProvider::simple(BlockFactory::get(BlockIds::WATER))
		)));
		$featureFactory->register(self::DISK_CLAY, new SphereReplaceFeature(new SphereReplaceConfiguration(BlockFactory::get(BlockIds::CLAY_BLOCK), new FeatureSpread(2, 1), 1, [BlockFactory::get(BlockIds::DIRT), BlockFactory::get(BlockIds::CLAY_BLOCK)])));
		$featureFactory->register(self::DISK_GRAVEL, new SphereReplaceFeature(new SphereReplaceConfiguration(BlockFactory::get(BlockIds::GRAVEL), new FeatureSpread(2, 3), 2, [BlockFactory::get(BlockIds::DIRT), BlockFactory::get(BlockIds::GRASS)])));
		$featureFactory->register(self::DISK_SAND, new SphereReplaceFeature(new SphereReplaceConfiguration(BlockFactory::get(BlockIds::SAND), new FeatureSpread(2, 4), 2, [BlockFactory::get(BlockIds::DIRT), BlockFactory::get(BlockIds::GRASS)])));
		$featureFactory->register(self::FREEZE_TOP_LAYER, new IceAndSnowFeature());

		$featureFactory->register(self::DESERT_WELL, new DesertWellsFeature());
	}
}
