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

use pocketmine\level\generator\vanilla\level\generator\feature\FeatureFactory;
use pocketmine\level\generator\vanilla\level\generator\feature\MiscOverworldFeatures;
use pocketmine\level\generator\vanilla\utils\valueproviders\ConstantInt;

final class MiscOverworldPlacements{

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
	public const SPRING_LAVA_DOUBLE = "spring_lava_double";
	public const SPRING_LAVA = "spring_lava";
	public const SPRING_DELTA = "spring_delta";
	public const SPRING_CLOSED = "spring_closed";
	public const SPRING_CLOSED_DOUBLE = "spring_closed_double";
	public const SPRING_OPEN = "spring_open";
	public const SPRING_WATER = "spring_water";

	private function __construct(){
		//NOOP
	}

	public static function bootstrap(PlacementFactory $placementFactory) : void {
		$features = FeatureFactory::getInstance();
		$placementFactory->register(self::ICE_SPIKE, $features->get(MiscOverworldFeatures::ICE_SPIKE), [CountPlacement::of(ConstantInt::of(3)), new InSquarePlacement(), new HeightmapPlacement(HeightmantType::OCEAN_SOLID)]);
		$placementFactory->register(self::ICE_PATCH, $features->get(MiscOverworldFeatures::ICE_PATCH), [CountPlacement::of(ConstantInt::of(2)), new InSquarePlacement(), new HeightmapPlacement(HeightmantType::OCEAN_SOLID), RandomOffsetPlacement::vertical(ConstantInt::of(-1))]);
		$placementFactory->register(self::FOREST_ROCK, $features->get(MiscOverworldFeatures::FOREST_ROCK), [CountPlacement::of(ConstantInt::of(2)), new InSquarePlacement(), new HeightmapPlacement(HeightmantType::OCEAN_SOLID)]);

		$placementFactory->register(self::LAKE_LAVA, $features->get(MiscOverworldFeatures::LAKE_LAVA), [new LakeLavaPlacement(80)]);
		$placementFactory->register(self::LAKE_WATER, $features->get(MiscOverworldFeatures::LAKE_WATER), [new LakeWaterPlacement(4)]);
		$placementFactory->register(self::DISK_CLAY, $features->get(MiscOverworldFeatures::DISK_CLAY), [new InSquarePlacement(), new HeightmapPlacement(HeightmantType::OCEAN_SOLID)]);
		$placementFactory->register(self::DISK_GRAVEL, $features->get(MiscOverworldFeatures::DISK_GRAVEL), [new InSquarePlacement(), new HeightmapPlacement(HeightmantType::OCEAN_SOLID)]);
		$placementFactory->register(self::DISK_SAND, $features->get(MiscOverworldFeatures::DISK_SAND), [CountPlacement::of(ConstantInt::of(3)), new InSquarePlacement(), new HeightmapPlacement(HeightmantType::OCEAN_SOLID)]);
		$placementFactory->register(self::FREEZE_TOP_LAYER, $features->get(MiscOverworldFeatures::FREEZE_TOP_LAYER), []);

		$placementFactory->register(self::DESERT_WELL, $features->get(MiscOverworldFeatures::DESERT_WELL), [new RarityFilter(1000), new InSquarePlacement(), new HeightmapPlacement(HeightmantType::OCEAN_SOLID)]);
	}
}
