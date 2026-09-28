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

namespace pocketmine\level\generator\vanilla\block;

use InvalidArgumentException;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\level\Position;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\convert\block\RuntimeBlockMapping;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use RuntimeException;
use SplFixedArray;

use function array_fill;
use function array_filter;
use function min;
use function str_contains;

/**
 * Manages block registration and instance creation
 */
class BlockFactory
{
	/**
	 * @var SplFixedArray|Block[]
	 * @phpstan-var SplFixedArray<Block>
	 */
	public static $fullList = null;

	/**
	 * @var SplFixedArray|int[]
	 * @phpstan-var SplFixedArray<int>
	 */
	private static $mappedStateIds;

	/**
	 * @var SplFixedArray|int[]
	 * @phpstan-var SplFixedArray<int>
	 */
	public static $light;
	/**
	 * @var SplFixedArray|int[]
	 * @phpstan-var SplFixedArray<int>
	 */
	public static $lightFilter;
	/**
	 * @var SplFixedArray|bool[]
	 * @phpstan-var SplFixedArray<bool>
	 */
	public static $diffusesSkyLight;
	/**
	 * @var SplFixedArray|float[]
	 * @phpstan-var SplFixedArray<float>
	 */
	public static $blastResistance;

	/** @var SplFixedArray */
	public static $hasEntityCollision = null;

	/**
	 * Initializes the block factory. By default this is called only once on server start, however you may wish to use
	 * this if you need to reset the block factory back to its original defaults for whatever reason.
	 */
	public static function init() : void
	{
		if (self::$fullList === null) {
			self::$fullList = new SplFixedArray(1280 << Block::INTERNAL_METADATA_BITS);
			self::$mappedStateIds = new SplFixedArray(1280 << Block::INTERNAL_METADATA_BITS);

			self::$light = SplFixedArray::fromArray(array_fill(0, 1280 << Block::INTERNAL_METADATA_BITS, 0));
			self::$lightFilter = SplFixedArray::fromArray(array_fill(0, 1280 << Block::INTERNAL_METADATA_BITS, 1));
			self::$diffusesSkyLight = SplFixedArray::fromArray(array_fill(0, 1280 << Block::INTERNAL_METADATA_BITS, false));
			self::$blastResistance = SplFixedArray::fromArray(array_fill(0, 1280 << Block::INTERNAL_METADATA_BITS, 0.0));
			self::$hasEntityCollision = SplFixedArray::fromArray(array_fill(0, 1280 << Block::INTERNAL_METADATA_BITS, false));

			self::registerBlock(new Air());
			self::registerBlock(new Stone());
			self::registerBlock(new Grass());
			self::registerBlock(new Dirt());
			self::registerBlock(new Cobblestone());
			self::registerBlock(new Planks());
			self::registerBlock(new Sapling());
			self::registerBlock(new Bedrock());
			self::registerBlock(new Water());
			self::registerBlock(new StillWater());
			self::registerBlock(new Lava());
			self::registerBlock(new StillLava());
			self::registerBlock(new Sand());
			self::registerBlock(new Gravel());
			self::registerBlock(new GoldOre());
			self::registerBlock(new IronOre());
			self::registerBlock(new CoalOre());
			self::registerBlock(new Log(BlockIds::LOG, 0, "Log"));
			self::registerBlock(new Leaves());
			self::registerBlock(new Sponge());
			self::registerBlock(new Glass());
			self::registerBlock(new LapisOre());
			self::registerBlock(new Lapis());
			self::registerBlock(new Sandstone());
			self::registerBlock(new NoteBlock());
			self::registerBlock(new Bed());
			self::registerBlock(new PoweredRail());
			self::registerBlock(new DetectorRail());
			//TODO: STICKY_PISTON
			self::registerBlock(new Cobweb());
			self::registerBlock(new TallGrass());
			self::registerBlock(new DeadBush());
			//TODO: PISTON
			//TODO: PISTONARMCOLLISION
			self::registerBlock(new Wool());
			self::registerBlock(new Element(BlockIds::ELEMENT_0, 0, "???"));
			self::registerBlock(new Dandelion());
			self::registerBlock(new Flower());
			self::registerBlock(new BrownMushroom());
			self::registerBlock(new RedMushroom());
			self::registerBlock(new Gold());
			self::registerBlock(new Iron());
			self::registerBlock(new DoubleStoneSlab());
			self::registerBlock(new StoneSlab());
			self::registerBlock(new Bricks());
			self::registerBlock(new TNT());
			self::registerBlock(new Bookshelf());
			self::registerBlock(new MossyCobblestone());
			self::registerBlock(new Obsidian());
			self::registerBlock(new Torch());
			self::registerBlock(new Fire());
			self::registerBlock(new MonsterSpawner());
			self::registerBlock(new WoodenStairs(BlockIds::OAK_STAIRS, 0, "Oak Stairs"));
			self::registerBlock(new Chest());
			//TODO: REDSTONE_WIRE
			self::registerBlock(new DiamondOre());
			self::registerBlock(new Diamond());
			self::registerBlock(new CraftingTable());
			self::registerBlock(new Wheat());
			self::registerBlock(new Farmland());
			self::registerBlock(new Furnace());
			self::registerBlock(new BurningFurnace());
			self::registerBlock(new SignPost(BlockIds::SIGN_POST, 0, "Oak Sign Post", Item::SIGN, BlockIds::WALL_SIGN));
			self::registerBlock(new WoodenDoor(BlockIds::OAK_DOOR_BLOCK, 0, "Oak Door", Item::OAK_DOOR));
			self::registerBlock(new Ladder());
			self::registerBlock(new Rail());
			self::registerBlock(new CobblestoneStairs());
			self::registerBlock(new WallSign(BlockIds::WALL_SIGN, 0, "Oak Sign Post", Item::SIGN, BlockIds::WALL_SIGN));
			self::registerBlock(new Lever());
			self::registerBlock(new StonePressurePlate(BlockIds::STONE_PRESSURE_PLATE, 0, "Stone Pressure Plate"));
			self::registerBlock(new IronDoor());
			self::registerBlock(new WoodenPressurePlate(BlockIds::WOODEN_PRESSURE_PLATE, 0, "Wooden Pressure Plate"));
			self::registerBlock(new RedstoneOre());
			self::registerBlock(new GlowingRedstoneOre());
			self::registerBlock(new RedstoneTorchUnlit());
			self::registerBlock(new RedstoneTorch());
			self::registerBlock(new StoneButton(BlockIds::STONE_BUTTON, 0, "Stone Button"));
			self::registerBlock(new SnowLayer());
			self::registerBlock(new Ice());
			self::registerBlock(new Snow());
			self::registerBlock(new Cactus());
			self::registerBlock(new Clay());
			self::registerBlock(new Sugarcane());
			self::registerBlock(new Jukebox());
			self::registerBlock(new WoodenFence());
			self::registerBlock(new Pumpkin());
			self::registerBlock(new Netherrack());
			self::registerBlock(new SoulSand());
			self::registerBlock(new Glowstone());
			self::registerBlock(new NetherPortal());
			self::registerBlock(new LitPumpkin());
			self::registerBlock(new Cake());
			//TODO: REPEATER_BLOCK
			//TODO: POWERED_REPEATER
			self::registerBlock(new InvisibleBedrock());
			self::registerBlock(new WoodenTrapdoor(BlockIds::WOODEN_TRAPDOOR, 0, "Wooden Trapdoor"));
			self::registerBlock(new InfestedStone());
			self::registerBlock(new StoneBricks());
			self::registerBlock(new BrownMushroomBlock());
			self::registerBlock(new RedMushroomBlock());
			self::registerBlock(new IronBars());
			self::registerBlock(new GlassPane());
			self::registerBlock(new Melon());
			self::registerBlock(new PumpkinStem());
			self::registerBlock(new MelonStem());
			self::registerBlock(new Vine());
			self::registerBlock(new FenceGate(BlockIds::OAK_FENCE_GATE, 0, "Oak Fence Gate"));
			self::registerBlock(new BrickStairs());
			self::registerBlock(new StoneBrickStairs());
			self::registerBlock(new Mycelium());
			self::registerBlock(new WaterLily());
			self::registerBlock(new NetherBrick(BlockIds::NETHER_BRICK_BLOCK, 0, "Nether Bricks"));
			self::registerBlock(new NetherBrickFence());
			self::registerBlock(new NetherBrickStairs());
			self::registerBlock(new NetherWartPlant());
			self::registerBlock(new EnchantingTable());
			self::registerBlock(new BrewingStand());
			self::registerBlock(new Cauldron());
			self::registerBlock(new EndPortal());
			self::registerBlock(new EndPortalFrame());
			self::registerBlock(new EndStone());
			self::registerBlock(new DragonEgg());
			self::registerBlock(new RedstoneLamp());
			self::registerBlock(new LitRedstoneLamp());
			//TODO: DROPPER
			self::registerBlock(new ActivatorRail());
			self::registerBlock(new CocoaBlock());
			self::registerBlock(new SandstoneStairs());
			self::registerBlock(new EmeraldOre());
			self::registerBlock(new EnderChest());
			self::registerBlock(new TripwireHook());
			self::registerBlock(new Tripwire());
			self::registerBlock(new Emerald());
			self::registerBlock(new WoodenStairs(BlockIds::SPRUCE_STAIRS, 0, "Spruce Stairs"));
			self::registerBlock(new WoodenStairs(BlockIds::BIRCH_STAIRS, 0, "Birch Stairs"));
			self::registerBlock(new WoodenStairs(BlockIds::JUNGLE_STAIRS, 0, "Jungle Stairs"));
			self::registerBlock(new CommandBlock());
			self::registerBlock(new Beacon());
			self::registerBlock(new BrickWall(BlockIds::COBBLESTONE_WALL, 0, "Cobblestone Wall"));
			self::registerBlock(new FlowerPot());
			self::registerBlock(new Carrot());
			self::registerBlock(new Potato());
			self::registerBlock(new WoodenButton(BlockIds::WOODEN_BUTTON, 0, "Wooden Button"));
			self::registerBlock(new Skull());
			self::registerBlock(new Anvil());
			self::registerBlock(new TrappedChest());
			self::registerBlock(new WeightedPressurePlateLight());
			self::registerBlock(new WeightedPressurePlateHeavy());
			//TODO: COMPARATOR_BLOCK
			//TODO: POWERED_COMPARATOR
			self::registerBlock(new DaylightSensor());
			self::registerBlock(new Redstone());
			self::registerBlock(new NetherQuartzOre());
			self::registerBlock(new Hopper());
			self::registerBlock(new Quartz());
			self::registerBlock(new QuartzStairs());
			self::registerBlock(new DoubleWoodenSlab());
			self::registerBlock(new WoodenSlab());
			self::registerBlock(new StainedHardenedClay());
			self::registerBlock(new StainedGlassPane());
			self::registerBlock(new Leaves2());
			self::registerBlock(new Log2(BlockIds::LOG2, 0, "Log2"));
			self::registerBlock(new WoodenStairs(BlockIds::ACACIA_STAIRS, 0, "Acacia Stairs"));
			self::registerBlock(new WoodenStairs(BlockIds::DARK_OAK_STAIRS, 0, "Dark Oak Stairs"));
			self::registerBlock(new Slime());
			self::registerBlock(new IronTrapdoor(BlockIds::IRON_TRAPDOOR, 0, "Iron Trapdoor"));
			self::registerBlock(new Prismarine());
			self::registerBlock(new SeaLantern());
			self::registerBlock(new HayBale());
			self::registerBlock(new Carpet());
			self::registerBlock(new HardenedClay());
			self::registerBlock(new Coal());
			self::registerBlock(new PackedIce());
			self::registerBlock(new DoublePlant());
			self::registerBlock(new StandingBanner());
			self::registerBlock(new WallBanner());
			//TODO: DAYLIGHT_DETECTOR_INVERTED
			self::registerBlock(new RedSandstone());
			self::registerBlock(new RedSandstoneStairs());
			self::registerBlock(new DoubleStoneSlab2());
			self::registerBlock(new StoneSlab2());
			self::registerBlock(new FenceGate(BlockIds::SPRUCE_FENCE_GATE, 0, "Spruce Fence Gate"));
			self::registerBlock(new FenceGate(BlockIds::BIRCH_FENCE_GATE, 0, "Birch Fence Gate"));
			self::registerBlock(new FenceGate(BlockIds::JUNGLE_FENCE_GATE, 0, "Jungle Fence Gate"));
			self::registerBlock(new FenceGate(BlockIds::DARK_OAK_FENCE_GATE, 0, "Dark Oak Fence Gate"));
			self::registerBlock(new FenceGate(BlockIds::ACACIA_FENCE_GATE, 0, "Acacia Fence Gate"));
			self::registerBlock(new RepeatingCommandBlock());
			self::registerBlock(new ChainCommandBlock());
			//TODO: HARD_GRASS_PANE
			//TODO: HARD_STAINED_GLASS_PANE
			//TODO: CHEMICAL_HEAT
			self::registerBlock(new WoodenDoor(BlockIds::SPRUCE_DOOR_BLOCK, 0, "Spruce Door", Item::SPRUCE_DOOR));
			self::registerBlock(new WoodenDoor(BlockIds::BIRCH_DOOR_BLOCK, 0, "Birch Door", Item::BIRCH_DOOR));
			self::registerBlock(new WoodenDoor(BlockIds::JUNGLE_DOOR_BLOCK, 0, "Jungle Door", Item::JUNGLE_DOOR));
			self::registerBlock(new WoodenDoor(BlockIds::ACACIA_DOOR_BLOCK, 0, "Acacia Door", Item::ACACIA_DOOR));
			self::registerBlock(new WoodenDoor(BlockIds::DARK_OAK_DOOR_BLOCK, 0, "Dark Oak Door", Item::DARK_OAK_DOOR));
			self::registerBlock(new GrassPath());
			self::registerBlock(new ItemFrame());
			self::registerBlock(new ChorusFlower());
			self::registerBlock(new Purpur());
			//TODO: COLORED_TORCH_RG
			self::registerBlock(new PurpurStairs());
			self::registerBlock(new UndyedShulkerBox());
			self::registerBlock(new EndBricks());
			self::registerBlock(new FrostedIce());
			self::registerBlock(new EndRod());
			//TODO: END_GATEWAY
			//TODO: ALLOW
			//TODO: DENY
			//TODO: BORDER_BLOCK
			self::registerBlock(new Magma());
			self::registerBlock(new NetherWartBlock());
			self::registerBlock(new NetherBrick(BlockIds::RED_NETHER_BRICK, 0, "Red Nether Bricks"));
			self::registerBlock(new BoneBlock());
			self::registerBlock(new ShulkerBox());
			self::registerBlock(new GlazedTerracotta(BlockIds::PURPLE_GLAZED_TERRACOTTA, 0, "Purple Glazed Terracotta"));
			self::registerBlock(new GlazedTerracotta(BlockIds::WHITE_GLAZED_TERRACOTTA, 0, "White Glazed Terracotta"));
			self::registerBlock(new GlazedTerracotta(BlockIds::ORANGE_GLAZED_TERRACOTTA, 0, "Orange Glazed Terracotta"));
			self::registerBlock(new GlazedTerracotta(BlockIds::MAGENTA_GLAZED_TERRACOTTA, 0, "Magenta Glazed Terracotta"));
			self::registerBlock(new GlazedTerracotta(BlockIds::LIGHT_BLUE_GLAZED_TERRACOTTA, 0, "Light Blue Glazed Terracotta"));
			self::registerBlock(new GlazedTerracotta(BlockIds::YELLOW_GLAZED_TERRACOTTA, 0, "Yellow Glazed Terracotta"));
			self::registerBlock(new GlazedTerracotta(BlockIds::LIME_GLAZED_TERRACOTTA, 0, "Lime Glazed Terracotta"));
			self::registerBlock(new GlazedTerracotta(BlockIds::PINK_GLAZED_TERRACOTTA, 0, "Pink Glazed Terracotta"));
			self::registerBlock(new GlazedTerracotta(BlockIds::GRAY_GLAZED_TERRACOTTA, 0, "Grey Glazed Terracotta"));
			self::registerBlock(new GlazedTerracotta(BlockIds::SILVER_GLAZED_TERRACOTTA, 0, "Light Grey Glazed Terracotta"));
			self::registerBlock(new GlazedTerracotta(BlockIds::CYAN_GLAZED_TERRACOTTA, 0, "Cyan Glazed Terracotta"));

			self::registerBlock(new GlazedTerracotta(BlockIds::BLUE_GLAZED_TERRACOTTA, 0, "Blue Glazed Terracotta"));
			self::registerBlock(new GlazedTerracotta(BlockIds::BROWN_GLAZED_TERRACOTTA, 0, "Brown Glazed Terracotta"));
			self::registerBlock(new GlazedTerracotta(BlockIds::GREEN_GLAZED_TERRACOTTA, 0, "Green Glazed Terracotta"));
			self::registerBlock(new GlazedTerracotta(BlockIds::RED_GLAZED_TERRACOTTA, 0, "Red Glazed Terracotta"));
			self::registerBlock(new GlazedTerracotta(BlockIds::BLACK_GLAZED_TERRACOTTA, 0, "Black Glazed Terracotta"));
			self::registerBlock(new Concrete());
			self::registerBlock(new ConcretePowder());
			//TODO: CHEMISTRY_TABLE
			//TODO: UNDERWATER_TORCH
			self::registerBlock(new ChorusPlant());
			self::registerBlock(new StainedGlass());
			//TODO: CAMERA
			self::registerBlock(new Podzol());
			self::registerBlock(new Beetroot());
			self::registerBlock(new Stonecutter());
			self::registerBlock(new GlowingObsidian());
			self::registerBlock(new NetherReactor());
			self::registerBlock(new InfoUpdate(BlockIds::INFO_UPDATE, 0, "update!"));
			self::registerBlock(new InfoUpdate(BlockIds::INFO_UPDATE2, 0, "ate!upd"));
			//TODO: MOVINGBLOCK
			//TODO: OBSERVER
			//TODO: STRUCTURE_BLOCK
			//TODO: HARD_GLASS
			//TODO: HARD_STAINED_GLASS
			self::registerBlock(new Reserved6(BlockIds::RESERVED6, 0, "reserved6"));

			self::registerBlock(new PrismarineStairs());
			self::registerBlock(new DarkPrismarineStairs());
			self::registerBlock(new PrismarineBricksStairs());
			self::registerBlock(new StrippedLog(BlockIds::STRIPPED_SPRUCE_LOG, 0, "Spruce"));
			self::registerBlock(new StrippedLog(BlockIds::STRIPPED_BIRCH_LOG, 0, "Birch"));
			self::registerBlock(new StrippedLog(BlockIds::STRIPPED_JUNGLE_LOG, 0, "Jungle"));
			self::registerBlock(new StrippedLog(BlockIds::STRIPPED_ACACIA_LOG, 0, "Acacia"));
			self::registerBlock(new StrippedLog(BlockIds::STRIPPED_DARK_OAK_LOG, 0, "Dark Oak"));
			self::registerBlock(new StrippedLog(BlockIds::STRIPPED_OAK_LOG, 0, "Oak"));
			self::registerBlock(new BlueIce());
			self::registerBlock(new Element(BlockIds::ELEMENT_1, 0, "Hydrogen"));
			self::registerBlock(new Element(BlockIds::ELEMENT_2, 0, "Helium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_3, 0, "Lithium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_4, 0, "Beryllium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_5, 0, "Boron"));
			self::registerBlock(new Element(BlockIds::ELEMENT_6, 0, "Carbon"));
			self::registerBlock(new Element(BlockIds::ELEMENT_7, 0, "Nitrogen"));
			self::registerBlock(new Element(BlockIds::ELEMENT_8, 0, "Oxygen"));
			self::registerBlock(new Element(BlockIds::ELEMENT_9, 0, "Fluorine"));
			self::registerBlock(new Element(BlockIds::ELEMENT_10, 0, "Neon"));
			self::registerBlock(new Element(BlockIds::ELEMENT_11, 0, "Sodium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_12, 0, "Magnesium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_13, 0, "Aluminum"));
			self::registerBlock(new Element(BlockIds::ELEMENT_14, 0, "Silicon"));
			self::registerBlock(new Element(BlockIds::ELEMENT_15, 0, "Phosphorus"));
			self::registerBlock(new Element(BlockIds::ELEMENT_16, 0, "Sulfur"));
			self::registerBlock(new Element(BlockIds::ELEMENT_17, 0, "Chlorine"));
			self::registerBlock(new Element(BlockIds::ELEMENT_18, 0, "Argon"));
			self::registerBlock(new Element(BlockIds::ELEMENT_19, 0, "Potassium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_20, 0, "Calcium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_21, 0, "Scandium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_22, 0, "Titanium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_23, 0, "Vanadium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_24, 0, "Chromium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_25, 0, "Manganese"));
			self::registerBlock(new Element(BlockIds::ELEMENT_26, 0, "Iron"));
			self::registerBlock(new Element(BlockIds::ELEMENT_27, 0, "Cobalt"));
			self::registerBlock(new Element(BlockIds::ELEMENT_28, 0, "Nickel"));
			self::registerBlock(new Element(BlockIds::ELEMENT_29, 0, "Copper"));
			self::registerBlock(new Element(BlockIds::ELEMENT_30, 0, "Zinc"));
			self::registerBlock(new Element(BlockIds::ELEMENT_31, 0, "Gallium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_32, 0, "Germanium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_33, 0, "Arsenic"));
			self::registerBlock(new Element(BlockIds::ELEMENT_34, 0, "Selenium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_35, 0, "Bromine"));
			self::registerBlock(new Element(BlockIds::ELEMENT_36, 0, "Krypton"));
			self::registerBlock(new Element(BlockIds::ELEMENT_37, 0, "Rubidium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_38, 0, "Strontium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_39, 0, "Yttrium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_40, 0, "Zirconium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_41, 0, "Niobium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_42, 0, "Molybdenum"));
			self::registerBlock(new Element(BlockIds::ELEMENT_43, 0, "Technetium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_44, 0, "Ruthenium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_45, 0, "Rhodium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_46, 0, "Palladium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_47, 0, "Silver"));
			self::registerBlock(new Element(BlockIds::ELEMENT_48, 0, "Cadmium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_49, 0, "Indium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_50, 0, "Tin"));
			self::registerBlock(new Element(BlockIds::ELEMENT_51, 0, "Antimony"));
			self::registerBlock(new Element(BlockIds::ELEMENT_52, 0, "Tellurium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_53, 0, "Iodine"));
			self::registerBlock(new Element(BlockIds::ELEMENT_54, 0, "Xenon"));
			self::registerBlock(new Element(BlockIds::ELEMENT_55, 0, "Cesium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_56, 0, "Barium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_57, 0, "Lanthanum"));
			self::registerBlock(new Element(BlockIds::ELEMENT_58, 0, "Cerium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_59, 0, "Praseodymium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_60, 0, "Neodymium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_61, 0, "Promethium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_62, 0, "Samarium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_63, 0, "Europium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_64, 0, "Gadolinium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_65, 0, "Terbium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_66, 0, "Dysprosium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_67, 0, "Holmium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_68, 0, "Erbium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_69, 0, "Thulium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_70, 0, "Ytterbium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_71, 0, "Lutetium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_72, 0, "Hafnium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_73, 0, "Tantalum"));
			self::registerBlock(new Element(BlockIds::ELEMENT_74, 0, "Tungsten"));
			self::registerBlock(new Element(BlockIds::ELEMENT_75, 0, "Rhenium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_76, 0, "Osmium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_77, 0, "Iridium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_78, 0, "Platinum"));
			self::registerBlock(new Element(BlockIds::ELEMENT_79, 0, "Gold"));
			self::registerBlock(new Element(BlockIds::ELEMENT_80, 0, "Mercury"));
			self::registerBlock(new Element(BlockIds::ELEMENT_81, 0, "Thallium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_82, 0, "Lead"));
			self::registerBlock(new Element(BlockIds::ELEMENT_83, 0, "Bismuth"));
			self::registerBlock(new Element(BlockIds::ELEMENT_84, 0, "Polonium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_85, 0, "Astatine"));
			self::registerBlock(new Element(BlockIds::ELEMENT_86, 0, "Radon"));
			self::registerBlock(new Element(BlockIds::ELEMENT_87, 0, "Francium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_88, 0, "Radium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_89, 0, "Actinium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_90, 0, "Thorium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_91, 0, "Protactinium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_92, 0, "Uranium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_93, 0, "Neptunium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_94, 0, "Plutonium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_95, 0, "Americium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_96, 0, "Curium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_97, 0, "Berkelium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_98, 0, "Californium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_99, 0, "Einsteinium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_100, 0, "Fermium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_101, 0, "Mendelevium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_102, 0, "Nobelium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_103, 0, "Lawrencium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_104, 0, "Rutherfordium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_105, 0, "Dubnium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_106, 0, "Seaborgium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_107, 0, "Bohrium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_108, 0, "Hassium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_109, 0, "Meitnerium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_110, 0, "Darmstadtium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_111, 0, "Roentgenium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_112, 0, "Copernicium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_113, 0, "Nihonium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_114, 0, "Flerovium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_115, 0, "Moscovium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_116, 0, "Livermorium"));
			self::registerBlock(new Element(BlockIds::ELEMENT_117, 0, "Tennessine"));
			self::registerBlock(new Element(BlockIds::ELEMENT_118, 0, "Oganesson"));
			//TODO: SEAGRASS
			//TODO: CORAL
			//TODO: CORAL_BLOCK
			//TODO: CORAL_FAN
			//TODO: CORAL_FAN_DEAD
			//TODO: CORAL_FAN_HANG
			//TODO: CORAL_FAN_HANG2
			//TODO: CORAL_FAN_HANG3
			//TODO: KELP
			//TODO: DRIED_KELP_BLOCK
			self::registerBlock(new WoodenButton(BlockIds::ACACIA_BUTTON, 0, "Acacia Button"));
			self::registerBlock(new WoodenButton(BlockIds::BIRCH_BUTTON, 0, "Birch Button"));
			self::registerBlock(new WoodenButton(BlockIds::DARK_OAK_BUTTON, 0, "Dark Oak Button"));
			self::registerBlock(new WoodenButton(BlockIds::JUNGLE_BUTTON, 0, "Jungle Button"));
			self::registerBlock(new WoodenButton(BlockIds::SPRUCE_BUTTON, 0, "Spruce Button"));
			self::registerBlock(new WoodenTrapdoor(BlockIds::ACACIA_TRAPDOOR, 0, "Acacia Trapdoor"));
			self::registerBlock(new WoodenTrapdoor(BlockIds::BIRCH_TRAPDOOR, 0, "Birch Trapdoor"));
			self::registerBlock(new WoodenTrapdoor(BlockIds::DARK_OAK_TRAPDOOR, 0, "Dark Oak Trapdoor"));
			self::registerBlock(new WoodenTrapdoor(BlockIds::JUNGLE_TRAPDOOR, 0, "Jungle Trapdoor"));
			self::registerBlock(new WoodenTrapdoor(BlockIds::SPRUCE_TRAPDOOR, 0, "Spruce Trapdoor"));
			self::registerBlock(new WoodenPressurePlate(BlockIds::ACACIA_PRESSURE_PLATE, 0, "Acacia Pressure Plate"));
			self::registerBlock(new WoodenPressurePlate(BlockIds::BIRCH_PRESSURE_PLATE, 0, "Birch Pressure Plate"));
			self::registerBlock(new WoodenPressurePlate(BlockIds::DARK_OAK_PRESSURE_PLATE, 0, "Dark Oak Pressure Plate"));
			self::registerBlock(new WoodenPressurePlate(BlockIds::JUNGLE_PRESSURE_PLATE, 0, "Jungle Pressure Plate"));
			self::registerBlock(new WoodenPressurePlate(BlockIds::SPRUCE_PRESSURE_PLATE, 0, "Spruce Pressure Plate"));
			self::registerBlock(new CarvedPumpkin());
			//TODO: SEA_PICKLE
			//TODO: CONDUIT
			//TODO: TURTLE_EGG
			//TODO: BUBBLE_COLUMN
			self::registerBlock(new Barrier());
			self::registerBlock(new StoneSlab3());
			//TODO: BAMBOO
			//TODO: BAMBOO_SAPLING
			//TODO: SCAFFOLDING
			self::registerBlock(new StoneSlab4());
			self::registerBlock(new DoubleStoneSlab3());
			self::registerBlock(new DoubleStoneSlab4());
			self::registerBlock(new GraniteStairs());
			self::registerBlock(new DioriteStairs());
			self::registerBlock(new AndesiteStairs());
			self::registerBlock(new PolishedGraniteStairs());
			self::registerBlock(new PolishedDioriteStairs());
			self::registerBlock(new PolishedAndesiteStairs());
			self::registerBlock(new MossyStoneBrickStairs());
			self::registerBlock(new SmoothRedSandstoneStairs());
			self::registerBlock(new SmoothSandstoneStairs());
			self::registerBlock(new EndBrickStairs());
			self::registerBlock(new MossyCobblestoneStairs());
			self::registerBlock(new NormalStoneStairs());
			self::registerBlock(new SignPost(BlockIds::SPRUCE_STANDING_SIGN, 0, "Spruce Sign Post", Item::SPRUCE_SIGN, BlockIds::SPRUCE_WALL_SIGN));
			self::registerBlock(new WallSign(BlockIds::SPRUCE_WALL_SIGN, 0, "Spruce Sign Post", Item::SPRUCE_SIGN, BlockIds::SPRUCE_WALL_SIGN));
			self::registerBlock(new SmoothStone());
			self::registerBlock(new RedNetherBrickStairs());
			self::registerBlock(new SmoothQuartzStairs());
			self::registerBlock(new SignPost(BlockIds::BIRCH_STANDING_SIGN, 0, "Birch Sign Post", Item::BIRCH_SIGN, BlockIds::BIRCH_WALL_SIGN));
			self::registerBlock(new WallSign(BlockIds::BIRCH_WALL_SIGN, 0, "Birch Sign Post", Item::BIRCH_SIGN, BlockIds::BIRCH_WALL_SIGN));
			self::registerBlock(new SignPost(BlockIds::JUNGLE_STANDING_SIGN, 0, "Jungle Sign Post", Item::JUNGLE_SIGN, BlockIds::JUNGLE_WALL_SIGN));
			self::registerBlock(new WallSign(BlockIds::JUNGLE_WALL_SIGN, 0, "Jungle Sign Post", Item::JUNGLE_SIGN, BlockIds::JUNGLE_WALL_SIGN));
			self::registerBlock(new SignPost(BlockIds::ACACIA_STANDING_SIGN, 0, "Acacia Sign Post", Item::ACACIA_SIGN, BlockIds::ACACIA_WALL_SIGN));
			self::registerBlock(new WallSign(BlockIds::ACACIA_WALL_SIGN, 0, "Acacia Sign Post", Item::ACACIA_SIGN, BlockIds::ACACIA_WALL_SIGN));
			self::registerBlock(new SignPost(BlockIds::DARKOAK_STANDING_SIGN, 0, "Darkoak Sign Post", Item::DARKOAK_SIGN, BlockIds::DARKOAK_WALL_SIGN));
			self::registerBlock(new WallSign(BlockIds::DARKOAK_WALL_SIGN, 0, "Darkoak Sign Post", Item::DARKOAK_SIGN, BlockIds::DARKOAK_WALL_SIGN));
			self::registerBlock(new Lectern());
			self::registerBlock(new Grindstone());
			self::registerBlock(new BlastFurnace());
			self::registerBlock(new StonecutterBlock());
			self::registerBlock(new Smoker());
			self::registerBlock(new LitSmoker());
			//TODO: CARTOGRAPHY_TABLE
			//TODO: FLETCHING_TABLE
			self::registerBlock(new SmithingTable());
			//TODO: BARREL
			//TODO: LOOM
			//TODO: BELL
			//TODO: SWEET_BERRY_BUSH
			self::registerBlock(new Lantern());
			self::registerBlock(new Campfire());
			//TODO: LAVA_CAULDRON
			//TODO: JIGSAW
			self::registerBlock(new Wood(BlockIds::WOOD, 0, "Wood"));
			//TODO: COMPOSTER
			self::registerBlock(new LitBlastFurnace());
			//TODO: LIGHT_BLOCK
			//TODO: WITHER_ROSE
			//TODO: STICKY_PISTON_ARM_COLLISION
			//TODO: BEE_NEST
			//TODO: BEEHIVE
			//TODO: HONEY_BLOCK
			//TODO: HONEYCOMB_BLOCK
			//TODO: LODESTONE
			//TODO: CRIMSON_ROOTS
			//TODO: WARPED_ROOTS
			self::registerBlock(new FixLog(BlockIds::CRIMSON_STEM, 0, "Crimson Log"));
			self::registerBlock(new FixLog(BlockIds::WARPED_STEM, 0, "Warped Log"));
			//TODO: WARPED_WART_BLOCK
			//TODO: CRIMSON_FUNGUS
			//TODO: WARPED_FUNGUS
			//TODO: SHROOMLIGHT
			//TODO: WEEPING_VINES
			//TODO: CRIMSON_NYLIUM
			//TODO: WARPED_NYLIUM
			//TODO: BASALT
			//TODO: POLISHED_BASALT
			//TODO: SOUL_SOIL
			self::registerBlock(new SoulFire());
			//TODO: NETHER_SPROUTS
			//TODO: TARGET
			self::registerBlock(new StrippedLog(BlockIds::STRIPPED_CRIMSON_STEM, 0, "Stripped Crimson Log"));
			self::registerBlock(new StrippedLog(BlockIds::STRIPPED_WARPED_STEM, 0, "Stripped Warped Log"));
			self::registerBlock(new CrimsonPlanks());
			self::registerBlock(new WarpedPlanks());
			self::registerBlock(new CrimsonDoor());
			self::registerBlock(new WarpedDoor());
			self::registerBlock(new WoodenTrapdoor(BlockIds::CRIMSON_TRAPDOOR, 0, "Crimson Trapdoor"));
			self::registerBlock(new WoodenTrapdoor(BlockIds::WARPED_TRAPDOOR, 0, "Crimson Trapdoor"));
			self::registerBlock(new CrimsonSignPost());
			self::registerBlock(new WarpedSignPost());
			self::registerBlock(new CrimsonWallSign());
			self::registerBlock(new WarpedWallSign());
			self::registerBlock(new CrimsonStairs());
			self::registerBlock(new WarpedStairs());
			self::registerBlock(new CrimsonFence());
			self::registerBlock(new WarpedFence());
			self::registerBlock(new CrimsonFenceGate());
			self::registerBlock(new WarpedFenceGate());
			self::registerBlock(new CrimsonButton());
			self::registerBlock(new WarpedButton());
			self::registerBlock(new CrimsonPressurePlate());
			self::registerBlock(new WarpedPressurePlate());
			self::registerBlock(new CrimsonSlab());
			self::registerBlock(new WarpedSlab());
			self::registerBlock(new CrimsonDoubleSlab());
			self::registerBlock(new WarpedDoubleSlab());
			self::registerBlock(new SoulTorch());
			self::registerBlock(new SoulLantern());
			self::registerBlock(new NetheriteBlock());
			self::registerBlock(new AncientDebris());
			self::registerBlock(new RespawnAnchor());
			self::registerBlock(new Blackstone(BlockIds::BLACKSTONE, 0, "Blackstone"));
			self::registerBlock(new Blackstone(BlockIds::POLISHED_BLACKSTONE_BRICKS, 0, "Polished Blackstone Bricks"));
			self::registerBlock(new BlackstoneStairs(BlockIds::POLISHED_BLACKSTONE_BRICK_STAIRS, 0, "Polished Blackstone Brick Stairs"));
			self::registerBlock(new BlackstoneStairs(BlockIds::BLACKSTONE_STAIRS, 0, "Blackstone Stairs"));
			self::registerBlock(new StoneWall(BlockIds::BLACKSTONE_WALL, 0, "Blackstone Wall"));
			self::registerBlock(new StoneWall(BlockIds::POLISHED_BLACKSTONE_BRICK_WALL, 0, "Polished Blackstone Brick Wall"));
			self::registerBlock(new Blackstone(BlockIds::CHISELED_POLISHED_BLACKSTONE, 0, "Chiseled Polished Blackstone"));
			self::registerBlock(new Blackstone(BlockIds::CRACKED_POLISHED_BLACKSTONE_BRICKS, 0, "Cracked Chiseled Polished Blackstone"));
			self::registerBlock(new GildedBlackstone());
			self::registerBlock(new BlackstoneSlab(BlockIds::BLACKSTONE_SLAB, 0, "Blackstone Slab", BlockIds::BLACKSTONE_DOUBLE_SLAB));
			self::registerBlock(new BlackstoneDoubleSlab(BlockIds::BLACKSTONE_DOUBLE_SLAB, 0, BlockIds::BLACKSTONE_SLAB));
			self::registerBlock(new BlackstoneSlab(BlockIds::POLISHED_BLACKSTONE_BRICK_SLAB, 0, "Polished Blackstone Brick Slab", BlockIds::POLISHED_BLACKSTONE_BRICK_DOUBLE_SLAB));
			self::registerBlock(new BlackstoneDoubleSlab(BlockIds::POLISHED_BLACKSTONE_BRICK_DOUBLE_SLAB, 0, BlockIds::POLISHED_BLACKSTONE_BRICK_SLAB));
			//TODO: CHAIN
			//TODO: TWISTING_VINES
			self::registerBlock(new NetherGoldOre());
			self::registerBlock(new CryingObsidian());
			self::registerBlock(new SoulCampfire());
			self::registerBlock(new Blackstone(BlockIds::POLISHED_BLACKSTONE, 0, "Polished Blackstone"));
			self::registerBlock(new BlackstoneStairs(BlockIds::POLISHED_BLACKSTONE_STAIRS, 0, "Polished Blackstone Stairs"));
			self::registerBlock(new BlackstoneSlab(BlockIds::POLISHED_BLACKSTONE_SLAB, 0, "Polished Blackstone Slab", BlockIds::POLISHED_BLACKSTONE_DOUBLE_SLAB));
			self::registerBlock(new BlackstoneDoubleSlab(BlockIds::POLISHED_BLACKSTONE_DOUBLE_SLAB, 0, BlockIds::POLISHED_BLACKSTONE_SLAB));
			self::registerBlock(new PolishedBlackstonePressurePlate());
			self::registerBlock(new PolishedBlackstoneButton());
			self::registerBlock(new StoneWall(BlockIds::POLISHED_BLACKSTONE_WALL, 0, "Polished Blackstone Wall"));
			self::registerBlock(new FixWood(BlockIds::WARPED_HYPHAE, 0, "Warped Hyphae"));
			self::registerBlock(new FixWood(BlockIds::CRIMSON_HYPHAE, 0, "Crimson Hyphae"));
			self::registerBlock(new StrippedWood(BlockIds::STRIPPED_WARPED_HYPHAE, 0, "Stripped Warped Hyphae"));
			self::registerBlock(new StrippedWood(BlockIds::STRIPPED_CRIMSON_HYPHAE, 0, "Stripped Crimson Hyphae"));
			self::registerBlock(new ChiseledNetherBricks());
			self::registerBlock(new CrackedNetherBricks());
			self::registerBlock(new QuartzBricks());
			//TODO: UNKNOWN
			self::registerBlock(new PowderSnow());
			self::registerBlock(new SculkSensor());
			//TODO: POINTED_DRIPSTONE
			self::registerBlock(new CopperOre());
			self::registerBlock(new LightningRod());
			self::registerBlock(new Dripstone());
			self::registerBlock(new DirtWithRoots());
			//TODO: HANGING_ROOTS
			self::registerBlock(new Moss());
			self::registerBlock(new SporeBlossom());
			//TODO: CAVE_VINES
			//TODO: BIG_DRIPLEAF
			self::registerBlock(new AzaleaLeaves());
			self::registerBlock(new AzaleaLeavesFlowered());
			self::registerBlock(new Calcite());
			self::registerBlock(new Amethyst());
			self::registerBlock(new BuddingAmethyst());
			self::registerBlock(new AmethystCluster());
			self::registerBlock(new AmethystBudLarge());
			self::registerBlock(new AmethystBudMedium());
			self::registerBlock(new AmethystBudSmall());
			self::registerBlock(new Tuff());
			self::registerBlock(new TintedGlass());
			self::registerBlock(new MossCarpet());
			//TODO: SMALL_DRIPLEAF_BLOCK
			self::registerBlock(new Azalea());
			self::registerBlock(new FloweringAzalea());
			self::registerBlock(new GlowFrame());
			self::registerBlock(new CopperBlock(BlockIds::COPPER_BLOCK, 0, "Copper"));
			self::registerBlock(new CopperBlock(BlockIds::EXPOSED_COPPER, 0, "Exposed Copper"));
			self::registerBlock(new CopperBlock(BlockIds::WEATHERED_COPPER, 0, "Weathered Copper"));
			self::registerBlock(new CopperBlock(BlockIds::OXIDIZED_COPPER, 0, "Oxidized Copper"));
			self::registerBlock(new CopperBlock(BlockIds::WAXED_COPPER, 0, "Waxed Copper"));
			self::registerBlock(new CopperBlock(BlockIds::WAXED_EXPOSED_COPPER, 0, "Waxed Exposed Copper"));
			self::registerBlock(new CopperBlock(BlockIds::WAXED_WEATHERED_COPPER, 0, "Waxed Weathered Copper"));
			self::registerBlock(new CutCopper(BlockIds::CUT_COPPER, 0, "Cut Copper"));
			self::registerBlock(new CutCopper(BlockIds::EXPOSED_CUT_COPPER, 0, "Exposed Cut Copper"));
			self::registerBlock(new CutCopper(BlockIds::WEATHERED_CUT_COPPER, 0, "Weathered Cut Copper"));
			self::registerBlock(new CutCopper(BlockIds::OXIDIZED_CUT_COPPER, 0, "Oxidized Cut Copper"));
			self::registerBlock(new CutCopper(BlockIds::WAXED_CUT_COPPER, 0, "Waxed Copper"));
			self::registerBlock(new CutCopper(BlockIds::WAXED_EXPOSED_CUT_COPPER, 0, "Waxed Exposed Cut Copper"));
			self::registerBlock(new CutCopper(BlockIds::WAXED_WEATHERED_CUT_COPPER, 0, "Waxed Weathered Cut Copper"));
			self::registerBlock(new CutCopperStairs(BlockIds::CUT_COPPER_STAIRS, 0, "Cut Copper Stairs"));
			self::registerBlock(new CutCopperStairs(BlockIds::EXPOSED_CUT_COPPER_STAIRS, 0, "Exposed Cut Copper Stairs"));
			self::registerBlock(new CutCopperStairs(BlockIds::WEATHERED_CUT_COPPER_STAIRS, 0, "Weathered Cut Copper Stairs"));
			self::registerBlock(new CutCopperStairs(BlockIds::OXIDIZED_CUT_COPPER_STAIRS, 0, "Oxidized Cut Copper Stairs"));
			self::registerBlock(new CutCopperStairs(BlockIds::WAXED_CUT_COPPER_STAIRS, 0, "Waxed Copper Stairs"));
			self::registerBlock(new CutCopperStairs(BlockIds::WAXED_EXPOSED_CUT_COPPER_STAIRS, 0, "Waxed Exposed Cut Copper Stairs"));
			self::registerBlock(new CutCopperStairs(BlockIds::WAXED_WEATHERED_CUT_COPPER_STAIRS, 0, "Waxed Weathered Cut Copper Stairs"));
			self::registerBlock(new CutCopperSlab(BlockIds::CUT_COPPER_SLAB, 0, "Cut Copper Slab", BlockIds::DOUBLE_CUT_COPPER_SLAB));
			self::registerBlock(new CutCopperSlab(BlockIds::EXPOSED_CUT_COPPER_SLAB, 0, "Exposed Cut Copper Slab", BlockIds::EXPOSED_DOUBLE_CUT_COPPER_SLAB));
			self::registerBlock(new CutCopperSlab(BlockIds::WEATHERED_CUT_COPPER_SLAB, 0, "Weathered Cut Copper Slab", BlockIds::WEATHERED_DOUBLE_CUT_COPPER_SLAB));
			self::registerBlock(new CutCopperSlab(BlockIds::OXIDIZED_CUT_COPPER_SLAB, 0, "Oxidized Cut Copper Slab", BlockIds::OXIDIZED_DOUBLE_CUT_COPPER_SLAB));
			self::registerBlock(new CutCopperSlab(BlockIds::WAXED_CUT_COPPER_SLAB, 0, "Waxed Cut Copper Slab", BlockIds::WAXED_DOUBLE_CUT_COPPER_SLAB));
			self::registerBlock(new CutCopperSlab(BlockIds::WAXED_EXPOSED_CUT_COPPER_SLAB, 0, "Waxed Exposed Cut Copper Slab", BlockIds::WAXED_EXPOSED_DOUBLE_CUT_COPPER_SLAB));
			self::registerBlock(new CutCopperSlab(BlockIds::WAXED_WEATHERED_CUT_COPPER_SLAB, 0, "Waxed Weathered Cut Copper Slab", BlockIds::WAXED_WEATHERED_DOUBLE_CUT_COPPER_SLAB));
			self::registerBlock(new CutCopperDoubleSlab(BlockIds::DOUBLE_CUT_COPPER_SLAB, 0, BlockIds::CUT_COPPER_SLAB));
			self::registerBlock(new CutCopperDoubleSlab(BlockIds::EXPOSED_DOUBLE_CUT_COPPER_SLAB, 0, BlockIds::EXPOSED_CUT_COPPER_SLAB));
			self::registerBlock(new CutCopperDoubleSlab(BlockIds::WEATHERED_DOUBLE_CUT_COPPER_SLAB, 0, BlockIds::WEATHERED_CUT_COPPER_SLAB));
			self::registerBlock(new CutCopperDoubleSlab(BlockIds::OXIDIZED_DOUBLE_CUT_COPPER_SLAB, 0, BlockIds::OXIDIZED_CUT_COPPER_SLAB));
			self::registerBlock(new CutCopperDoubleSlab(BlockIds::WAXED_DOUBLE_CUT_COPPER_SLAB, 0, BlockIds::WAXED_CUT_COPPER_SLAB));
			self::registerBlock(new CutCopperDoubleSlab(BlockIds::WAXED_EXPOSED_DOUBLE_CUT_COPPER_SLAB, 0, BlockIds::WAXED_EXPOSED_CUT_COPPER_SLAB));
			self::registerBlock(new CutCopperDoubleSlab(BlockIds::WAXED_WEATHERED_DOUBLE_CUT_COPPER_SLAB, 0, BlockIds::WAXED_WEATHERED_CUT_COPPER_SLAB));
			//TODO: CAVE_VINES_BODY_WITH_BERRIES
			//TODO: CAVE_VINES_HEAD_WITH_BERRIES
			self::registerBlock(new SmoothBasalt());
			self::registerBlock(new Deepslate());
			self::registerBlock(new CobbledDeepslate());
			self::registerBlock(new DeepslateSlab(BlockIds::COBBLED_DEEPSLATE_SLAB, 0, "Cobbled Deepslate Slab", BlockIds::COBBLED_DEEPSLATE_DOUBLE_SLAB));
			self::registerBlock(new DeepslateStairs(BlockIds::COBBLED_DEEPSLATE_STAIRS, 0, "Cobbled Deepslate Stairs"));
			self::registerBlock(new DeepslateWall(BlockIds::COBBLED_DEEPSLATE_WALL, 0, "Cobbled Deepslate Wall"));
			self::registerBlock(new PolishedDeepslate());
			self::registerBlock(new DeepslateSlab(BlockIds::POLISHED_DEEPSLATE_SLAB, 0, "Polished Deepslate Slab", BlockIds::POLISHED_DEEPSLATE_DOUBLE_SLAB));
			self::registerBlock(new DeepslateStairs(BlockIds::POLISHED_DEEPSLATE_STAIRS, 0, "Polished Deepslate Stairs"));
			self::registerBlock(new DeepslateWall(BlockIds::POLISHED_DEEPSLATE_WALL, 0, "Polished Deepslate Wall"));
			self::registerBlock(new DeepslateTiles(BlockIds::DEEPSLATE_TILES, 0, "Deepslate Tiles"));
			self::registerBlock(new DeepslateSlab(BlockIds::DEEPSLATE_TILE_SLAB, 0, "Deepslate Tile Slab", BlockIds::DEEPSLATE_TILE_DOUBLE_SLAB));
			self::registerBlock(new DeepslateStairs(BlockIds::DEEPSLATE_TILE_STAIRS, 0, "Deepslate Tile Stairs"));
			self::registerBlock(new DeepslateWall(BlockIds::DEEPSLATE_TILE_WALL, 0, "Deepslate Tile Wall"));
			self::registerBlock(new DeepslateBricks(BlockIds::DEEPSLATE_BRICKS, 0, "Deepslate Bricks"));
			self::registerBlock(new DeepslateSlab(BlockIds::DEEPSLATE_BRICK_SLAB, 0, "Deepslate Brick Slab", BlockIds::DEEPSLATE_BRICK_DOUBLE_SLAB));
			self::registerBlock(new DeepslateStairs(BlockIds::DEEPSLATE_BRICK_STAIRS, 0, "Deepslate Brick Stairs"));
			self::registerBlock(new DeepslateWall(BlockIds::DEEPSLATE_BRICK_WALL, 0, "Deepslate Brick Wall"));
			self::registerBlock(new ChiseledDeepslate());
			self::registerBlock(new DeepslateDoubleSlab(BlockIds::COBBLED_DEEPSLATE_DOUBLE_SLAB, 0, BlockIds::COBBLED_DEEPSLATE_SLAB));
			self::registerBlock(new DeepslateDoubleSlab(BlockIds::POLISHED_DEEPSLATE_DOUBLE_SLAB, 0, BlockIds::POLISHED_DEEPSLATE_SLAB));
			self::registerBlock(new DeepslateDoubleSlab(BlockIds::DEEPSLATE_TILE_DOUBLE_SLAB, 0, BlockIds::DEEPSLATE_TILE_SLAB));
			self::registerBlock(new DeepslateDoubleSlab(BlockIds::DEEPSLATE_BRICK_DOUBLE_SLAB, 0, BlockIds::DEEPSLATE_BRICK_SLAB));
			self::registerBlock(new DeepslateLapisOre());
			self::registerBlock(new DeepslateIronOre());
			self::registerBlock(new DeepslateGoldOre());
			self::registerBlock(new DeepslateRedstoneOre());
			self::registerBlock(new LitDeepslateRedstoneOre());
			self::registerBlock(new DeepslateDiamondOre());
			self::registerBlock(new DeepslateCoalOre());
			self::registerBlock(new DeepslateEmeraldOre());
			self::registerBlock(new DeepslateCopperOre());
			self::registerBlock(new DeepslateTiles(BlockIds::CRACKED_DEEPSLATE_TILES, 0, "Cracked Deepslate Tiles"));
			self::registerBlock(new DeepslateBricks(BlockIds::CRACKED_DEEPSLATE_BRICKS, 0, "Cracked Deepslate Bricks"));
			//TODO: GLOW_LICHEN
			self::registerBlock(new Candle(BlockIds::CANDLE, 0, "Candle", null, BlockIds::CANDLE_CAKE));
			self::registerBlock(new Candle(BlockIds::WHITE_CANDLE, 0, "White Candle", null, BlockIds::WHITE_CANDLE_CAKE));
			self::registerBlock(new Candle(BlockIds::ORANGE_CANDLE, 0, "Orange Candle", null, BlockIds::ORANGE_CANDLE_CAKE));
			self::registerBlock(new Candle(BlockIds::MAGENTA_CANDLE, 0, "Magenta Candle", null, BlockIds::MAGENTA_CANDLE_CAKE));
			self::registerBlock(new Candle(BlockIds::LIGHT_BLUE_CANDLE, 0, "Light Blue Candle", null, BlockIds::LIGHT_BLUE_CANDLE_CAKE));
			self::registerBlock(new Candle(BlockIds::YELLOW_CANDLE, 0, "Yellow Candle", null, BlockIds::YELLOW_CANDLE_CAKE));
			self::registerBlock(new Candle(BlockIds::LIME_CANDLE, 0, "Lime Candle", null, BlockIds::LIME_CANDLE_CAKE));
			self::registerBlock(new Candle(BlockIds::PINK_CANDLE, 0, "Pink Candle", null, BlockIds::PINK_CANDLE_CAKE));
			self::registerBlock(new Candle(BlockIds::GRAY_CANDLE, 0, "Gray Candle", null, BlockIds::GRAY_CANDLE_CAKE));
			self::registerBlock(new Candle(BlockIds::LIGHT_GRAY_CANDLE, 0, "Light Gray Candle", null, BlockIds::LIGHT_GRAY_CANDLE_CAKE));
			self::registerBlock(new Candle(BlockIds::CYAN_CANDLE, 0, "Cyan Candle", null, BlockIds::CYAN_CANDLE_CAKE));
			self::registerBlock(new Candle(BlockIds::PURPLE_CANDLE, 0, "Purple Candle", null, BlockIds::PURPLE_CANDLE_CAKE));
			self::registerBlock(new Candle(BlockIds::BLUE_CANDLE, 0, "Blue Candle", null, BlockIds::BLUE_CANDLE_CAKE));
			self::registerBlock(new Candle(BlockIds::BROWN_CANDLE, 0, "Brown Candle", null, BlockIds::BROWN_CANDLE_CAKE));
			self::registerBlock(new Candle(BlockIds::GREEN_CANDLE, 0, "Green Candle", null, BlockIds::GREEN_CANDLE_CAKE));
			self::registerBlock(new Candle(BlockIds::RED_CANDLE, 0, "Red Candle", null, BlockIds::RED_CANDLE_CAKE));
			self::registerBlock(new Candle(BlockIds::BLACK_CANDLE, 0, "Black Candle", null, BlockIds::BLACK_CANDLE_CAKE));
			self::registerBlock(new CandleCake(BlockIds::CANDLE_CAKE, 0, "Candle Cake", null, BlockIds::CANDLE));
			self::registerBlock(new CandleCake(BlockIds::WHITE_CANDLE_CAKE, 0, "White Candle Cake", null, BlockIds::WHITE_CANDLE));
			self::registerBlock(new CandleCake(BlockIds::ORANGE_CANDLE_CAKE, 0, "Orange Candle Cake", null, BlockIds::ORANGE_CANDLE));
			self::registerBlock(new CandleCake(BlockIds::MAGENTA_CANDLE_CAKE, 0, "Magenta Candle Cake", null, BlockIds::MAGENTA_CANDLE));
			self::registerBlock(new CandleCake(BlockIds::LIGHT_BLUE_CANDLE_CAKE, 0, "Light Blue Candle Cake", null, BlockIds::LIGHT_BLUE_CANDLE));
			self::registerBlock(new CandleCake(BlockIds::YELLOW_CANDLE_CAKE, 0, "Yellow Candle Cake", null, BlockIds::YELLOW_CANDLE));
			self::registerBlock(new CandleCake(BlockIds::LIME_CANDLE_CAKE, 0, "Lime Candle Cake", null, BlockIds::LIME_CANDLE));
			self::registerBlock(new CandleCake(BlockIds::PINK_CANDLE_CAKE, 0, "Pink Candle Cake", null, BlockIds::PINK_CANDLE));
			self::registerBlock(new CandleCake(BlockIds::GRAY_CANDLE_CAKE, 0, "Gray Candle Cake", null, BlockIds::GRAY_CANDLE));
			self::registerBlock(new CandleCake(BlockIds::LIGHT_GRAY_CANDLE_CAKE, 0, "Light Gray Candle Cake", null, BlockIds::LIGHT_GRAY_CANDLE));
			self::registerBlock(new CandleCake(BlockIds::CYAN_CANDLE_CAKE, 0, "Cyan Candle Cake", null, BlockIds::CYAN_CANDLE));
			self::registerBlock(new CandleCake(BlockIds::PURPLE_CANDLE_CAKE, 0, "Purple Candle Cake", null, BlockIds::PURPLE_CANDLE));
			self::registerBlock(new CandleCake(BlockIds::BLUE_CANDLE_CAKE, 0, "Blue Candle Cake", null, BlockIds::BLUE_CANDLE));
			self::registerBlock(new CandleCake(BlockIds::BROWN_CANDLE_CAKE, 0, "Brown Candle Cake", null, BlockIds::BROWN_CANDLE));
			self::registerBlock(new CandleCake(BlockIds::GREEN_CANDLE_CAKE, 0, "Green Candle Cake", null, BlockIds::GREEN_CANDLE));
			self::registerBlock(new CandleCake(BlockIds::RED_CANDLE_CAKE, 0, "Red Candle Cake", null, BlockIds::RED_CANDLE));
			self::registerBlock(new CandleCake(BlockIds::BLACK_CANDLE_CAKE, 0, "Black Candle Cake", null, BlockIds::BLACK_CANDLE));
			self::registerBlock(new CopperBlock(BlockIds::WAXED_OXIDIZED_COPPER, 0, "Waxed Oxidized Copper Door"));
			self::registerBlock(new CutCopper(BlockIds::WAXED_OXIDIZED_CUT_COPPER, 0, "Waxed Oxidized Cut Copper"));
			self::registerBlock(new CutCopperStairs(BlockIds::WAXED_OXIDIZED_CUT_COPPER_STAIRS, 0, "Waxed Oxidized Cut Copper Stairs"));
			self::registerBlock(new CutCopperSlab(BlockIds::WAXED_OXIDIZED_CUT_COPPER_SLAB, 0, "Waxed Oxidized Cut Copper Slab", BlockIds::WAXED_OXIDIZED_DOUBLE_CUT_COPPER_SLAB));
			self::registerBlock(new CutCopperDoubleSlab(BlockIds::WAXED_OXIDIZED_DOUBLE_CUT_COPPER_SLAB, 0, BlockIds::WAXED_OXIDIZED_CUT_COPPER_SLAB));
			self::registerBlock(new RawIron()); //end
			self::registerBlock(new RawCopper());
			self::registerBlock(new RawGold());
			//TODO: INFESTED_DEEPSLATE
			self::registerBlock(new BambooDoor());
			self::registerBlock(new Sculk());
			//TODO: SCULK_VEIN
			//TODO: SCULK_CATALYST
			//TODO: SCULK_SHRIEKER

			//TODO: CLIENT_REQUEST_PLACEHOLDER_BLOCK

			//TODO: FROG_SPAWN
			//TODO: PEARLESCENT_FROGLIGHT
			//TODO: VERDANT_FROGLIGHT
			//TODO: OCHRE_FROGLIGHT
			self::registerBlock(new MangroveLeaves());
			//TODO: MANGROVE_PROPAGULE

			self::registerBlock(new Mud());
			self::registerBlock(new MudBrickDoubleSlab());
			self::registerBlock(new MudBrickSlab());
			self::registerBlock(new MudBrickStairs());
			self::registerBlock(new MudBrickWall(BlockIds::MUD_BRICK_WALL, 0, "Mud Brick Log"));
			self::registerBlock(new MudBricks());
			self::registerBlock(new PackedMud());
			self::registerBlock(new ReinforcedDeepslate());
			self::registerBlock(new MangroveDoor());
			self::registerBlock(new MangroveButton());
			self::registerBlock(new MangroveDoubleSlab());
			self::registerBlock(new MangroveFence());
			self::registerBlock(new MangroveFenceGate());
			self::registerBlock(new FixLog(BlockIds::MANGROVE_LOG, 0, "Mangrove Log"));
			self::registerBlock(new MangrovePlanks());
			self::registerBlock(new MangrovePressurePlate());
			//TODO: MANGROVE_ROOTS
			self::registerBlock(new MangroveSlab());
			self::registerBlock(new MangroveStairs());
			self::registerBlock(new MangroveSignPost());
			self::registerBlock(new WoodenTrapdoor(BlockIds::MANGROVE_TRAPDOOR, 0, "Mangrove Trapdoor"));
			self::registerBlock(new MangroveWallSign());
			self::registerBlock(new FixWood(BlockIds::MANGROVE_WOOD, 0, "Mangrove Wood"));
			//TODO: MUDDY_MANGROVE_ROOTS
			self::registerBlock(new StrippedLog(BlockIds::STRIPPED_MANGROVE_LOG, 0, "Stripped Mangrove Log"));
			self::registerBlock(new StrippedWood(BlockIds::STRIPPED_MANGROVE_WOOD, 0, "Stripped Mangrove Wood"));
			self::registerBlock(new BambooButton());
			self::registerBlock(new BambooDoubleSlab());
			self::registerBlock(new BambooFence());
			self::registerBlock(new BambooFenceGate());
			//TODO: BAMBOO_HANGING_SIGN
			self::registerBlock(new BambooMosaic());
			self::registerBlock(new BambooMosaicDoubleSlab());
			self::registerBlock(new BambooMosaicSlab());
			self::registerBlock(new BambooMosaicStairs());
			self::registerBlock(new BambooPlanks());
			self::registerBlock(new BambooPressurePlate());
			self::registerBlock(new BambooSlab());
			self::registerBlock(new BambooStairs());
			self::registerBlock(new BambooSignPost());
			self::registerBlock(new BambooWallSign());
			self::registerBlock(new WoodenTrapdoor(BlockIds::BAMBOO_TRAPDOOR, 0, "Bamboo Trapdoor"));
			//TODO: BIRCH_HANGING_SIGN
			//TODO: CHISELED_BOOKSHELF
			//TODO: CRIMSON_HANGING_SIGN
			//TODO: DARK_OAK_HANGING_SIGN
			//TODO: JUNGLE_HANGING_SIGN
			//TODO: MANGROVE_HANGING_SIGN
			//TODO: OAK_HANGING_SIGN

			//TODO: WARPED_HANGING_SIGN
			//TODO: SPRUCE_HANGING_SIGN
			self::registerBlock(new FixLog(BlockIds::BAMBOO_BLOCK, 0, "Bamboo Log"));
			self::registerBlock(new StrippedLog(BlockIds::STRIPPED_BAMBOO_BLOCK, 0, "Stripped Bamboo Log"));
			self::registerBlock(new DecoratedPot());
			self::registerBlock(new SuspiciousSand());
			self::registerBlock(new TorchFlower());
			//TODO: TORCHFLOWER_CROP
			//TODO: CALIBRATED_SCULK_SENSOR
			self::registerBlock(new CherryButton());
			self::registerBlock(new CherryDoubleSlab());
			self::registerBlock(new CherryFence());
			self::registerBlock(new CherryFenceGate());
			//TODO: CHERRY_HANGING_SIGN
			self::registerBlock(new CherryLeaves());
			self::registerBlock(new FixLog(BlockIds::CHERRY_LOG, 0, "Cherry Log"));
			self::registerBlock(new CherryPlanks());
			self::registerBlock(new CherryPressurePlate());
			self::registerBlock(new CherrySapling());
			self::registerBlock(new CherrySlab());
			self::registerBlock(new CherryStairs());
			self::registerBlock(new CherrySignPost());
			self::registerBlock(new WoodenTrapdoor(BlockIds::CHERRY_TRAPDOOR, 0, "Cherry Trapdoor"));
			self::registerBlock(new CherryWallSign());
			self::registerBlock(new FixWood(BlockIds::CHERRY_WOOD, 0, "Cherry Wood"));
			//TODO: PINK_PETALS
			self::registerBlock(new StrippedWood(BlockIds::STRIPPED_CHERRY_WOOD, 0, "Stripped Cherry Wood"));
			self::registerBlock(new StrippedLog(BlockIds::STRIPPED_CHERRY_LOG, 0, "Stripped Cherry Log"));
			self::registerBlock(new SuspiciousGravel());
			//TODO: PITCHER_CROP
			//TODO: PITCHER_PLANT
			self::registerBlock(new SnifferEgg());
			self::registerBlock(new ChiseledCopper(BlockIds::CHISELED_COPPER, 0, "Chiseled Copper"));
			self::registerBlock(new ChiseledTuff());
			self::registerBlock(new ChiseledTuffBricks());
			//TODO: COPPER_BULB
			self::registerBlock(new CopperGrate(BlockIds::COPPER_GRATE, 0, "Copper Grate"));
			self::registerBlock(new CopperTrapdoor(BlockIds::COPPER_TRAPDOOR, 0, "Copper Trapdoor"));
			//TODO: CRAFTER
			self::registerBlock(new ChiseledCopper(BlockIds::EXPOSED_CHISELED_COPPER, 0, "Exposed Chiseled Copper"));
			//TODO: EXPOSED_COPPER_BULB
			self::registerBlock(new CopperGrate(BlockIds::EXPOSED_COPPER_GRATE, 0, "Exposed Copper Grate"));
			self::registerBlock(new CopperTrapdoor(BlockIds::EXPOSED_COPPER_TRAPDOOR, 0, "Exposed Copper Trapdoor"));
			self::registerBlock(new ChiseledCopper(BlockIds::OXIDIZED_CHISELED_COPPER, 0, "Oxidized Chiseled Copper"));
			//TODO: OXIDIZED_COPPER_BULB
			self::registerBlock(new CopperGrate(BlockIds::OXIDIZED_COPPER_GRATE, 0, "Oxidized Copper Grate"));
			self::registerBlock(new CopperTrapdoor(BlockIds::OXIDIZED_COPPER_TRAPDOOR, 0, "Oxidized Copper Trapdoor"));
			self::registerBlock(new PolishedTuff());
			self::registerBlock(new TuffDoubleSlab(BlockIds::POLISHED_TUFF_DOUBLE_SLAB, 0, BlockIds::POLISHED_TUFF_SLAB));
			self::registerBlock(new TuffSlab(BlockIds::POLISHED_TUFF_SLAB, 0, "Polished Tuff Slab", BlockIds::POLISHED_TUFF_DOUBLE_SLAB));
			self::registerBlock(new TuffStairs(BlockIds::POLISHED_TUFF_STAIRS, 0, "Polished Tuff Stairs"));
			self::registerBlock(new StoneWall(BlockIds::POLISHED_TUFF_WALL, 0, "Polished Tuff Wall"));
			self::registerBlock(new TuffDoubleSlab(BlockIds::TUFF_BRICK_DOUBLE_SLAB, 0, BlockIds::TUFF_BRICK_SLAB));
			self::registerBlock(new TuffSlab(BlockIds::TUFF_BRICK_SLAB, 0, "Tuff Brick Slab", BlockIds::TUFF_BRICK_DOUBLE_SLAB));
			self::registerBlock(new TuffStairs(BlockIds::TUFF_BRICK_STAIRS, 0, "Tuff Brick Stairs"));
			self::registerBlock(new StoneWall(BlockIds::TUFF_BRICK_WALL, 0, "Tuff Brick Wall"));
			self::registerBlock(new TuffBricks());
			self::registerBlock(new TuffDoubleSlab(BlockIds::TUFF_DOUBLE_SLAB, 0, BlockIds::TUFF_SLAB));
			self::registerBlock(new TuffSlab(BlockIds::TUFF_SLAB, 0, "Tuff Slab", BlockIds::TUFF_DOUBLE_SLAB));
			self::registerBlock(new TuffStairs(BlockIds::TUFF_STAIRS, 0, "Tuff Stairs"));
			self::registerBlock(new StoneWall(BlockIds::TUFF_WALL, 0, "Tuff Wall"));
			self::registerBlock(new ChiseledCopper(BlockIds::WAXED_CHISELED_COPPER, 0, "Waxed Chiseled Copper"));
			//TODO: WAXED_COPPER_BULB
			self::registerBlock(new CopperGrate(BlockIds::WAXED_COPPER_GRATE, 0, "Waxed Copper Grate"));
			self::registerBlock(new CopperTrapdoor(BlockIds::WAXED_COPPER_TRAPDOOR, 0, "Waxed Copper Trapdoor"));
			self::registerBlock(new ChiseledCopper(BlockIds::WAXED_EXPOSED_CHISELED_COPPER, 0, "Waxed Exposed Chiseled Copper"));
			//TODO: WAXED_EXPOSED_COPPER_BULB
			self::registerBlock(new CopperGrate(BlockIds::WAXED_EXPOSED_COPPER_GRATE, 0, "Waxed Exposed Copper Grate"));
			self::registerBlock(new CopperTrapdoor(BlockIds::WAXED_EXPOSED_COPPER_TRAPDOOR, 0, "Waxed Exposed Copper Trapdoor"));
			self::registerBlock(new ChiseledCopper(BlockIds::WAXED_OXIDIZED_CHISELED_COPPER, 0, "Waxed Oxidized Chiseled Copper"));
			//TODO: WAXED_OXIDIZED_COPPER_BULB
			self::registerBlock(new CopperGrate(BlockIds::WAXED_OXIDIZED_COPPER_GRATE, 0, "Waxed Oxidized Copper Grate"));
			self::registerBlock(new CopperTrapdoor(BlockIds::WAXED_OXIDIZED_COPPER_TRAPDOOR, 0, "Waxed Oxidized Copper Trapdoor"));
			self::registerBlock(new ChiseledCopper(BlockIds::WAXED_WEATHERED_CHISELED_COPPER, 0, "Waxed Weathered Chiseled Copper"));
			//TODO: WAXED_WEATHERED_COPPER_BULB
			self::registerBlock(new CopperGrate(BlockIds::WAXED_WEATHERED_COPPER_GRATE, 0, "Waxed Weathered Copper Grate"));
			self::registerBlock(new CopperTrapdoor(BlockIds::WAXED_WEATHERED_COPPER_TRAPDOOR, 0, "Waxed Weathered Copper Trapdoor"));
			self::registerBlock(new ChiseledCopper(BlockIds::WEATHERED_CHISELED_COPPER, 0, "Weathered Chiseled Copper"));
			//TODO: WEATHERED_COPPER_BULB
			self::registerBlock(new CopperGrate(BlockIds::WEATHERED_COPPER_GRATE, 0, "Weathered Copper Grate"));
			self::registerBlock(new CopperTrapdoor(BlockIds::WEATHERED_COPPER_TRAPDOOR, 0, "Weathered Copper Trapdoor"));
			self::registerBlock(new CopperDoor(BlockIds::WEATHERED_COPPER_DOOR, 0, "Weathered Copper Door"));
			self::registerBlock(new CopperDoor(BlockIds::WAXED_WEATHERED_COPPER_DOOR, 0, "Waxed Weathered Copper Door"));
			self::registerBlock(new CopperDoor(BlockIds::WAXED_OXIDIZED_COPPER_DOOR, 0, "Waxed Oxidized Copper Door"));
			self::registerBlock(new CopperDoor(BlockIds::WAXED_EXPOSED_COPPER_DOOR, 0, "Waxed Exposed Copper Door"));
			self::registerBlock(new CopperDoor(BlockIds::WAXED_COPPER_DOOR, 0, "Waxed Copper Door"));
			self::registerBlock(new CopperDoor(BlockIds::OXIDIZED_COPPER_DOOR, 0, "Oxidized Copper Door"));
			self::registerBlock(new CopperDoor(BlockIds::EXPOSED_COPPER_DOOR, 0, "Exposed Copper Door"));
			self::registerBlock(new CopperDoor(BlockIds::COPPER_DOOR, 0, "Copper Door"));
			self::registerBlock(new CherryDoor());
			//TODO: RESIN_CLUMP
			//TODO: ACACIA_HANGING_SIGN
			self::registerBlock(new BrickWall(BlockIds::MOSSY_COBBLESTONE_WALL, 0, "Mossy Cobblestone Wall"));
			self::registerBlock(new StoneWall(BlockIds::GRANITE_WALL, 0, "Granite Wall"));
			self::registerBlock(new StoneWall(BlockIds::DIORITE_WALL, 0, "Diorite Wall"));
			self::registerBlock(new StoneWall(BlockIds::ANDESITE_WALL, 0, "Andesite Wall"));
			self::registerBlock(new SandstoneWall(BlockIds::SANDSTONE_WALL, 0, "Sandstone Wall"));
			self::registerBlock(new BrickWall(BlockIds::BRICK_WALL, 0, "Brick Wall"));
			self::registerBlock(new StoneWall(BlockIds::STONE_BRICK_WALL, 0, "Stone Brick Wall"));
			self::registerBlock(new StoneWall(BlockIds::MOSSY_STONE_BRICK_WALL, 0, "Mossy Stone Brick Wall"));
			self::registerBlock(new BrickWall(BlockIds::NETHER_BRICK_WALL, 0, "Nether Brick Wall"));
			self::registerBlock(new EndStoneBrickWall(BlockIds::END_STONE_BRICK_WALL, 0, "End Stone Brick Wall"));
			self::registerBlock(new StoneWall(BlockIds::PRISMARINE_WALL, 0, "Prismarine Wall"));
			self::registerBlock(new SandstoneWall(BlockIds::RED_SANDSTONE_WALL, 0, "Red Sandstone Wall"));
			self::registerBlock(new BrickWall(BlockIds::RED_NETHER_BRICK_WALL, 0, "Red Nether Brick Wall"));

			//TODO: TRIAL_SPAWNER
			//TODO: VAULT
			self::registerBlock(new HeavyCore());
			//TODO: DEPRECATED_ANVIL
			//TODO: MUSHROOM_STEM

			self::registerBlock(new DriedGhast());
			self::registerBlock(new ChiseledResinBricks());
			self::registerBlock(new ClosedEyeblossom());
			self::registerBlock(new CreakingHeart());
			self::registerBlock(new OpenEyeblossom());
			self::registerBlock(new PaleHangingMoss());
			self::registerBlock(new PaleMoss());
			self::registerBlock(new PaleMossCarpet());
			self::registerBlock(new PaleOakButton());
			self::registerBlock(new PaleOakDoor());
			self::registerBlock(new PaleOakDoubleSlab());
			self::registerBlock(new PaleOakFence());
			self::registerBlock(new PaleOakFenceGate());
			//TODO: PALE_OAK_HANGING_SIGN
			self::registerBlock(new PaleOakLeaves());
			self::registerBlock(new FixLog(BlockIds::PALE_OAK_LOG, 0, "Pale Oak Log"));
			self::registerBlock(new PaleOakPlanks());
			self::registerBlock(new PaleOakPressurePlate());
			self::registerBlock(new PaleOakSapling());
			self::registerBlock(new PaleOakSlab());
			self::registerBlock(new PaleOakStairs());
			self::registerBlock(new PaleOakSignPost());
			self::registerBlock(new WoodenTrapdoor(BlockIds::PALE_OAK_TRAPDOOR, 0, "Pale Oak Trapdoor"));
			self::registerBlock(new PaleOakWallSign());
			self::registerBlock(new FixWood(BlockIds::PALE_OAK_WOOD, 0, "Pale Oak Wood"));
			self::registerBlock(new Resin());
			self::registerBlock(new ResinBrickDoubleSlab());
			self::registerBlock(new ResinBrickSlab());
			self::registerBlock(new ResinBrickStairs());
			self::registerBlock(new ResinBricks());
			self::registerBlock(new StrippedLog(BlockIds::STRIPPED_PALE_OAK_LOG, 0, "Stripped Pale Oak Log"));
			self::registerBlock(new StrippedWood(BlockIds::STRIPPED_PALE_OAK_WOOD, 0, "Stripped Pale Oak Wood"));
			self::registerBlock(new StoneWall(BlockIds::RESIN_BRICK_WALL, 0, "Resin Brick Wall"));
			self::registerBlock(new Bush(BlockIds::BUSH, 0, "Bush"));
			self::registerBlock(new CactusFlower(BlockIds::CACTUS_FLOWER, 0, "Cactus Flower"));

			self::registerBlock(new class(BlockIds::FIREFLY_BUSH, 0, "Firefly Bush") extends Bush {
				public function canBeReplaced() : bool{ return false; }

				public function getToolType() : int{ return BlockToolType::TYPE_NONE; }
			});

			self::registerBlock(new class(BlockIds::LEAF_LITTER, 0, "Leaf Litter") extends Petals {
				public function canBeReplaced() : bool{ return true; }

				public function supportedWhenPlacedAt(Block $blockReplace, Vector3 $clickVector, int $face, bool $isClickedBlock) : bool{
					return !$this->isSameType($blockReplace) && parent::supportedWhenPlacedAt($blockReplace, $clickVector, $face, $isClickedBlock);
				}
			});
			self::registerBlock(new DryGrass(BlockIds::SHORT_DRY_GRASS, 0, "Short Dry Grass"));
			self::registerBlock(new DryGrass(BlockIds::TALL_DRY_GRASS, 0, "Tall Dry Grass"));
			self::registerBlock(new Petals(BlockIds::WILDFLOWERS, 0, "Wildflowers"));
			//TODO: COPPER_CHEST
			//TODO: EXPOSED_COPPER_CHEST
			//TODO: OXIDIZED_COPPER_CHEST
			//TODO: WAXED_COPPER_CHEST
			//TODO: WAXED_EXPOSED_COPPER_CHEST
			//TODO: WAXED_OXIDDIZED_COPPER_CHEST
			//TODO: WAXED_WEATHERED_COPPER_CHEST
			//TODO: WEATHERED_COPPER_CHEST

			//TODO: WEATHERED_COPPER_CHEST
			//TODO: WEATHERED_LIGHTNING_ROD
			//TODO: WEATHERED_COPPER_LANTERN
			//TODO: WEATHERED_COPPER_GOLEM_STATUE
			//TODO: WEATHERED_COPPER_CHAIN
			//TODO: WEATHERED_COPPER_BARS
			//TODO: WAXED_WEATHERED_LIGHTNING_ROD
			//TODO: WAXED_WEATHERED_COPPER_LANTERN
			//TODO: WAXED_WEATHERED_COPPER_GOLEM_STATUE
			//TODO: WAXED_WEATHERED_COPPER_CHAIN
			//TODO: WAXED_WEATHERED_COPPER_BARS
			//TODO: WAXED_OXIDIZED_LIGHTNING_ROD
			//TODO: WAXED_OXIDIZED_COPPER_LANTERN
			//TODO: WAXED_OXIDIZED_COPPER_GOLEM_STATUE
			//TODO: WAXED_OXIDIZED_COPPER_CHAIN
			//TODO: WAXED_OXIDIZED_COPPER_BARS
			//TODO: WAXED_LIGHTNING_ROD
			//TODO: WAXED_EXPOSED_LIGHTNING_ROD
			//TODO: WAXED_EXPOSED_COPPER_LANTERN
			//TODO: WAXED_EXPOSED_COPPER_GOLEM_STATUE
			//TODO: WAXED_EXPOSED_COPPER_CHAIN
			//TODO: WAXED_EXPOSED_COPPER_BARS
			//TODO: WAXED_COPPER_LANTERN
			//TODO: WAXED_COPPER_GOLEM_STATUE
			//TODO: WAXED_COPPER_CHAIN
			//TODO: WAXED_COPPER_BARS
			//TODO: WARPED_SHELF
			//TODO: SPRUCE_SHELF
			//TODO: PALE_OAK_SHELF
			//TODO: OXIDIZED_LIGHTNING_ROD
			//TODO: OXIDIZED_COPPER_LANTERN
			//TODO: OXIDIZED_COPPER_GOLEM_STATUE
			//TODO: OXIDIZED_COPPER_CHAIN
			//TODO: OXIDIZED_COPPER_BARS
			//TODO: OAK_SHELF
			//TODO: MANGROVE_SHELF
			//TODO: JUNGLE_SHELF
			//TODO: EXPOSED_LIGHTNING_ROD
			//TODO: EXPOSED_COPPER_LANTERN
			//TODO: EXPOSED_COPPER_GOLEM_STATUE
			//TODO: EXPOSED_COPPER_CHAIN
			//TODO: EXPOSED_COPPER_BARS
			//TODO: DARK_OAK_SHELF
			//TODO: CRIMSON_SHELF
			//TODO: COPPER_TORCH
			//TODO: COPPER_LANTERN
			//TODO: COPPER_GOLEM_STATUE
			//TODO: COPPER_CHAIN
			//TODO: COPPER_BARS
			//TODO: CHERRY_SHELF
			//TODO: BIRCH_SHELF
			//TODO: BAMBOO_SHELF
			//TODO: ACACIA_SHELF
			//TODO: GOLDEN_DANDELION
			//TODO: CHISELED_CINNABAR
			//TODO: CHISELED_SULFUR
			//TODO: CINNABAR
			//TODO: CINNABAR_BRICK_DOUBLE_SLAB
			//TODO: CINNABAR_BRICK_SLAB
			//TODO: CINNABAR_BRICK_STAIRS
			//TODO: CINNABAR_BRICK_WALL
			//TODO: CINNABAR_BRICKS
			//TODO: CINNABAR_DOUBLE_SLAB
			//TODO: CINNABAR_SLAB
			//TODO: CINNABAR_STAIRS
			//TODO: CINNABAR_WALL
			//TODO: POLISHED_CINNABAR
			//TODO: POLISHED_CINNABAR_DOUBLE_SLAB
			//TODO: POLISHED_CINNABAR_SLAB
			//TODO: SULFUR_WALL
			//TODO: SULFUR_STAIRS
			//TODO: SULFUR_SLAB
			//TODO: SULFUR_DOUBLE_SLAB
			//TODO: SULFUR_CUBE_SPAWN_EGG
			//TODO: SULFUR_CUBE_BUCKET
			//TODO: SULFUR_BRICKS
			//TODO: SULFUR_BRICK_WALL
			//TODO: SULFUR_BRICK_STAIRS
			//TODO: SULFUR_BRICK_SLAB
			//TODO: SULFUR_BRICK_DOUBLE_SLAB
			//TODO: SULFUR
			//TODO: POTENT_SULFUR
			//TODO: POLISHED_SULFUR_WALL
			//TODO: POLISHED_SULFUR_STAIRS
			//TODO: POLISHED_SULFUR_SLAB
			//TODO: POLISHED_SULFUR_DOUBLE_SLAB
			//TODO: POLISHED_SULFUR
			//TODO: POLISHED_CINNABAR_WALL
			//TODO: POLISHED_CINNABAR_STAIRS
		}
	}

	public static function isInit() : bool
	{
		return self::$fullList !== null;
	}

	/**
	 * Registers a block type into the index. Plugins may use this method to register new block types or override
	 * existing ones.
	 *
	 * NOTE: If you are registering a new block type, you will need to add it to the creative inventory yourself - it
	 * will not automatically appear there.
	 *
	 * @param bool $override Whether to override existing registrations
	 *
	 * @throws RuntimeException if something attempted to override an already-registered block without specifying the
	 * $override parameter.
	 */
	public static function registerBlock(Block $block, bool $override = false) : void
	{
		$id = $block->getId();
		$meta = $block->getDamage();

		if (!$override && self::isRegistered($id, $meta)) {
			throw new RuntimeException("Trying to overwrite an already registered block");
		}

		for ($meta = 0; $meta < (1 << Block::INTERNAL_METADATA_BITS); ++$meta) {
			$variant = clone $block;
			$variant->setDamage($meta);

			self::fillStaticArrays($variant->getFullId(), $variant);
		}
	}

	private static function fillStaticArrays(int $index, Block $block) : void
	{
		self::$fullList[$index] = $block;
		self::$mappedStateIds[$index] = $block->getFullId();
		self::$light[$index] = $block->getLightLevel();
		self::$lightFilter[$index] = min(15, $block->getLightFilter() + 1); //opacity plus 1 standard light filter
		self::$diffusesSkyLight[$index] = $block->diffusesSkyLight();
		self::$blastResistance[$index] = $block->getBlastResistance();
		self::$hasEntityCollision[$index] = $block->hasEntityCollision();
	}

	/**
	 * Returns a new Block instance with the specified ID, meta and position.
	 */
	public static function get(int $id, int $meta = 0, ?Position $pos = null) : Block
	{
		if($meta < 0 || $meta >= (1 << Block::INTERNAL_METADATA_BITS)){
			throw new InvalidArgumentException("Block meta value $meta is out of bounds");
		}

		$index = ($id << Block::INTERNAL_METADATA_BITS) | $meta;
		if($index < 0 || $index >= self::$fullList->getSize()){
			throw new InvalidArgumentException("Block ID $id is out of bounds");
		}

		if(self::$fullList[$index] !== null){
			$block = clone self::$fullList[$index];
		}else{
			$block = new UnknownBlock($id, $meta);
		}

		if ($pos !== null) {
			$block->x = $pos->getFloorX();
			$block->y = $pos->getFloorY();
			$block->z = $pos->getFloorZ();
			$block->level = $pos->level;
		}

		return $block;
	}

	public static function fromFullBlock(int $fullState, ?Position $pos = null) : Block
	{
		return self::get($fullState >> Block::INTERNAL_METADATA_BITS, $fullState & Block::INTERNAL_METADATA_MASK, $pos);
	}

	private static function normalizeName(string $name) : string
	{
		return str_contains($name, ":") ? $name : "minecraft:" . $name;
	}

	/**
	 * Returns the block with the given vanilla name, e.g. fromName("minecraft:oak_stairs", ["weirdo_direction" => 2]).
	 * States which are not given keep their default values. Returns null for unknown names or states.
	 *
	 * @param mixed[] $states state name => value (bool, int or string)
	 */
	public static function fromName(string $name, array $states = [], ?Position $pos = null) : ?Block
	{
		$mapping = RuntimeBlockMapping::getInstance(ProtocolInfo::CURRENT_PROTOCOL);
		$runtimeId = $mapping->lookupState(self::normalizeName($name), $states);
		return $runtimeId === null ? null : self::fromFullBlock($mapping->fromRuntimeId($runtimeId), $pos);
	}

	/**
	 * Returns the vanilla name of a block, e.g. "minecraft:oak_stairs"
	 */
	public static function getVanillaName(Block $block) : string
	{
		$mapping = RuntimeBlockMapping::getInstance(ProtocolInfo::CURRENT_PROTOCOL);
		return $mapping->toName($mapping->toRuntimeId($block->getFullId()));
	}

	/**
	 * Registers a block implementation by the vanilla name of the block, so the legacy ID doesn't have to be known.
	 * The factory gets the legacy ID taken from the block palette.
	 *
	 * @param \Closure $factory fn(int $legacyId) : Block
	 * @phpstan-param \Closure(int) : Block $factory
	 *
	 * @return int the legacy ID the block was registered with
	 * @throws InvalidArgumentException if the block is unknown
	 */
	public static function registerNamed(string $name, \Closure $factory, bool $override = false) : int
	{
		$mapping = RuntimeBlockMapping::getInstance(ProtocolInfo::CURRENT_PROTOCOL);
		$runtimeId = $mapping->lookupState(self::normalizeName($name));
		if ($runtimeId === null) {
			throw new InvalidArgumentException("Unknown block \"$name\"");
		}
		$legacyId = $mapping->fromRuntimeId($runtimeId) >> Block::INTERNAL_METADATA_BITS;
		$block = $factory($legacyId);
		if (!($block instanceof Block) || $block->getId() !== $legacyId) {
			throw new InvalidArgumentException("The factory of \"$name\" must create a block with the ID $legacyId");
		}
		self::registerBlock($block, $override);
		return $legacyId;
	}

	public static function getListOffset(int $id, int $meta) : int
	{
		return $id << Block::INTERNAL_METADATA_BITS | $meta;
	}

	/**
	 * Returns whether a specified block state is already registered in the block factory.
	 */
	public static function isRegistered(int $id, int $meta = 0) : bool
	{
		$b = self::$fullList[self::getListOffset($id, $meta)];
		return $b !== null && !($b instanceof UnknownBlock);
	}

	/**
	 * @return Block[]
	 */
	public static function getAllKnownStates() : array
	{
		return array_filter(self::$fullList->toArray(), function (?Block $v) : bool { return $v !== null; });
	}

	/**
	 * Returns the ID of the state mapped to the given state ID.
	 * Used to correct invalid blockstates found in loaded chunks.
	 */
	public static function getMappedStateId(int $fullState) : int
	{
		return self::$mappedStateIds[$fullState] ?? $fullState;
	}
}
