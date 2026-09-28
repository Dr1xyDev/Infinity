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
use pocketmine\level\generator\vanilla\block\Stone;
use pocketmine\level\generator\vanilla\level\generator\feature\configurations\OreConfiguration;
use pocketmine\level\generator\vanilla\level\generator\feature\configurations\ReplaceBlockConfiguration;
use pocketmine\level\generator\vanilla\level\generator\feature\template\BlockClassTest;
use pocketmine\level\generator\vanilla\level\generator\feature\template\BlockMatchTest;

final class OreFeatures{

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

	public static function bootstrap(FeatureFactory $featureFactory) : void {
		$netherrackRuleTest = new BlockMatchTest(BlockFactory::get(BlockIds::NETHERRACK));
		$stoneRuleTest = new BlockClassTest(Stone::class);
		$featureFactory->register(self::ORE_MAGMA, new OreFeature(new OreConfiguration($netherrackRuleTest, BlockFactory::get(BlockIds::MAGMA), 33)));
		$featureFactory->register(self::ORE_SOUL_SAND, new OreFeature(new OreConfiguration($netherrackRuleTest, BlockFactory::get(BlockIds::SOUL_SAND), 12)));
		$featureFactory->register(self::ORE_GOLD_DELTAS, new OreFeature(new OreConfiguration($netherrackRuleTest, BlockFactory::get(BlockIds::NETHER_GOLD_ORE), 10)));
		$featureFactory->register(self::ORE_QUARTZ_DELTAS, new OreFeature(new OreConfiguration($netherrackRuleTest, BlockFactory::get(BlockIds::NETHER_QUARTZ_ORE), 14)));
		$featureFactory->register(self::ORE_GOLD_NETHER, new OreFeature(new OreConfiguration($netherrackRuleTest, BlockFactory::get(BlockIds::NETHER_GOLD_ORE), 10)));
		$featureFactory->register(self::ORE_QUARTZ_NETHER, new OreFeature(new OreConfiguration($netherrackRuleTest, BlockFactory::get(BlockIds::NETHER_QUARTZ_ORE), 14)));
		$featureFactory->register(self::ORE_GRAVEL_NETHER, new OreFeature(new OreConfiguration($netherrackRuleTest, BlockFactory::get(BlockIds::GRAVEL), 33)));
		$featureFactory->register(self::ORE_BLACKSTONE, new OreFeature(new OreConfiguration($netherrackRuleTest, BlockFactory::get(BlockIds::BLACKSTONE), 33)));
		$featureFactory->register(self::ORE_DIRT, new OreFeature(new OreConfiguration($stoneRuleTest, BlockFactory::get(BlockIds::DIRT), 33)));
		$featureFactory->register(self::ORE_GRAVEL, new OreFeature(new OreConfiguration($stoneRuleTest, BlockFactory::get(BlockIds::GRAVEL), 33)));
		$featureFactory->register(self::ORE_GRANITE, new OreFeature(new OreConfiguration($stoneRuleTest, BlockFactory::get(BlockIds::STONE, Stone::GRANITE), 33)));
		$featureFactory->register(self::ORE_DIORITE, new OreFeature(new OreConfiguration($stoneRuleTest, BlockFactory::get(BlockIds::STONE, Stone::DIORITE), 33)));
		$featureFactory->register(self::ORE_ANDESITE, new OreFeature(new OreConfiguration($stoneRuleTest, BlockFactory::get(BlockIds::STONE, Stone::ANDESITE), 33)));
		$featureFactory->register(self::ORE_COAL, new OreFeature(new OreConfiguration($stoneRuleTest, BlockFactory::get(BlockIds::COAL_ORE), 17)));
		$featureFactory->register(self::ORE_IRON, new OreFeature(new OreConfiguration($stoneRuleTest, BlockFactory::get(BlockIds::IRON_ORE), 9)));
		$featureFactory->register(self::ORE_GOLD_EXTRA, new OreFeature(new OreConfiguration($stoneRuleTest, BlockFactory::get(BlockIds::GOLD_ORE), 9)));
		$featureFactory->register(self::ORE_GOLD, new OreFeature(new OreConfiguration($stoneRuleTest, BlockFactory::get(BlockIds::GOLD_ORE), 9)));
		$featureFactory->register(self::ORE_REDSTONE, new OreFeature(new OreConfiguration($stoneRuleTest, BlockFactory::get(BlockIds::REDSTONE_ORE), 8)));
		$featureFactory->register(self::ORE_DIAMOND, new OreFeature(new OreConfiguration($stoneRuleTest, BlockFactory::get(BlockIds::DIAMOND_ORE), 8)));
		$featureFactory->register(self::ORE_LAPIS, new OreFeature(new OreConfiguration($stoneRuleTest, BlockFactory::get(BlockIds::LAPIS_ORE), 7)));
		$featureFactory->register(self::ORE_INFESTED, new OreFeature(new OreConfiguration($stoneRuleTest, BlockFactory::get(BlockIds::MONSTER_EGG), 9)));
		$featureFactory->register(self::ORE_EMERALD, new ReplaceBlockFeature(new ReplaceBlockConfiguration(BlockFactory::get(BlockIds::STONE), BlockFactory::get(BlockIds::EMERALD_ORE))));
		$featureFactory->register(self::ORE_DEBRIS_LARGE, new OreFeature(new OreConfiguration($netherrackRuleTest, BlockFactory::get(BlockIds::ANCIENT_DEBRIS), 3)));
		$featureFactory->register(self::ORE_DEBRIS_SMALL, new OreFeature(new OreConfiguration($netherrackRuleTest, BlockFactory::get(BlockIds::ANCIENT_DEBRIS), 2)));
	}
}
