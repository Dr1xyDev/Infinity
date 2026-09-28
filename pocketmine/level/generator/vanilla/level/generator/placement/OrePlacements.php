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
use pocketmine\level\generator\vanilla\level\generator\feature\OreFeatures;
use pocketmine\level\generator\vanilla\level\generator\verticalanchor\VerticalAnchor;
use pocketmine\level\generator\vanilla\utils\valueproviders\ConstantInt;

final class OrePlacements{

	public const ORE_MAGMA = "ore_magma";
	public const ORE_SOUL_SAND = "ore_soul_sand";
	public const ORE_GOLD_DELTAS = "ore_gold_deltas";
	public const ORE_QUARTZ_DELTAS = "ore_quartz_deltas";
	public const ORE_GOLD_NETHER = "ore_gold_nether";
	public const ORE_QUARTZ_NETHER = "ore_quartz_nether";
	public const ORE_GRAVEL_NETHER = "ore_gravel_nether";
	public const ORE_BLACKSTONE = "ore_blackstone";
	public const ORE_DIRT = "ore_dirt";
	public const ORE_GRAVEL = "ore_gravel";
	public const ORE_GRANITE = "ore_granite";
	public const ORE_DIORITE = "ore_diorite";
	public const ORE_ANDESITE = "ore_andesite";
	public const ORE_COAL = "ore_coal";
	public const ORE_IRON = "ore_iron";
	public const ORE_GOLD_EXTRA = "ore_gold_extra";
	public const ORE_GOLD = "ore_gold";
	public const ORE_REDSTONE = "ore_redstone";
	public const ORE_DIAMOND = "ore_diamond";
	public const ORE_LAPIS = "ore_lapis";
	public const ORE_INFESTED = "ore_infested";
	public const ORE_EMERALD = "ore_emerald";
	public const ORE_DEBRIS_LARGE = "ore_debris_large";
	public const ORE_DEBRIS_SMALL = "ore_debris_small";

	private function __construct(){
		//NOOP
	}

	public static function bootstrap(PlacementFactory $placementFactory) : void {
		$range10x10 = HeightRangePlacement::uniform(VerticalAnchor::aboveBottom(10), VerticalAnchor::belowTop(10));
		$range8x8 = HeightRangePlacement::uniform(VerticalAnchor::aboveBottom(8), VerticalAnchor::belowTop(8));

		$features = FeatureFactory::getInstance();
		$placementFactory->register(self::ORE_MAGMA, $features->get(OreFeatures::ORE_MAGMA), self::commonOrePlacement(4, HeightRangePlacement::uniform(VerticalAnchor::absolute(27), VerticalAnchor::absolute(36))));
		$placementFactory->register(self::ORE_SOUL_SAND, $features->get(OreFeatures::ORE_SOUL_SAND), self::commonOrePlacement(12, HeightRangePlacement::uniform(VerticalAnchor::bottom(), VerticalAnchor::absolute(31))));
		$placementFactory->register(self::ORE_GOLD_DELTAS, $features->get(OreFeatures::ORE_GOLD_DELTAS), self::commonOrePlacement(20, $range10x10));
		$placementFactory->register(self::ORE_QUARTZ_DELTAS, $features->get(OreFeatures::ORE_QUARTZ_DELTAS), self::commonOrePlacement(32, $range10x10));
		$placementFactory->register(self::ORE_GOLD_NETHER, $features->get(OreFeatures::ORE_GOLD_NETHER), self::commonOrePlacement(10, $range10x10));
		$placementFactory->register(self::ORE_QUARTZ_NETHER, $features->get(OreFeatures::ORE_QUARTZ_NETHER), self::commonOrePlacement(16, $range10x10));
		$placementFactory->register(self::ORE_GRAVEL_NETHER, $features->get(OreFeatures::ORE_GRAVEL_NETHER), self::commonOrePlacement(2, HeightRangePlacement::uniform(VerticalAnchor::absolute(2), VerticalAnchor::absolute(41))));
		$placementFactory->register(self::ORE_BLACKSTONE, $features->get(OreFeatures::ORE_BLACKSTONE), self::commonOrePlacement(2, HeightRangePlacement::uniform(VerticalAnchor::absolute(5), VerticalAnchor::absolute(31))));
		$placementFactory->register(self::ORE_DIRT, $features->get(OreFeatures::ORE_DIRT), self::commonOrePlacement(7, HeightRangePlacement::uniform(VerticalAnchor::bottom(), VerticalAnchor::absolute(160))));
		$placementFactory->register(self::ORE_GRAVEL, $features->get(OreFeatures::ORE_GRAVEL), self::commonOrePlacement(14, HeightRangePlacement::uniform(VerticalAnchor::bottom(), VerticalAnchor::top())));
		$placementFactory->register(self::ORE_GRANITE, $features->get(OreFeatures::ORE_GRANITE), self::commonOrePlacement(10, HeightRangePlacement::uniform(VerticalAnchor::bottom(), VerticalAnchor::absolute(80))));
		$placementFactory->register(self::ORE_DIORITE, $features->get(OreFeatures::ORE_DIORITE), self::commonOrePlacement(10, HeightRangePlacement::uniform(VerticalAnchor::bottom(), VerticalAnchor::absolute(80))));
		$placementFactory->register(self::ORE_ANDESITE, $features->get(OreFeatures::ORE_ANDESITE), self::commonOrePlacement(10, HeightRangePlacement::uniform(VerticalAnchor::bottom(), VerticalAnchor::absolute(80))));
		$placementFactory->register(self::ORE_COAL, $features->get(OreFeatures::ORE_COAL), self::commonOrePlacement(20, HeightRangePlacement::uniform(VerticalAnchor::bottom(), VerticalAnchor::absolute(128))));
		$placementFactory->register(self::ORE_IRON, $features->get(OreFeatures::ORE_IRON), self::commonOrePlacement(20, HeightRangePlacement::uniform(VerticalAnchor::bottom(), VerticalAnchor::absolute(64))));
		$placementFactory->register(self::ORE_GOLD_EXTRA, $features->get(OreFeatures::ORE_GOLD_EXTRA), self::commonOrePlacement(50, HeightRangePlacement::uniform(VerticalAnchor::absolute(32), VerticalAnchor::absolute(256))));
		$placementFactory->register(self::ORE_GOLD, $features->get(OreFeatures::ORE_GOLD), self::commonOrePlacement(2, HeightRangePlacement::uniform(VerticalAnchor::bottom(), VerticalAnchor::absolute(32))));
		$placementFactory->register(self::ORE_REDSTONE, $features->get(OreFeatures::ORE_REDSTONE), self::commonOrePlacement(8, HeightRangePlacement::uniform(VerticalAnchor::bottom(), VerticalAnchor::absolute(16))));
		$placementFactory->register(self::ORE_DIAMOND, $features->get(OreFeatures::ORE_DIAMOND), self::commonOrePlacement(2, HeightRangePlacement::uniform(VerticalAnchor::bottom(), VerticalAnchor::absolute(16))));
		$placementFactory->register(self::ORE_LAPIS, $features->get(OreFeatures::ORE_LAPIS), self::commonOrePlacement(4, HeightRangePlacement::uniform(VerticalAnchor::bottom(), VerticalAnchor::absolute(16))));
		$placementFactory->register(self::ORE_INFESTED, $features->get(OreFeatures::ORE_INFESTED), self::commonOrePlacement(7, HeightRangePlacement::uniform(VerticalAnchor::bottom(), VerticalAnchor::absolute(64))));
		$placementFactory->register(self::ORE_EMERALD, $features->get(OreFeatures::ORE_EMERALD), self::commonOrePlacement(2, HeightRangePlacement::uniform(VerticalAnchor::absolute(4), VerticalAnchor::absolute(32))));
		$placementFactory->register(self::ORE_DEBRIS_LARGE, $features->get(OreFeatures::ORE_DEBRIS_LARGE), [new InSquarePlacement(), HeightRangePlacement::triangle(VerticalAnchor::absolute(8), VerticalAnchor::absolute(24))]);
		$placementFactory->register(self::ORE_DEBRIS_SMALL, $features->get(OreFeatures::ORE_DEBRIS_SMALL), [new InSquarePlacement(), $range8x8]);
	}

	private static function orePlacement(PlacementModifier $frequencyModifier, PlacementModifier $heightRange) : array {
		return [$frequencyModifier, new InSquarePlacement(), $heightRange];
	}

	private static function commonOrePlacement(int $count, PlacementModifier $heightRange) : array {
		return self::orePlacement(new CountPlacement(ConstantInt::of($count)), $heightRange);
	}

	private static function rareOrePlacement(int $rarity, PlacementModifier $heightRange) : array {
		return self::orePlacement(new RarityFilter($rarity), $heightRange);
	}
}
