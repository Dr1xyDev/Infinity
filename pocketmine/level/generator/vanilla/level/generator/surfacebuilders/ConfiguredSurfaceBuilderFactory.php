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

namespace pocketmine\level\generator\vanilla\level\generator\surfacebuilders;

use pocketmine\level\generator\vanilla\block\BlockFactory;
use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\block\utils\ColorBlockMetaHelper;
use pocketmine\level\generator\vanilla\utils\SingletonTrait;

class ConfiguredSurfaceBuilderFactory {
	use SingletonTrait;

	public const BADLANDS = "badlands";
	public const BASALT_DELTAS = "basalt_deltas";
	public const CRIMSON_FOREST = "crimson_forest";
	public const DESERT = "desert";
	public const END = "end";
	public const ERODED_BADLANDS = "eroded_badlands";
	public const FROZEN_OCEAN = "frozen_ocean";
	public const FULL_SAND = "full_sand";
	public const GIANT_TREE_TAIGA = "giant_tree_taiga";
	public const GRASS = "grass";
	public const GRAVELLY_MOUNTAIN = "gravelly_mountain";
	public const ICE_SPIKES = "ice_spikes";
	public const MOUNTAIN = "mountain";
	public const MYCELIUM = "mycelium";
	public const NETHER = "nether";
	public const NOPE = "nope";
	public const OCEAN_SAND = "ocean_sand";
	public const SHATTERED_SAVANNA = "shattered_savanna";
	public const SOUL_SAND_VALLEY = "soul_sand_valley";
	public const STONE = "stone";
	public const SWAMP = "swamp";
	public const WARPED_FOREST = "warped_forest";
	public const WOODED_BADLANDS = "wooded_badlands";

	/** @var ConfiguredSurfaceBuilder[] */
	private array $surfaceBuilders = [];

	public function __construct(){
		$stone = BlockFactory::get(BlockIds::STONE);
		$sand = BlockFactory::get(BlockIds::SAND);
		$gravel = BlockFactory::get(BlockIds::GRAVEL);
		$dirt = BlockFactory::get(BlockIds::DIRT);
		$grassBlock = BlockFactory::get(BlockIds::GRASS);
		$netherrack = BlockFactory::get(BlockIds::NETHERRACK);
		$endStone = BlockFactory::get(BlockIds::END_STONE);
		$blackstone = BlockFactory::get(BlockIds::BLACKSTONE);
		$basalt = BlockFactory::get(BlockIds::BASALT);
		$soulSand = BlockFactory::get(BlockIds::SOUL_SAND);

		$grassDirtGravelConfig = new SurfaceBuilderConfig($grassBlock, $dirt, $gravel);
		$stoneGravelConfig = new SurfaceBuilderConfig($stone, $stone, $gravel);
		$sandGravelConfig = new SurfaceBuilderConfig($sand, $sand, $gravel);
		$sandFullConfig = new SurfaceBuilderConfig($sand, $sand, $sand);
		$netherrackConfig = new SurfaceBuilderConfig($netherrack, $netherrack, $netherrack);
		$endStoneConfig = new SurfaceBuilderConfig($endStone, $endStone, $endStone);
		$soulSandConfig = new SurfaceBuilderConfig($soulSand, $soulSand, $soulSand);
		$badlandsConfig = new SurfaceBuilderConfig(
			$sand,
			BlockFactory::get(BlockIds::TERRACOTTA, ColorBlockMetaHelper::WHITE),
			$gravel
		);

		$this->register(self::BADLANDS, new BadlandsSurfaceBuilder(), $badlandsConfig);
		$this->register(self::BASALT_DELTAS, new BasaltDeltasSurfaceBuilder(), new SurfaceBuilderConfig($blackstone, $basalt, BlockFactory::get(BlockIds::MAGMA)));
		$this->register(self::CRIMSON_FOREST, new NetherForestsSurfaceBuilder(), new SurfaceBuilderConfig(BlockFactory::get(BlockIds::CRIMSON_NYLIUM), $netherrack, BlockFactory::get(BlockIds::NETHER_WART_BLOCK)));
		$this->register(self::DESERT, new DefaultSurfaceBuilder(), $sandGravelConfig);
		$this->register(self::END, new DefaultSurfaceBuilder(), $endStoneConfig);
		$this->register(self::ERODED_BADLANDS, new ErodedBadlandsSurfaceBuilder(), $badlandsConfig);
		$this->register(self::FROZEN_OCEAN, new FrozenOceanSurfaceBuilder(), $grassDirtGravelConfig);
		$this->register(self::FULL_SAND, new DefaultSurfaceBuilder(), $sandFullConfig);
		$this->register(self::GIANT_TREE_TAIGA, new GiantTreeTaigaSurfaceBuilder(), $grassDirtGravelConfig);
		$this->register(self::GRASS, new DefaultSurfaceBuilder(), $grassDirtGravelConfig);
		$this->register(self::GRAVELLY_MOUNTAIN, new GravellyMountainSurfaceBuilder(), $grassDirtGravelConfig);
		$this->register(self::ICE_SPIKES, new DefaultSurfaceBuilder(), new SurfaceBuilderConfig(BlockFactory::get(BlockIds::SNOW_BLOCK), $dirt, $gravel));
		$this->register(self::MOUNTAIN, new MountainSurfaceBuilder(), $grassDirtGravelConfig);
		$this->register(self::MYCELIUM, new DefaultSurfaceBuilder(), new SurfaceBuilderConfig(BlockFactory::get(BlockIds::MYCELIUM), $dirt, $gravel));
		$this->register(self::NETHER, new NetherSurfaceBuilder(), $netherrackConfig);
		$this->register(self::NOPE, new NoopSurfaceBuilder(), $stoneGravelConfig);
		$this->register(self::OCEAN_SAND, new DefaultSurfaceBuilder(), new SurfaceBuilderConfig($grassBlock, $dirt, $sand));
		$this->register(self::SHATTERED_SAVANNA, new ShatteredSavannaSurfaceBuilder(), $grassDirtGravelConfig);
		$this->register(self::SOUL_SAND_VALLEY, new SoulSandValleySurfaceBuilder(), $soulSandConfig);
		$this->register(self::STONE, new DefaultSurfaceBuilder(), $stoneGravelConfig);
		$this->register(self::SWAMP, new SwampSurfaceBuilder(), $grassDirtGravelConfig);
		$this->register(self::WARPED_FOREST, new NetherForestsSurfaceBuilder(), new SurfaceBuilderConfig(BlockFactory::get(BlockIds::WARPED_NYLIUM), $netherrack, BlockFactory::get(BlockIds::WARPED_WART_BLOCK)));
		$this->register(self::WOODED_BADLANDS, new WoodedBadlandsSurfaceBuilder(), $badlandsConfig);
	}

	public function get(string $name) : ?ConfiguredSurfaceBuilder {
		return $this->surfaceBuilders[$name] ?? null;
	}

	public function register(string $name, SurfaceBuilder $builder, SurfaceBuilderConfig $config) : void {
		$this->surfaceBuilders[$name] = new ConfiguredSurfaceBuilder($builder, $config);
	}
}
