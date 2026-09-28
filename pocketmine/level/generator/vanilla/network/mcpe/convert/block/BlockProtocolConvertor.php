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

namespace pocketmine\level\generator\vanilla\network\mcpe\convert\block;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\block\Flower;
use pocketmine\level\generator\vanilla\block\Stone;
use pocketmine\level\generator\vanilla\block\StoneSlab;
use pocketmine\level\generator\vanilla\block\TallGrass;
use pocketmine\level\generator\vanilla\block\utils\ColorBlockMetaHelper;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\utils\SingletonTrait;
use RuntimeException;

class BlockProtocolConvertor {
	use SingletonTrait;

	/** @var BlockConvertor[] */
	private array $blocks = [];
	/** @var Block[][] */
	private array $caches = [];

	public function __construct() {
		$this->registerTransmittedMeta(BlockIds::PRISMARINE_STAIRS, BlockIds::COBBLESTONE_STAIRS, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::DARK_PRISMARINE_STAIRS, BlockIds::COBBLESTONE_STAIRS, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::PRISMARINE_BRICKS_STAIRS, BlockIds::COBBLESTONE_STAIRS, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::BLUE_ICE, BlockIds::ICE, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::ACACIA_BUTTON, BlockIds::WOODEN_BUTTON, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::BIRCH_BUTTON, BlockIds::WOODEN_BUTTON, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::DARK_OAK_BUTTON, BlockIds::WOODEN_BUTTON, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::JUNGLE_BUTTON, BlockIds::WOODEN_BUTTON, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::SPRUCE_BUTTON, BlockIds::WOODEN_BUTTON, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::ACACIA_TRAPDOOR, BlockIds::WOODEN_TRAPDOOR, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::BIRCH_TRAPDOOR, BlockIds::WOODEN_TRAPDOOR, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::DARK_OAK_TRAPDOOR, BlockIds::WOODEN_TRAPDOOR, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::JUNGLE_TRAPDOOR, BlockIds::WOODEN_TRAPDOOR, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::SPRUCE_TRAPDOOR, BlockIds::WOODEN_TRAPDOOR, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::ACACIA_PRESSURE_PLATE, BlockIds::WOODEN_PRESSURE_PLATE, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::BIRCH_PRESSURE_PLATE, BlockIds::WOODEN_PRESSURE_PLATE, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::DARK_OAK_PRESSURE_PLATE, BlockIds::WOODEN_PRESSURE_PLATE, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::JUNGLE_PRESSURE_PLATE, BlockIds::WOODEN_PRESSURE_PLATE, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::SPRUCE_PRESSURE_PLATE, BlockIds::WOODEN_PRESSURE_PLATE, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::CARVED_PUMPKIN, BlockIds::PUMPKIN, ProtocolInfo::PROTOCOL_407);

		$this->registerTransmittedMeta(BlockIds::GRANITE_STAIRS, BlockIds::COBBLESTONE_STAIRS, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::DIORITE_STAIRS, BlockIds::COBBLESTONE_STAIRS, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::ANDESITE_STAIRS, BlockIds::COBBLESTONE_STAIRS, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::POLISHED_GRANITE_STAIRS, BlockIds::COBBLESTONE_STAIRS, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::POLISHED_DIORITE_STAIRS, BlockIds::COBBLESTONE_STAIRS, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::POLISHED_ANDESITE_STAIRS, BlockIds::COBBLESTONE_STAIRS, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::MOSSY_STONE_BRICK_STAIRS, BlockIds::COBBLESTONE_STAIRS, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::SMOOTH_RED_SANDSTONE_STAIRS, BlockIds::COBBLESTONE_STAIRS, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::SMOOTH_SANDSTONE_STAIRS, BlockIds::COBBLESTONE_STAIRS, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::END_BRICK_STAIRS, BlockIds::COBBLESTONE_STAIRS, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::MOSSY_COBBLESTONE_STAIRS, BlockIds::COBBLESTONE_STAIRS, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::NORMAL_STONE_STAIRS, BlockIds::COBBLESTONE_STAIRS, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::SPRUCE_STANDING_SIGN, BlockIds::SIGN_POST, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::SPRUCE_WALL_SIGN, BlockIds::WALL_SIGN, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::RED_NETHER_BRICK_STAIRS, BlockIds::NETHER_BRICK_STAIRS, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::SMOOTH_QUARTZ_STAIRS, BlockIds::QUARTZ_STAIRS, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::BIRCH_STANDING_SIGN, BlockIds::SIGN_POST, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::BIRCH_WALL_SIGN, BlockIds::WALL_SIGN, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::JUNGLE_STANDING_SIGN, BlockIds::SIGN_POST, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::JUNGLE_WALL_SIGN, BlockIds::WALL_SIGN, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::ACACIA_STANDING_SIGN, BlockIds::SIGN_POST, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::ACACIA_WALL_SIGN, BlockIds::WALL_SIGN, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::DARKOAK_STANDING_SIGN, BlockIds::SIGN_POST, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::DARKOAK_WALL_SIGN, BlockIds::WALL_SIGN, ProtocolInfo::PROTOCOL_407);

		$this->registerTransmittedMeta(BlockIds::SMOKER, BlockIds::FURNACE, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::LIT_SMOKER, BlockIds::BURNING_FURNACE, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::BLAST_FURNACE, BlockIds::FURNACE, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::LIT_BLAST_FURNACE, BlockIds::BURNING_FURNACE, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::STONECUTTER_BLOCK, BlockIds::STONECUTTER, ProtocolInfo::PROTOCOL_407);
		$this->registerTransmittedMeta(BlockIds::SMITHING_TABLE, BlockIds::CRAFTING_TABLE, ProtocolInfo::PROTOCOL_407);

		$this->registerTransmittedMeta(BlockIds::SOUL_FIRE, BlockIds::FIRE, ProtocolInfo::PROTOCOL_419);
		$this->registerTransmittedMeta(BlockIds::CRIMSON_DOOR, BlockIds::WOODEN_DOOR_BLOCK, ProtocolInfo::PROTOCOL_419);
		$this->registerTransmittedMeta(BlockIds::WARPED_DOOR, BlockIds::WOODEN_DOOR_BLOCK, ProtocolInfo::PROTOCOL_419);
		$this->registerTransmittedMeta(BlockIds::CRIMSON_TRAPDOOR, BlockIds::WOODEN_TRAPDOOR, ProtocolInfo::PROTOCOL_419);
		$this->registerTransmittedMeta(BlockIds::WARPED_TRAPDOOR, BlockIds::WOODEN_TRAPDOOR, ProtocolInfo::PROTOCOL_419);
		$this->registerTransmittedMeta(BlockIds::CRIMSON_STANDING_SIGN, BlockIds::SIGN_POST, ProtocolInfo::PROTOCOL_419);
		$this->registerTransmittedMeta(BlockIds::WARPED_STANDING_SIGN, BlockIds::SIGN_POST, ProtocolInfo::PROTOCOL_419);
		$this->registerTransmittedMeta(BlockIds::CRIMSON_WALL_SIGN, BlockIds::WALL_SIGN, ProtocolInfo::PROTOCOL_419);
		$this->registerTransmittedMeta(BlockIds::WARPED_WALL_SIGN, BlockIds::WALL_SIGN, ProtocolInfo::PROTOCOL_419);
		$this->registerTransmittedMeta(BlockIds::CRIMSON_STAIRS, BlockIds::WOODEN_STAIRS, ProtocolInfo::PROTOCOL_419);
		$this->registerTransmittedMeta(BlockIds::WARPED_STAIRS, BlockIds::WOODEN_STAIRS, ProtocolInfo::PROTOCOL_419);
		$this->registerTransmittedMeta(BlockIds::CRIMSON_FENCE, BlockIds::FENCE, ProtocolInfo::PROTOCOL_419);
		$this->registerTransmittedMeta(BlockIds::WARPED_FENCE, BlockIds::FENCE, ProtocolInfo::PROTOCOL_419);
		$this->registerTransmittedMeta(BlockIds::CRIMSON_FENCE_GATE, BlockIds::FENCE_GATE, ProtocolInfo::PROTOCOL_419);
		$this->registerTransmittedMeta(BlockIds::WARPED_FENCE_GATE, BlockIds::FENCE_GATE, ProtocolInfo::PROTOCOL_419);
		$this->registerTransmittedMeta(BlockIds::CRIMSON_BUTTON, BlockIds::WOODEN_BUTTON, ProtocolInfo::PROTOCOL_419);
		$this->registerTransmittedMeta(BlockIds::WARPED_BUTTON, BlockIds::WOODEN_BUTTON, ProtocolInfo::PROTOCOL_419);
		$this->registerTransmittedMeta(BlockIds::CRIMSON_PRESSURE_PLATE, BlockIds::WOODEN_PRESSURE_PLATE, ProtocolInfo::PROTOCOL_419);
		$this->registerTransmittedMeta(BlockIds::WARPED_PRESSURE_PLATE, BlockIds::WOODEN_PRESSURE_PLATE, ProtocolInfo::PROTOCOL_419);
		$this->registerTransmittedMeta(BlockIds::SOUL_TORCH, BlockIds::TORCH, ProtocolInfo::PROTOCOL_419);
		$this->registerTransmittedMeta(BlockIds::POLISHED_BLACKSTONE_BRICK_STAIRS, BlockIds::STONE_BRICK_STAIRS, ProtocolInfo::PROTOCOL_419);
		$this->registerTransmittedMeta(BlockIds::BLACKSTONE_STAIRS, BlockIds::STONE_BRICK_STAIRS, ProtocolInfo::PROTOCOL_419);
		$this->registerTransmittedMeta(BlockIds::NETHER_GOLD_ORE, BlockIds::GOLD_ORE, ProtocolInfo::PROTOCOL_419);
		$this->registerTransmittedMeta(BlockIds::CRYING_OBSIDIAN, BlockIds::OBSIDIAN, ProtocolInfo::PROTOCOL_419);
		$this->registerTransmittedMeta(BlockIds::POLISHED_BLACKSTONE_STAIRS, BlockIds::STONE_BRICK_STAIRS, ProtocolInfo::PROTOCOL_419);
		$this->registerTransmittedMeta(BlockIds::POLISHED_BLACKSTONE_PRESSURE_PLATE, BlockIds::STONE_PRESSURE_PLATE, ProtocolInfo::PROTOCOL_419);
		$this->registerTransmittedMeta(BlockIds::POLISHED_BLACKSTONE_BUTTON, BlockIds::STONE_BUTTON, ProtocolInfo::PROTOCOL_419);
		$this->registerTransmittedMeta(BlockIds::CHISELED_NETHER_BRICKS, BlockIds::RED_NETHER_BRICK, ProtocolInfo::PROTOCOL_419);
		$this->registerTransmittedMeta(BlockIds::CRACKED_NETHER_BRICKS, BlockIds::RED_NETHER_BRICK, ProtocolInfo::PROTOCOL_419);

		$this->registerTransmittedMeta(BlockIds::POWDER_SNOW, BlockIds::SNOW_BLOCK, ProtocolInfo::PROTOCOL_440);
		$this->registerTransmittedMeta(BlockIds::TUFF, BlockIds::DIRT, ProtocolInfo::PROTOCOL_440);
		$this->registerTransmittedMeta(BlockIds::TINTED_GLASS, BlockIds::LEAVES, ProtocolInfo::PROTOCOL_440);
		$this->registerTransmittedMeta(BlockIds::GLOW_FRAME, BlockIds::ITEM_FRAME_BLOCK, ProtocolInfo::PROTOCOL_440);
		$this->registerTransmittedMeta(BlockIds::COBBLED_DEEPSLATE_STAIRS, BlockIds::STONE_STAIRS, ProtocolInfo::PROTOCOL_440);
		$this->registerTransmittedMeta(BlockIds::POLISHED_DEEPSLATE_STAIRS, BlockIds::STONE_STAIRS, ProtocolInfo::PROTOCOL_440);
		$this->registerTransmittedMeta(BlockIds::DEEPSLATE_TILE_STAIRS, BlockIds::STONE_STAIRS, ProtocolInfo::PROTOCOL_440);
		$this->registerTransmittedMeta(BlockIds::DEEPSLATE_BRICK_STAIRS, BlockIds::STONE_STAIRS, ProtocolInfo::PROTOCOL_440);
		$this->registerTransmittedMeta(BlockIds::CUT_COPPER_STAIRS, BlockIds::STONE_STAIRS, ProtocolInfo::PROTOCOL_440);
		$this->registerTransmittedMeta(BlockIds::EXPOSED_CUT_COPPER_STAIRS, BlockIds::STONE_STAIRS, ProtocolInfo::PROTOCOL_440);
		$this->registerTransmittedMeta(BlockIds::WEATHERED_CUT_COPPER_STAIRS, BlockIds::STONE_STAIRS, ProtocolInfo::PROTOCOL_440);
		$this->registerTransmittedMeta(BlockIds::OXIDIZED_CUT_COPPER_STAIRS, BlockIds::STONE_STAIRS, ProtocolInfo::PROTOCOL_440);
		$this->registerTransmittedMeta(BlockIds::WAXED_CUT_COPPER_STAIRS, BlockIds::STONE_STAIRS, ProtocolInfo::PROTOCOL_440);
		$this->registerTransmittedMeta(BlockIds::WAXED_EXPOSED_CUT_COPPER_STAIRS, BlockIds::STONE_STAIRS, ProtocolInfo::PROTOCOL_440);
		$this->registerTransmittedMeta(BlockIds::WAXED_WEATHERED_CUT_COPPER_STAIRS, BlockIds::STONE_STAIRS, ProtocolInfo::PROTOCOL_440);
		$this->registerTransmittedMeta(BlockIds::WAXED_OXIDIZED_CUT_COPPER_STAIRS, BlockIds::STONE_STAIRS, ProtocolInfo::PROTOCOL_440);

		$this->registerTransmittedMeta(BlockIds::MUD_BRICK_STAIRS, BlockIds::COBBLESTONE_STAIRS, ProtocolInfo::PROTOCOL_527);
		$this->registerTransmittedMeta(BlockIds::MANGROVE_DOOR, BlockIds::WOODEN_DOOR_BLOCK, ProtocolInfo::PROTOCOL_527);
		$this->registerTransmittedMeta(BlockIds::MANGROVE_BUTTON, BlockIds::WOODEN_BUTTON, ProtocolInfo::PROTOCOL_527);
		$this->registerTransmittedMeta(BlockIds::MANGROVE_FENCE, BlockIds::FENCE, ProtocolInfo::PROTOCOL_527);
		$this->registerTransmittedMeta(BlockIds::MANGROVE_FENCE_GATE, BlockIds::FENCE_GATE, ProtocolInfo::PROTOCOL_527);
		$this->registerTransmittedMeta(BlockIds::MANGROVE_PRESSURE_PLATE, BlockIds::WOODEN_PRESSURE_PLATE, ProtocolInfo::PROTOCOL_527);
		$this->registerTransmittedMeta(BlockIds::MANGROVE_STAIRS, BlockIds::WOODEN_STAIRS, ProtocolInfo::PROTOCOL_527);
		$this->registerTransmittedMeta(BlockIds::MANGROVE_STANDING_SIGN, BlockIds::SIGN_POST, ProtocolInfo::PROTOCOL_527);
		$this->registerTransmittedMeta(BlockIds::MANGROVE_TRAPDOOR, BlockIds::WOODEN_TRAPDOOR, ProtocolInfo::PROTOCOL_527);
		$this->registerTransmittedMeta(BlockIds::MANGROVE_WALL_SIGN, BlockIds::WALL_SIGN, ProtocolInfo::PROTOCOL_527);

		$this->registerTransmittedMeta(BlockIds::BAMBOO_DOOR, BlockIds::WOODEN_DOOR_BLOCK, ProtocolInfo::PROTOCOL_589);
		$this->registerTransmittedMeta(BlockIds::BAMBOO_BUTTON, BlockIds::WOODEN_BUTTON, ProtocolInfo::PROTOCOL_589);
		$this->registerTransmittedMeta(BlockIds::BAMBOO_DOUBLE_SLAB, BlockIds::DOUBLE_WOODEN_SLAB, ProtocolInfo::PROTOCOL_589);
		$this->registerTransmittedMeta(BlockIds::BAMBOO_FENCE, BlockIds::FENCE, ProtocolInfo::PROTOCOL_589);
		$this->registerTransmittedMeta(BlockIds::BAMBOO_FENCE_GATE, BlockIds::FENCE_GATE, ProtocolInfo::PROTOCOL_589);
		$this->registerTransmittedMeta(BlockIds::BAMBOO_MOSAIC_DOUBLE_SLAB, BlockIds::DOUBLE_WOODEN_SLAB, ProtocolInfo::PROTOCOL_589);
		$this->registerTransmittedMeta(BlockIds::BAMBOO_MOSAIC_STAIRS, BlockIds::WOODEN_STAIRS, ProtocolInfo::PROTOCOL_589);
		$this->registerTransmittedMeta(BlockIds::BAMBOO_PRESSURE_PLATE, BlockIds::WOODEN_PRESSURE_PLATE, ProtocolInfo::PROTOCOL_589);
		$this->registerTransmittedMeta(BlockIds::BAMBOO_STAIRS, BlockIds::WOODEN_STAIRS, ProtocolInfo::PROTOCOL_589);
		$this->registerTransmittedMeta(BlockIds::BAMBOO_STANDING_SIGN, BlockIds::SIGN_POST, ProtocolInfo::PROTOCOL_589);
		$this->registerTransmittedMeta(BlockIds::BAMBOO_WALL_SIGN, BlockIds::WALL_SIGN, ProtocolInfo::PROTOCOL_589);
		$this->registerTransmittedMeta(BlockIds::BAMBOO_TRAPDOOR, BlockIds::WOODEN_TRAPDOOR, ProtocolInfo::PROTOCOL_589);
		$this->registerTransmittedMeta(BlockIds::CHERRY_BUTTON, BlockIds::WOODEN_BUTTON, ProtocolInfo::PROTOCOL_589);
		$this->registerTransmittedMeta(BlockIds::CHERRY_FENCE, BlockIds::FENCE, ProtocolInfo::PROTOCOL_589);
		$this->registerTransmittedMeta(BlockIds::CHERRY_FENCE_GATE, BlockIds::FENCE_GATE, ProtocolInfo::PROTOCOL_589);
		$this->registerTransmittedMeta(BlockIds::CHERRY_PRESSURE_PLATE, BlockIds::WOODEN_PRESSURE_PLATE, ProtocolInfo::PROTOCOL_589);
		$this->registerTransmittedMeta(BlockIds::CHERRY_STAIRS, BlockIds::WOODEN_STAIRS, ProtocolInfo::PROTOCOL_589);
		$this->registerTransmittedMeta(BlockIds::CHERRY_STANDING_SIGN, BlockIds::SIGN_POST, ProtocolInfo::PROTOCOL_589);
		$this->registerTransmittedMeta(BlockIds::CHERRY_TRAPDOOR, BlockIds::WOODEN_TRAPDOOR, ProtocolInfo::PROTOCOL_589);
		$this->registerTransmittedMeta(BlockIds::CHERRY_WALL_SIGN, BlockIds::WALL_SIGN, ProtocolInfo::PROTOCOL_589);
		$this->registerTransmittedMeta(BlockIds::CHERRY_DOOR, BlockIds::WOODEN_DOOR_BLOCK, ProtocolInfo::PROTOCOL_589);

		$this->registerTransmittedMeta(BlockIds::COPPER_TRAPDOOR, BlockIds::WOODEN_TRAPDOOR, ProtocolInfo::PROTOCOL_630);
		$this->registerTransmittedMeta(BlockIds::EXPOSED_COPPER_TRAPDOOR, BlockIds::WOODEN_TRAPDOOR, ProtocolInfo::PROTOCOL_630);
		$this->registerTransmittedMeta(BlockIds::OXIDIZED_COPPER_TRAPDOOR, BlockIds::WOODEN_TRAPDOOR, ProtocolInfo::PROTOCOL_630);
		$this->registerTransmittedMeta(BlockIds::WAXED_COPPER_TRAPDOOR, BlockIds::WOODEN_TRAPDOOR, ProtocolInfo::PROTOCOL_630);
		$this->registerTransmittedMeta(BlockIds::WAXED_EXPOSED_COPPER_TRAPDOOR, BlockIds::WOODEN_TRAPDOOR, ProtocolInfo::PROTOCOL_630);
		$this->registerTransmittedMeta(BlockIds::WAXED_OXIDIZED_COPPER_TRAPDOOR, BlockIds::WOODEN_TRAPDOOR, ProtocolInfo::PROTOCOL_630);
		$this->registerTransmittedMeta(BlockIds::WAXED_WEATHERED_COPPER_TRAPDOOR, BlockIds::WOODEN_TRAPDOOR, ProtocolInfo::PROTOCOL_630);
		$this->registerTransmittedMeta(BlockIds::WEATHERED_COPPER_TRAPDOOR, BlockIds::WOODEN_TRAPDOOR, ProtocolInfo::PROTOCOL_630);
		$this->registerTransmittedMeta(BlockIds::WEATHERED_COPPER_DOOR, BlockIds::WOODEN_DOOR_BLOCK, ProtocolInfo::PROTOCOL_630);
		$this->registerTransmittedMeta(BlockIds::WAXED_WEATHERED_COPPER_DOOR, BlockIds::WOODEN_DOOR_BLOCK, ProtocolInfo::PROTOCOL_630);
		$this->registerTransmittedMeta(BlockIds::WAXED_OXIDIZED_COPPER_DOOR, BlockIds::WOODEN_DOOR_BLOCK, ProtocolInfo::PROTOCOL_630);
		$this->registerTransmittedMeta(BlockIds::WAXED_EXPOSED_COPPER_DOOR, BlockIds::WOODEN_DOOR_BLOCK, ProtocolInfo::PROTOCOL_630);
		$this->registerTransmittedMeta(BlockIds::WAXED_COPPER_DOOR, BlockIds::WOODEN_DOOR_BLOCK, ProtocolInfo::PROTOCOL_630);
		$this->registerTransmittedMeta(BlockIds::OXIDIZED_COPPER_DOOR, BlockIds::WOODEN_DOOR_BLOCK, ProtocolInfo::PROTOCOL_630);
		$this->registerTransmittedMeta(BlockIds::EXPOSED_COPPER_DOOR, BlockIds::WOODEN_DOOR_BLOCK, ProtocolInfo::PROTOCOL_630);
		$this->registerTransmittedMeta(BlockIds::COPPER_DOOR, BlockIds::WOODEN_DOOR_BLOCK, ProtocolInfo::PROTOCOL_630);

		$this->registerTransmittedMeta(BlockIds::POLISHED_TUFF_STAIRS, BlockIds::STONE_BRICK_STAIRS, ProtocolInfo::PROTOCOL_685);
		$this->registerTransmittedMeta(BlockIds::TUFF_BRICK_STAIRS, BlockIds::STONE_BRICK_STAIRS, ProtocolInfo::PROTOCOL_685);
		$this->registerTransmittedMeta(BlockIds::TUFF_STAIRS, BlockIds::STONE_BRICK_STAIRS, ProtocolInfo::PROTOCOL_685);

		$this->registerTransmittedMeta(BlockIds::PALE_OAK_BUTTON, BlockIds::WOODEN_BUTTON, ProtocolInfo::PROTOCOL_766);
		$this->registerTransmittedMeta(BlockIds::PALE_OAK_DOOR, BlockIds::WOODEN_DOOR_BLOCK, ProtocolInfo::PROTOCOL_766);
		$this->registerTransmittedMeta(BlockIds::PALE_OAK_FENCE, BlockIds::FENCE, ProtocolInfo::PROTOCOL_766);
		$this->registerTransmittedMeta(BlockIds::PALE_OAK_FENCE_GATE, BlockIds::FENCE_GATE, ProtocolInfo::PROTOCOL_766);
		$this->registerTransmittedMeta(BlockIds::PALE_OAK_PRESSURE_PLATE, BlockIds::WOODEN_PRESSURE_PLATE, ProtocolInfo::PROTOCOL_766);
		$this->registerTransmittedMeta(BlockIds::PALE_OAK_STAIRS, BlockIds::WOODEN_STAIRS, ProtocolInfo::PROTOCOL_766);
		$this->registerTransmittedMeta(BlockIds::PALE_OAK_STANDING_SIGN, BlockIds::SIGN_POST, ProtocolInfo::PROTOCOL_766);
		$this->registerTransmittedMeta(BlockIds::PALE_OAK_TRAPDOOR, BlockIds::WOODEN_TRAPDOOR, ProtocolInfo::PROTOCOL_766);
		$this->registerTransmittedMeta(BlockIds::PALE_OAK_WALL_SIGN, BlockIds::WALL_SIGN, ProtocolInfo::PROTOCOL_766);
		$this->registerTransmittedMeta(BlockIds::RESIN_BRICK_STAIRS, BlockIds::STONE_STAIRS, ProtocolInfo::PROTOCOL_766);

		$this->registerStaticMeta(BlockIds::DOUBLE_STONE_SLAB3, BlockIds::DOUBLE_STONE_SLAB, StoneSlab::STONE, ProtocolInfo::PROTOCOL_407);
		$this->registerStaticMeta(BlockIds::DOUBLE_STONE_SLAB4, BlockIds::DOUBLE_STONE_SLAB, StoneSlab::STONE, ProtocolInfo::PROTOCOL_407);

		$this->registerStaticMeta(BlockIds::WOOD, BlockIds::LOG, 12, ProtocolInfo::PROTOCOL_407);

		$this->registerStaticMeta(BlockIds::CALCITE, BlockIds::STONE, Stone::DIORITE, ProtocolInfo::PROTOCOL_440);
		$this->registerStaticMeta(BlockIds::AMETHYST_BLOCK, BlockIds::STONE, Stone::DIORITE, ProtocolInfo::PROTOCOL_440);
		$this->registerStaticMeta(BlockIds::BUDDING_AMETHYST, BlockIds::STONE, Stone::DIORITE, ProtocolInfo::PROTOCOL_440);
		$this->registerStaticMeta(BlockIds::AMETHYST_CLUSTER, BlockIds::STONE, Stone::DIORITE, ProtocolInfo::PROTOCOL_440);
		$this->registerStaticMeta(BlockIds::LARGE_AMETHYST_BUD, BlockIds::STONE, Stone::DIORITE, ProtocolInfo::PROTOCOL_440);
		$this->registerStaticMeta(BlockIds::MEDIUM_AMETHYST_BUD, BlockIds::STONE, Stone::DIORITE, ProtocolInfo::PROTOCOL_440);
		$this->registerStaticMeta(BlockIds::SMALL_AMETHYST_BUD, BlockIds::STONE, Stone::DIORITE, ProtocolInfo::PROTOCOL_440);
		$this->registerStaticMeta(BlockIds::MOSS_CARPET, BlockIds::CARPET, ColorBlockMetaHelper::GREEN, ProtocolInfo::PROTOCOL_440);
		$this->registerStaticMeta(BlockIds::AZALEA, BlockIds::GRASS, ColorBlockMetaHelper::GRAY, ProtocolInfo::PROTOCOL_440);
		$this->registerStaticMeta(BlockIds::FLOWERING_AZALEA, BlockIds::GRASS, ColorBlockMetaHelper::GRAY, ProtocolInfo::PROTOCOL_440);

		$this->registerStaticMeta(BlockIds::CHISELED_COPPER, BlockIds::STONE, Stone::POLISHED_ANDESITE, ProtocolInfo::PROTOCOL_630);
		$this->registerStaticMeta(BlockIds::COPPER_GRATE, BlockIds::STONE, Stone::POLISHED_ANDESITE, ProtocolInfo::PROTOCOL_630);
		$this->registerStaticMeta(BlockIds::EXPOSED_CHISELED_COPPER, BlockIds::STONE, Stone::POLISHED_ANDESITE, ProtocolInfo::PROTOCOL_630);
		$this->registerStaticMeta(BlockIds::EXPOSED_COPPER_GRATE, BlockIds::STONE, Stone::POLISHED_ANDESITE, ProtocolInfo::PROTOCOL_630);
		$this->registerStaticMeta(BlockIds::OXIDIZED_CHISELED_COPPER, BlockIds::STONE, Stone::POLISHED_ANDESITE, ProtocolInfo::PROTOCOL_630);
		$this->registerStaticMeta(BlockIds::OXIDIZED_COPPER_GRATE, BlockIds::STONE, Stone::POLISHED_ANDESITE, ProtocolInfo::PROTOCOL_630);
		$this->registerStaticMeta(BlockIds::WAXED_CHISELED_COPPER, BlockIds::STONE, Stone::POLISHED_ANDESITE, ProtocolInfo::PROTOCOL_630);
		$this->registerStaticMeta(BlockIds::WAXED_COPPER_GRATE, BlockIds::STONE, Stone::POLISHED_ANDESITE, ProtocolInfo::PROTOCOL_630);
		$this->registerStaticMeta(BlockIds::WAXED_EXPOSED_CHISELED_COPPER, BlockIds::STONE, Stone::POLISHED_ANDESITE, ProtocolInfo::PROTOCOL_630);
		$this->registerStaticMeta(BlockIds::WAXED_EXPOSED_COPPER_GRATE, BlockIds::STONE, Stone::POLISHED_ANDESITE, ProtocolInfo::PROTOCOL_630);
		$this->registerStaticMeta(BlockIds::WAXED_OXIDIZED_CHISELED_COPPER, BlockIds::STONE, Stone::POLISHED_ANDESITE, ProtocolInfo::PROTOCOL_630);
		$this->registerStaticMeta(BlockIds::WAXED_OXIDIZED_COPPER_GRATE, BlockIds::STONE, Stone::POLISHED_ANDESITE, ProtocolInfo::PROTOCOL_630);
		$this->registerStaticMeta(BlockIds::WAXED_WEATHERED_CHISELED_COPPER, BlockIds::STONE, Stone::POLISHED_ANDESITE, ProtocolInfo::PROTOCOL_630);
		$this->registerStaticMeta(BlockIds::WAXED_WEATHERED_COPPER_GRATE, BlockIds::STONE, Stone::POLISHED_ANDESITE, ProtocolInfo::PROTOCOL_630);
		$this->registerStaticMeta(BlockIds::WEATHERED_CHISELED_COPPER, BlockIds::STONE, Stone::POLISHED_ANDESITE, ProtocolInfo::PROTOCOL_630);
		$this->registerStaticMeta(BlockIds::WEATHERED_COPPER_GRATE, BlockIds::STONE, Stone::POLISHED_ANDESITE, ProtocolInfo::PROTOCOL_630);

		$this->registerStaticMeta(BlockIds::PALE_MOSS_CARPET, BlockIds::CARPET, ColorBlockMetaHelper::GRAY, ProtocolInfo::PROTOCOL_766);

		$this->registerStaticMeta(BlockIds::CACTUS_FLOWER, BlockIds::RED_FLOWER, Flower::TYPE_PINK_TULIP, ProtocolInfo::PROTOCOL_786);
		$this->registerStaticMeta(BlockIds::FIREFLY_BUSH, BlockIds::TALL_GRASS, TallGrass::TYPE_TALL_GRASS, ProtocolInfo::PROTOCOL_786);
		$this->registerStaticMeta(BlockIds::SHORT_DRY_GRASS, BlockIds::TALL_GRASS, TallGrass::TYPE_FERN, ProtocolInfo::PROTOCOL_786);

		$this->registerNullableMeta(BlockIds::JUKEBOX, BlockIds::PLANKS, ProtocolInfo::PROTOCOL_407);

		$this->registerNullableMeta(BlockIds::ELEMENT_1, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_2, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_3, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_4, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_5, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_6, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_7, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_8, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_9, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_10, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_11, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_12, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_13, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_14, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_15, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_16, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_17, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_18, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_19, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_20, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_21, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_22, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_23, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_24, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_25, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_26, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_27, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_28, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_29, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_30, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_31, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_32, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_33, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_34, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_35, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_36, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_37, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_38, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_39, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_40, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_41, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_42, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_43, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_44, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_45, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_46, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_47, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_48, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_49, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_50, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_51, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_52, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_53, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_54, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_55, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_56, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_57, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_58, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_59, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_60, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_61, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_62, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_63, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_64, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_65, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_66, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_67, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_68, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_69, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_70, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_71, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_72, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_73, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_74, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_75, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_76, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_77, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_78, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_79, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_80, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_81, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_82, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_83, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_84, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_85, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_86, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_87, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_88, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_89, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_90, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_91, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_92, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_93, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_94, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_95, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_96, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_97, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_98, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_99, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_100, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_101, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_102, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_103, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_104, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_105, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_106, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_107, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_108, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_109, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_110, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_111, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_112, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_113, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_114, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_115, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_116, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_117, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::ELEMENT_118, BlockIds::SLIME_BLOCK, ProtocolInfo::PROTOCOL_407);

		$this->registerNullableMeta(BlockIds::BARRIER, BlockIds::INVISIBLEBEDROCK, ProtocolInfo::PROTOCOL_407);

		$this->registerNullableMeta(BlockIds::SMOOTH_STONE, BlockIds::STONE, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::LANTERN, BlockIds::TORCH, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::CAMPFIRE, BlockIds::FURNACE, ProtocolInfo::PROTOCOL_407);

		$this->registerNullableMeta(BlockIds::CRIMSON_PLANKS, BlockIds::PLANKS, ProtocolInfo::PROTOCOL_419);
		$this->registerNullableMeta(BlockIds::WARPED_PLANKS, BlockIds::PLANKS, ProtocolInfo::PROTOCOL_419);
		$this->registerNullableMeta(BlockIds::CRIMSON_DOUBLE_SLAB, BlockIds::DOUBLE_WOODEN_SLAB, ProtocolInfo::PROTOCOL_419);
		$this->registerNullableMeta(BlockIds::WARPED_DOUBLE_SLAB, BlockIds::DOUBLE_WOODEN_SLAB, ProtocolInfo::PROTOCOL_419);
		$this->registerNullableMeta(BlockIds::SOUL_LANTERN, BlockIds::TORCH, ProtocolInfo::PROTOCOL_419);
		$this->registerNullableMeta(BlockIds::NETHERITE_BLOCK, BlockIds::DIAMOND_BLOCK, ProtocolInfo::PROTOCOL_419);
		$this->registerNullableMeta(BlockIds::ANCIENT_DEBRIS, BlockIds::OBSIDIAN, ProtocolInfo::PROTOCOL_419);
		$this->registerNullableMeta(BlockIds::RESPAWN_ANCHOR, BlockIds::OBSIDIAN, ProtocolInfo::PROTOCOL_419);
		$this->registerNullableMeta(BlockIds::BLACKSTONE, BlockIds::STONE, ProtocolInfo::PROTOCOL_419);
		$this->registerNullableMeta(BlockIds::POLISHED_BLACKSTONE_BRICKS, BlockIds::STONE, ProtocolInfo::PROTOCOL_419);
		$this->registerNullableMeta(BlockIds::CHISELED_POLISHED_BLACKSTONE, BlockIds::STONE, ProtocolInfo::PROTOCOL_419);
		$this->registerNullableMeta(BlockIds::CRACKED_POLISHED_BLACKSTONE_BRICKS, BlockIds::STONE, ProtocolInfo::PROTOCOL_419);
		$this->registerNullableMeta(BlockIds::GILDED_BLACKSTONE, BlockIds::GOLD_ORE, ProtocolInfo::PROTOCOL_419);
		$this->registerNullableMeta(BlockIds::BLACKSTONE_DOUBLE_SLAB, BlockIds::DOUBLE_STONE_SLAB, ProtocolInfo::PROTOCOL_419);
		$this->registerNullableMeta(BlockIds::POLISHED_BLACKSTONE_BRICK_DOUBLE_SLAB, BlockIds::DOUBLE_STONE_SLAB, ProtocolInfo::PROTOCOL_419);
		$this->registerNullableMeta(BlockIds::SOUL_CAMPFIRE, BlockIds::FURNACE, ProtocolInfo::PROTOCOL_407);
		$this->registerNullableMeta(BlockIds::POLISHED_BLACKSTONE, BlockIds::STONE, ProtocolInfo::PROTOCOL_419);
		$this->registerNullableMeta(BlockIds::POLISHED_BLACKSTONE_DOUBLE_SLAB, BlockIds::DOUBLE_STONE_SLAB, ProtocolInfo::PROTOCOL_419);
		$this->registerNullableMeta(BlockIds::QUARTZ_BRICKS, BlockIds::QUARTZ_BLOCK, ProtocolInfo::PROTOCOL_419);

		$this->registerNullableMeta(BlockIds::SCULK_SENSOR, BlockIds::STONE_SLAB, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::COPPER_ORE, BlockIds::COAL_ORE, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::LIGHTNING_ROD, BlockIds::END_ROD, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::DRIPSTONE_BLOCK, BlockIds::STONE, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::DIRT_WITH_ROOTS, BlockIds::DIRT, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::MOSS_BLOCK, BlockIds::DIRT, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::SPORE_BLOSSOM, BlockIds::RED_FLOWER, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::AZALEA_LEAVES, BlockIds::LEAVES, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::AZALEA_LEAVES_FLOWERED, BlockIds::LEAVES, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::SMOOTH_BASALT, BlockIds::STONE, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::DEEPSLATE, BlockIds::STONE, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::COBBLED_DEEPSLATE, BlockIds::STONE, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::POLISHED_DEEPSLATE, BlockIds::STONE, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::DEEPSLATE_TILES, BlockIds::STONE, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::DEEPSLATE_BRICKS, BlockIds::STONE, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::CHISELED_DEEPSLATE, BlockIds::STONE, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::COBBLED_DEEPSLATE_DOUBLE_SLAB, BlockIds::DOUBLE_STONE_SLAB, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::POLISHED_DEEPSLATE_DOUBLE_SLAB, BlockIds::DOUBLE_STONE_SLAB, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::DEEPSLATE_TILE_DOUBLE_SLAB, BlockIds::DOUBLE_STONE_SLAB, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::DEEPSLATE_BRICK_DOUBLE_SLAB, BlockIds::DOUBLE_STONE_SLAB, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::DEEPSLATE_LAPIS_ORE, BlockIds::LAPIS_ORE, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::DEEPSLATE_IRON_ORE, BlockIds::IRON_ORE, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::DEEPSLATE_GOLD_ORE, BlockIds::GOLD_ORE, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::DEEPSLATE_REDSTONE_ORE, BlockIds::REDSTONE_ORE, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::LIT_DEEPSLATE_REDSTONE_ORE, BlockIds::GLOWING_REDSTONE_ORE, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::DEEPSLATE_DIAMOND_ORE, BlockIds::DIAMOND_ORE, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::DEEPSLATE_COAL_ORE, BlockIds::COAL_ORE, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::DEEPSLATE_EMERALD_ORE, BlockIds::EMERALD_ORE, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::DEEPSLATE_COPPER_ORE, BlockIds::COAL_ORE, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::CRACKED_DEEPSLATE_TILES, BlockIds::STONE, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::CRACKED_DEEPSLATE_BRICKS, BlockIds::STONE, ProtocolInfo::PROTOCOL_440);

		$this->registerNullableMeta(BlockIds::CANDLE, BlockIds::TORCH, ProtocolInfo::PROTOCOL_448);
		$this->registerNullableMeta(BlockIds::WHITE_CANDLE, BlockIds::TORCH, ProtocolInfo::PROTOCOL_448);
		$this->registerNullableMeta(BlockIds::ORANGE_CANDLE, BlockIds::TORCH, ProtocolInfo::PROTOCOL_448);
		$this->registerNullableMeta(BlockIds::MAGENTA_CANDLE, BlockIds::TORCH, ProtocolInfo::PROTOCOL_448);
		$this->registerNullableMeta(BlockIds::LIGHT_BLUE_CANDLE, BlockIds::TORCH, ProtocolInfo::PROTOCOL_448);
		$this->registerNullableMeta(BlockIds::YELLOW_CANDLE, BlockIds::TORCH, ProtocolInfo::PROTOCOL_448);
		$this->registerNullableMeta(BlockIds::LIME_CANDLE, BlockIds::TORCH, ProtocolInfo::PROTOCOL_448);
		$this->registerNullableMeta(BlockIds::PINK_CANDLE, BlockIds::TORCH, ProtocolInfo::PROTOCOL_448);
		$this->registerNullableMeta(BlockIds::GRAY_CANDLE, BlockIds::TORCH, ProtocolInfo::PROTOCOL_448);
		$this->registerNullableMeta(BlockIds::LIGHT_GRAY_CANDLE, BlockIds::TORCH, ProtocolInfo::PROTOCOL_448);
		$this->registerNullableMeta(BlockIds::CYAN_CANDLE, BlockIds::TORCH, ProtocolInfo::PROTOCOL_448);
		$this->registerNullableMeta(BlockIds::PURPLE_CANDLE, BlockIds::TORCH, ProtocolInfo::PROTOCOL_448);
		$this->registerNullableMeta(BlockIds::BLUE_CANDLE, BlockIds::TORCH, ProtocolInfo::PROTOCOL_448);
		$this->registerNullableMeta(BlockIds::BROWN_CANDLE, BlockIds::TORCH, ProtocolInfo::PROTOCOL_448);
		$this->registerNullableMeta(BlockIds::GREEN_CANDLE, BlockIds::TORCH, ProtocolInfo::PROTOCOL_448);
		$this->registerNullableMeta(BlockIds::RED_CANDLE, BlockIds::TORCH, ProtocolInfo::PROTOCOL_448);
		$this->registerNullableMeta(BlockIds::BLACK_CANDLE, BlockIds::TORCH, ProtocolInfo::PROTOCOL_448);
		$this->registerNullableMeta(BlockIds::CANDLE_CAKE, BlockIds::CAKE_BLOCK, ProtocolInfo::PROTOCOL_448);
		$this->registerNullableMeta(BlockIds::WHITE_CANDLE_CAKE, BlockIds::CAKE_BLOCK, ProtocolInfo::PROTOCOL_448);
		$this->registerNullableMeta(BlockIds::ORANGE_CANDLE_CAKE, BlockIds::CAKE_BLOCK, ProtocolInfo::PROTOCOL_448);
		$this->registerNullableMeta(BlockIds::MAGENTA_CANDLE_CAKE, BlockIds::CAKE_BLOCK, ProtocolInfo::PROTOCOL_448);
		$this->registerNullableMeta(BlockIds::LIGHT_BLUE_CANDLE_CAKE, BlockIds::CAKE_BLOCK, ProtocolInfo::PROTOCOL_448);
		$this->registerNullableMeta(BlockIds::YELLOW_CANDLE_CAKE, BlockIds::CAKE_BLOCK, ProtocolInfo::PROTOCOL_448);
		$this->registerNullableMeta(BlockIds::LIME_CANDLE_CAKE, BlockIds::CAKE_BLOCK, ProtocolInfo::PROTOCOL_448);
		$this->registerNullableMeta(BlockIds::PINK_CANDLE_CAKE, BlockIds::CAKE_BLOCK, ProtocolInfo::PROTOCOL_448);
		$this->registerNullableMeta(BlockIds::GRAY_CANDLE_CAKE, BlockIds::CAKE_BLOCK, ProtocolInfo::PROTOCOL_448);
		$this->registerNullableMeta(BlockIds::LIGHT_GRAY_CANDLE_CAKE, BlockIds::CAKE_BLOCK, ProtocolInfo::PROTOCOL_448);
		$this->registerNullableMeta(BlockIds::CYAN_CANDLE_CAKE, BlockIds::CAKE_BLOCK, ProtocolInfo::PROTOCOL_448);
		$this->registerNullableMeta(BlockIds::PURPLE_CANDLE_CAKE, BlockIds::CAKE_BLOCK, ProtocolInfo::PROTOCOL_448);
		$this->registerNullableMeta(BlockIds::BLUE_CANDLE_CAKE, BlockIds::CAKE_BLOCK, ProtocolInfo::PROTOCOL_448);
		$this->registerNullableMeta(BlockIds::BROWN_CANDLE_CAKE, BlockIds::CAKE_BLOCK, ProtocolInfo::PROTOCOL_448);
		$this->registerNullableMeta(BlockIds::GREEN_CANDLE_CAKE, BlockIds::CAKE_BLOCK, ProtocolInfo::PROTOCOL_448);
		$this->registerNullableMeta(BlockIds::RED_CANDLE_CAKE, BlockIds::CAKE_BLOCK, ProtocolInfo::PROTOCOL_448);
		$this->registerNullableMeta(BlockIds::BLACK_CANDLE_CAKE, BlockIds::CAKE_BLOCK, ProtocolInfo::PROTOCOL_448);

		$this->registerNullableMeta(BlockIds::COPPER_BLOCK, BlockIds::COAL_BLOCK, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::EXPOSED_COPPER, BlockIds::COAL_BLOCK, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::WEATHERED_COPPER, BlockIds::COAL_BLOCK, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::OXIDIZED_COPPER, BlockIds::COAL_BLOCK, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::WAXED_COPPER, BlockIds::COAL_BLOCK, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::WAXED_EXPOSED_COPPER, BlockIds::COAL_BLOCK, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::WAXED_WEATHERED_COPPER, BlockIds::COAL_BLOCK, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::WAXED_OXIDIZED_COPPER, BlockIds::COAL_BLOCK, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::CUT_COPPER, BlockIds::STONE, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::EXPOSED_CUT_COPPER, BlockIds::STONE, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::WEATHERED_CUT_COPPER, BlockIds::STONE, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::OXIDIZED_CUT_COPPER, BlockIds::STONE, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::WAXED_CUT_COPPER, BlockIds::STONE, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::WAXED_EXPOSED_CUT_COPPER, BlockIds::STONE, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::WAXED_WEATHERED_CUT_COPPER, BlockIds::STONE, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::WAXED_OXIDIZED_CUT_COPPER, BlockIds::STONE, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::DOUBLE_CUT_COPPER_SLAB, BlockIds::DOUBLE_STONE_SLAB, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::EXPOSED_DOUBLE_CUT_COPPER_SLAB, BlockIds::DOUBLE_STONE_SLAB, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::WEATHERED_DOUBLE_CUT_COPPER_SLAB, BlockIds::DOUBLE_STONE_SLAB, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::OXIDIZED_DOUBLE_CUT_COPPER_SLAB, BlockIds::DOUBLE_STONE_SLAB, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::WAXED_DOUBLE_CUT_COPPER_SLAB, BlockIds::DOUBLE_STONE_SLAB, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::WAXED_EXPOSED_DOUBLE_CUT_COPPER_SLAB, BlockIds::DOUBLE_STONE_SLAB, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::WAXED_WEATHERED_DOUBLE_CUT_COPPER_SLAB, BlockIds::DOUBLE_STONE_SLAB, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::WAXED_OXIDIZED_DOUBLE_CUT_COPPER_SLAB, BlockIds::DOUBLE_STONE_SLAB, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::RAW_IRON_BLOCK, BlockIds::IRON_BLOCK, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::RAW_COPPER_BLOCK, BlockIds::COAL_BLOCK, ProtocolInfo::PROTOCOL_440);
		$this->registerNullableMeta(BlockIds::RAW_GOLD_BLOCK, BlockIds::GOLD_BLOCK, ProtocolInfo::PROTOCOL_440);

		$this->registerNullableMeta(BlockIds::SCULK, BlockIds::FLOWER_POT_BLOCK, ProtocolInfo::PROTOCOL_465);

		$this->registerNullableMeta(BlockIds::MANGROVE_LEAVES, BlockIds::LEAVES, ProtocolInfo::PROTOCOL_527);
		$this->registerNullableMeta(BlockIds::MUD, BlockIds::DIRT, ProtocolInfo::PROTOCOL_527);
		$this->registerNullableMeta(BlockIds::MUD_BRICK_DOUBLE_SLAB, BlockIds::DOUBLE_STONE_SLAB, ProtocolInfo::PROTOCOL_527);
		$this->registerNullableMeta(BlockIds::MUD_BRICKS, BlockIds::DIRT, ProtocolInfo::PROTOCOL_527);
		$this->registerNullableMeta(BlockIds::PACKED_MUD, BlockIds::DIRT, ProtocolInfo::PROTOCOL_527);
		$this->registerNullableMeta(BlockIds::REINFORCED_DEEPSLATE, BlockIds::OBSIDIAN, ProtocolInfo::PROTOCOL_527);
		$this->registerNullableMeta(BlockIds::MANGROVE_DOUBLE_SLAB, BlockIds::DOUBLE_WOODEN_SLAB, ProtocolInfo::PROTOCOL_527);
		$this->registerNullableMeta(BlockIds::MANGROVE_PLANKS, BlockIds::PLANKS, ProtocolInfo::PROTOCOL_527);

		$this->registerNullableMeta(BlockIds::BAMBOO_MOSAIC, BlockIds::PLANKS, ProtocolInfo::PROTOCOL_589);
		$this->registerNullableMeta(BlockIds::BAMBOO_PLANKS, BlockIds::PLANKS, ProtocolInfo::PROTOCOL_589);
		$this->registerNullableMeta(BlockIds::DECORATED_POT, BlockIds::FLOWER_POT_BLOCK, ProtocolInfo::PROTOCOL_589);
		$this->registerNullableMeta(BlockIds::SUSPICIOUS_SAND, BlockIds::SAND, ProtocolInfo::PROTOCOL_589);
		$this->registerNullableMeta(BlockIds::TORCHFLOWER, BlockIds::RED_FLOWER, ProtocolInfo::PROTOCOL_589);
		$this->registerNullableMeta(BlockIds::CHERRY_DOUBLE_SLAB, BlockIds::DOUBLE_WOODEN_SLAB, ProtocolInfo::PROTOCOL_589);
		$this->registerNullableMeta(BlockIds::CHERRY_LEAVES, BlockIds::LEAVES, ProtocolInfo::PROTOCOL_589);
		$this->registerNullableMeta(BlockIds::CHERRY_PLANKS, BlockIds::PLANKS, ProtocolInfo::PROTOCOL_589);
		$this->registerNullableMeta(BlockIds::CHERRY_SAPLING, BlockIds::SAPLING, ProtocolInfo::PROTOCOL_589);
		$this->registerNullableMeta(BlockIds::SUSPICIOUS_GRAVEL, BlockIds::GRAVEL, ProtocolInfo::PROTOCOL_589);
		$this->registerNullableMeta(BlockIds::SNIFFER_EGG, BlockIds::FLOWER_POT_BLOCK, ProtocolInfo::PROTOCOL_589);

		$this->registerNullableMeta(BlockIds::HEAVY_CORE, BlockIds::FLOWER_POT_BLOCK, ProtocolInfo::PROTOCOL_671);

		$this->registerNullableMeta(BlockIds::CHISELED_TUFF, BlockIds::STONE, ProtocolInfo::PROTOCOL_685);
		$this->registerNullableMeta(BlockIds::CHISELED_TUFF_BRICKS, BlockIds::STONE, ProtocolInfo::PROTOCOL_685);
		$this->registerNullableMeta(BlockIds::POLISHED_TUFF, BlockIds::DIRT, ProtocolInfo::PROTOCOL_685);
		$this->registerNullableMeta(BlockIds::POLISHED_TUFF_DOUBLE_SLAB, BlockIds::DOUBLE_STONE_SLAB, ProtocolInfo::PROTOCOL_685);
		$this->registerNullableMeta(BlockIds::TUFF_BRICK_DOUBLE_SLAB, BlockIds::DOUBLE_STONE_SLAB, ProtocolInfo::PROTOCOL_685);
		$this->registerNullableMeta(BlockIds::TUFF_BRICKS, BlockIds::DIRT, ProtocolInfo::PROTOCOL_685);
		$this->registerNullableMeta(BlockIds::TUFF_DOUBLE_SLAB, BlockIds::DOUBLE_STONE_SLAB, ProtocolInfo::PROTOCOL_685);

		$this->registerNullableMeta(BlockIds::CHISELED_RESIN_BRICKS, BlockIds::BRICK_BLOCK, ProtocolInfo::PROTOCOL_766);
		$this->registerNullableMeta(BlockIds::CLOSED_EYEBLOSSOM, BlockIds::RED_FLOWER, ProtocolInfo::PROTOCOL_766);
		$this->registerNullableMeta(BlockIds::CREAKING_HEART, BlockIds::BONE_BLOCK, ProtocolInfo::PROTOCOL_766);
		$this->registerNullableMeta(BlockIds::OPEN_EYEBLOSSOM, BlockIds::RED_FLOWER, ProtocolInfo::PROTOCOL_766);
		$this->registerNullableMeta(BlockIds::PALE_HANGING_MOSS, BlockIds::REEDS_BLOCK, ProtocolInfo::PROTOCOL_766);
		$this->registerNullableMeta(BlockIds::PALE_MOSS_BLOCK, BlockIds::DIRT, ProtocolInfo::PROTOCOL_766);
		$this->registerNullableMeta(BlockIds::PALE_OAK_DOUBLE_SLAB, BlockIds::DOUBLE_WOODEN_SLAB, ProtocolInfo::PROTOCOL_766);
		$this->registerNullableMeta(BlockIds::PALE_OAK_LEAVES, BlockIds::LEAVES, ProtocolInfo::PROTOCOL_766);
		$this->registerNullableMeta(BlockIds::PALE_OAK_PLANKS, BlockIds::PLANKS, ProtocolInfo::PROTOCOL_766);
		$this->registerNullableMeta(BlockIds::PALE_OAK_SAPLING, BlockIds::SAPLING, ProtocolInfo::PROTOCOL_766);
		$this->registerNullableMeta(BlockIds::RESIN_BLOCK, BlockIds::BRICK_BLOCK, ProtocolInfo::PROTOCOL_766);
		$this->registerNullableMeta(BlockIds::RESIN_BRICK_DOUBLE_SLAB, BlockIds::DOUBLE_STONE_SLAB, ProtocolInfo::PROTOCOL_766);
		$this->registerNullableMeta(BlockIds::RESIN_BRICKS, BlockIds::BRICK_BLOCK, ProtocolInfo::PROTOCOL_766);

		$this->registerNullableMeta(BlockIds::BUSH, BlockIds::TALL_GRASS, ProtocolInfo::PROTOCOL_786);
		$this->registerNullableMeta(BlockIds::LEAF_LITTER, BlockIds::RED_FLOWER, ProtocolInfo::PROTOCOL_786);
		$this->registerNullableMeta(BlockIds::TALL_DRY_GRASS, BlockIds::TALL_GRASS, ProtocolInfo::PROTOCOL_786);
		$this->registerNullableMeta(BlockIds::WILDFLOWERS, BlockIds::YELLOW_FLOWER, ProtocolInfo::PROTOCOL_786);

		$this->registerNullableMeta(BlockIds::DRIED_GHAST, BlockIds::FLOWER_POT_BLOCK, ProtocolInfo::PROTOCOL_818);

		$logMetaTo2bit = fn(int $convertProtocolVersion) : BlockConvertor => new LogMetaTo2bitConvertor($convertProtocolVersion);

		$this->register(BlockIds::STRIPPED_SPRUCE_LOG, $logMetaTo2bit(ProtocolInfo::PROTOCOL_407));
		$this->register(BlockIds::STRIPPED_BIRCH_LOG, $logMetaTo2bit(ProtocolInfo::PROTOCOL_407));
		$this->register(BlockIds::STRIPPED_JUNGLE_LOG, $logMetaTo2bit(ProtocolInfo::PROTOCOL_407));
		$this->register(BlockIds::STRIPPED_ACACIA_LOG, $logMetaTo2bit(ProtocolInfo::PROTOCOL_407));
		$this->register(BlockIds::STRIPPED_DARK_OAK_LOG, $logMetaTo2bit(ProtocolInfo::PROTOCOL_407));
		$this->register(BlockIds::STRIPPED_OAK_LOG, $logMetaTo2bit(ProtocolInfo::PROTOCOL_407));

		$this->register(BlockIds::CRIMSON_STEM, $logMetaTo2bit(ProtocolInfo::PROTOCOL_419));
		$this->register(BlockIds::WARPED_STEM, $logMetaTo2bit(ProtocolInfo::PROTOCOL_419));
		$this->register(BlockIds::STRIPPED_CRIMSON_STEM, $logMetaTo2bit(ProtocolInfo::PROTOCOL_419));
		$this->register(BlockIds::STRIPPED_WARPED_STEM, $logMetaTo2bit(ProtocolInfo::PROTOCOL_419));
		$this->register(BlockIds::WARPED_HYPHAE, $logMetaTo2bit(ProtocolInfo::PROTOCOL_419));
		$this->register(BlockIds::CRIMSON_HYPHAE, $logMetaTo2bit(ProtocolInfo::PROTOCOL_419));
		$this->register(BlockIds::STRIPPED_WARPED_HYPHAE, $logMetaTo2bit(ProtocolInfo::PROTOCOL_419));
		$this->register(BlockIds::STRIPPED_CRIMSON_HYPHAE, $logMetaTo2bit(ProtocolInfo::PROTOCOL_419));

		$this->register(BlockIds::MANGROVE_LOG, $logMetaTo2bit(ProtocolInfo::PROTOCOL_527));
		$this->register(BlockIds::MANGROVE_WOOD, $logMetaTo2bit(ProtocolInfo::PROTOCOL_527));
		$this->register(BlockIds::STRIPPED_MANGROVE_WOOD, $logMetaTo2bit(ProtocolInfo::PROTOCOL_527));
		$this->register(BlockIds::STRIPPED_MANGROVE_LOG, $logMetaTo2bit(ProtocolInfo::PROTOCOL_527));

		$this->register(BlockIds::BAMBOO_BLOCK, $logMetaTo2bit(ProtocolInfo::PROTOCOL_589));
		$this->register(BlockIds::STRIPPED_BAMBOO_BLOCK, $logMetaTo2bit(ProtocolInfo::PROTOCOL_589));

		$this->register(BlockIds::CHERRY_LOG, $logMetaTo2bit(ProtocolInfo::PROTOCOL_589));
		$this->register(BlockIds::CHERRY_WOOD, $logMetaTo2bit(ProtocolInfo::PROTOCOL_589));
		$this->register(BlockIds::STRIPPED_CHERRY_WOOD, $logMetaTo2bit(ProtocolInfo::PROTOCOL_589));
		$this->register(BlockIds::STRIPPED_CHERRY_LOG, $logMetaTo2bit(ProtocolInfo::PROTOCOL_589));

		$this->register(BlockIds::PALE_OAK_LOG, $logMetaTo2bit(ProtocolInfo::PROTOCOL_766));
		$this->register(BlockIds::PALE_OAK_WOOD, $logMetaTo2bit(ProtocolInfo::PROTOCOL_766));
		$this->register(BlockIds::STRIPPED_PALE_OAK_LOG, $logMetaTo2bit(ProtocolInfo::PROTOCOL_766));
		$this->register(BlockIds::STRIPPED_PALE_OAK_WOOD, $logMetaTo2bit(ProtocolInfo::PROTOCOL_766));

		$oldMetaWallConverter = fn(int $oldLegacyMeta) : BlockConvertor => new OldMetaWallConvertor($oldLegacyMeta);
		$newWallConverter = fn(int $convertProtocolVersion) : BlockConvertor => new NewWallConvertor($convertProtocolVersion);

		$this->register(BlockIds::COBBLESTONE_WALL, $oldMetaWallConverter(0));
		$this->register(BlockIds::MOSSY_COBBLESTONE_WALL, $oldMetaWallConverter(1));
		$this->register(BlockIds::GRANITE_WALL, $oldMetaWallConverter(2));
		$this->register(BlockIds::DIORITE_WALL, $oldMetaWallConverter(3));
		$this->register(BlockIds::ANDESITE_WALL, $oldMetaWallConverter(4));
		$this->register(BlockIds::SANDSTONE_WALL, $oldMetaWallConverter(5));
		$this->register(BlockIds::BRICK_WALL, $oldMetaWallConverter(6));
		$this->register(BlockIds::STONE_BRICK_WALL, $oldMetaWallConverter(7));
		$this->register(BlockIds::MOSSY_STONE_BRICK_WALL, $oldMetaWallConverter(8));
		$this->register(BlockIds::NETHER_BRICK_WALL, $oldMetaWallConverter(9));
		$this->register(BlockIds::END_STONE_BRICK_WALL, $oldMetaWallConverter(10));
		$this->register(BlockIds::PRISMARINE_WALL, $oldMetaWallConverter(11));
		$this->register(BlockIds::RED_SANDSTONE_WALL, $oldMetaWallConverter(12));
		$this->register(BlockIds::RED_NETHER_BRICK_WALL, $oldMetaWallConverter(13));

		$this->register(BlockIds::BLACKSTONE_WALL, $newWallConverter(ProtocolInfo::PROTOCOL_419));
		$this->register(BlockIds::POLISHED_BLACKSTONE_BRICK_WALL, $newWallConverter(ProtocolInfo::PROTOCOL_419));
		$this->register(BlockIds::POLISHED_BLACKSTONE_WALL, $newWallConverter(ProtocolInfo::PROTOCOL_419));

		$this->register(BlockIds::COBBLED_DEEPSLATE_WALL, $newWallConverter(ProtocolInfo::PROTOCOL_440));
		$this->register(BlockIds::POLISHED_DEEPSLATE_WALL, $newWallConverter(ProtocolInfo::PROTOCOL_440));
		$this->register(BlockIds::DEEPSLATE_TILE_WALL, $newWallConverter(ProtocolInfo::PROTOCOL_440));
		$this->register(BlockIds::DEEPSLATE_BRICK_WALL, $newWallConverter(ProtocolInfo::PROTOCOL_440));

		$this->register(BlockIds::MUD_BRICK_WALL, $newWallConverter(ProtocolInfo::PROTOCOL_527));

		$this->register(BlockIds::POLISHED_TUFF_WALL, $newWallConverter(ProtocolInfo::PROTOCOL_685));
		$this->register(BlockIds::TUFF_BRICK_WALL, $newWallConverter(ProtocolInfo::PROTOCOL_685));
		$this->register(BlockIds::TUFF_WALL, $newWallConverter(ProtocolInfo::PROTOCOL_685));

		$this->register(BlockIds::RESIN_BRICK_WALL, $newWallConverter(ProtocolInfo::PROTOCOL_766));

		$slabConvertor = fn(int $newBlockLegacyId, int $convertProtocolVersion) : BlockConvertor => new NewSlabConvertor($newBlockLegacyId, $convertProtocolVersion);

		$this->register(BlockIds::STONE_SLAB3, $slabConvertor(BlockIds::STONE_SLAB, ProtocolInfo::PROTOCOL_407));
		$this->register(BlockIds::STONE_SLAB4, $slabConvertor(BlockIds::STONE_SLAB, ProtocolInfo::PROTOCOL_407));

		$this->register(BlockIds::CRIMSON_SLAB, $slabConvertor(BlockIds::WOODEN_SLAB, ProtocolInfo::PROTOCOL_419));
		$this->register(BlockIds::WARPED_SLAB, $slabConvertor(BlockIds::WOODEN_SLAB, ProtocolInfo::PROTOCOL_419));
		$this->register(BlockIds::BLACKSTONE_SLAB, $slabConvertor(BlockIds::STONE_SLAB, ProtocolInfo::PROTOCOL_419));
		$this->register(BlockIds::POLISHED_BLACKSTONE_BRICK_SLAB, $slabConvertor(BlockIds::STONE_SLAB, ProtocolInfo::PROTOCOL_419));
		$this->register(BlockIds::POLISHED_BLACKSTONE_SLAB, $slabConvertor(BlockIds::STONE_SLAB, ProtocolInfo::PROTOCOL_419));

		$this->register(BlockIds::COBBLED_DEEPSLATE_SLAB, $slabConvertor(BlockIds::STONE_SLAB, ProtocolInfo::PROTOCOL_440));
		$this->register(BlockIds::POLISHED_DEEPSLATE_SLAB, $slabConvertor(BlockIds::STONE_SLAB, ProtocolInfo::PROTOCOL_440));
		$this->register(BlockIds::DEEPSLATE_TILE_SLAB, $slabConvertor(BlockIds::STONE_SLAB, ProtocolInfo::PROTOCOL_440));
		$this->register(BlockIds::DEEPSLATE_BRICK_SLAB, $slabConvertor(BlockIds::STONE_SLAB, ProtocolInfo::PROTOCOL_440));
		$this->register(BlockIds::CUT_COPPER_SLAB, $slabConvertor(BlockIds::STONE_SLAB, ProtocolInfo::PROTOCOL_440));
		$this->register(BlockIds::EXPOSED_CUT_COPPER_SLAB, $slabConvertor(BlockIds::STONE_SLAB, ProtocolInfo::PROTOCOL_440));
		$this->register(BlockIds::WEATHERED_CUT_COPPER_SLAB, $slabConvertor(BlockIds::STONE_SLAB, ProtocolInfo::PROTOCOL_440));
		$this->register(BlockIds::OXIDIZED_CUT_COPPER_SLAB, $slabConvertor(BlockIds::STONE_SLAB, ProtocolInfo::PROTOCOL_440));
		$this->register(BlockIds::WAXED_CUT_COPPER_SLAB, $slabConvertor(BlockIds::STONE_SLAB, ProtocolInfo::PROTOCOL_440));
		$this->register(BlockIds::WAXED_EXPOSED_CUT_COPPER_SLAB, $slabConvertor(BlockIds::STONE_SLAB, ProtocolInfo::PROTOCOL_440));
		$this->register(BlockIds::WAXED_WEATHERED_CUT_COPPER_SLAB, $slabConvertor(BlockIds::STONE_SLAB, ProtocolInfo::PROTOCOL_440));
		$this->register(BlockIds::WAXED_OXIDIZED_CUT_COPPER_SLAB, $slabConvertor(BlockIds::STONE_SLAB, ProtocolInfo::PROTOCOL_440));

		$this->register(BlockIds::MUD_BRICK_SLAB, $slabConvertor(BlockIds::STONE_SLAB, ProtocolInfo::PROTOCOL_527));
		$this->register(BlockIds::MANGROVE_SLAB, $slabConvertor(BlockIds::WOODEN_SLAB, ProtocolInfo::PROTOCOL_527));

		$this->register(BlockIds::BAMBOO_MOSAIC_SLAB, $slabConvertor(BlockIds::WOODEN_SLAB, ProtocolInfo::PROTOCOL_589));
		$this->register(BlockIds::BAMBOO_SLAB, $slabConvertor(BlockIds::WOODEN_SLAB, ProtocolInfo::PROTOCOL_589));
		$this->register(BlockIds::CHERRY_SLAB, $slabConvertor(BlockIds::WOODEN_SLAB, ProtocolInfo::PROTOCOL_589));

		$this->register(BlockIds::POLISHED_TUFF_SLAB, $slabConvertor(BlockIds::STONE_SLAB, ProtocolInfo::PROTOCOL_685));
		$this->register(BlockIds::TUFF_BRICK_SLAB, $slabConvertor(BlockIds::STONE_SLAB, ProtocolInfo::PROTOCOL_685));
		$this->register(BlockIds::TUFF_SLAB, $slabConvertor(BlockIds::STONE_SLAB, ProtocolInfo::PROTOCOL_685));

		$this->register(BlockIds::PALE_OAK_SLAB, $slabConvertor(BlockIds::WOODEN_SLAB, ProtocolInfo::PROTOCOL_766));
		$this->register(BlockIds::RESIN_BRICK_SLAB, $slabConvertor(BlockIds::STONE_SLAB, ProtocolInfo::PROTOCOL_766));

		$this->register(BlockIds::SKULL_BLOCK, new SkullConvertor());

		$this->register(BlockIds::LOG, new LogConvertor());
		$this->register(BlockIds::LOG2, new Log2Convertor());

		$this->register(BlockIds::MELON_STEM, new StemConvertor());
		$this->register(BlockIds::PUMPKIN_STEM, new StemConvertor());
	}

	public function register(int $blockLegacyId, BlockConvertor $converter, bool $override = false) : void {
		if (!$override && isset($this->blocks[$blockLegacyId])) {
			throw new RuntimeException("Trying to overwrite an already registered " . $blockLegacyId . " block protocol");
		}

		$this->blocks[$blockLegacyId] = $converter;
	}

	public function registerTransmittedMeta(int $blockLegacyId, int $targetLegacyId, int $minimalProtocol, bool $override = false) : void {
		$this->register($blockLegacyId, new TransmittedMetaConvertor($targetLegacyId, $minimalProtocol), $override);
	}

	public function registerStaticMeta(int $blockLegacyId, int $targetLegacyId, int $targetLegacyMeta, int $minimalProtocol, bool $override = false) : void {
		$this->register($blockLegacyId, new StaticMetaConvertor($targetLegacyId, $targetLegacyMeta, $minimalProtocol), $override);
	}

	public function registerNullableMeta(int $blockLegacyId, int $newBlockLegacyId, int $convertProtocolVersion, bool $override = false) : void {
		$this->register($blockLegacyId, new NullableMetaConvertor($newBlockLegacyId, $convertProtocolVersion), $override);
	}

	public function get(Block $block, int $protocolVersion) : ?Block {
		if (isset($this->caches[$protocolVersion][$block->getFullId()])) {
			$cacheBlock = $this->caches[$protocolVersion][$block->getFullId()];
			return $cacheBlock === null ? null : $cacheBlock;
		}

		$blockConvertorEntry = $this->blocks[$block->getId()] ?? null;
		if ($blockConvertorEntry === null) {
			$this->caches[$protocolVersion][$block->getFullId()] = null;
			return null;
		}

		$convertedBlock = $blockConvertorEntry->to($block, $protocolVersion);
		$this->caches[$protocolVersion][$block->getFullId()] = $convertedBlock;
		return $convertedBlock;
	}
}
