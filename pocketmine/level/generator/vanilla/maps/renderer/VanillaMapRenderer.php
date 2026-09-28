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

namespace pocketmine\level\generator\vanilla\maps\renderer;

use pocketmine\level\generator\vanilla\block\Air;
use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\Liquid;
use pocketmine\level\generator\vanilla\block\Log;
use pocketmine\level\generator\vanilla\block\Log2;
use pocketmine\level\generator\vanilla\block\Planks;
use pocketmine\level\generator\vanilla\block\Prismarine;
use pocketmine\level\generator\vanilla\block\Stone;
use pocketmine\level\generator\vanilla\block\StoneSlab;
use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\maps\MapData;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\utils\Color;

use function floor;
use function max;
use function min;

class VanillaMapRenderer extends MapRenderer
{
	protected $currentCheckX = 0;

	public function initialize(MapData $mapData) : void
	{

	}

	public function onMapCreated(Player $player, MapData $mapData) : void
	{
		// TODO: make this async
		for ($i = 0; $i < 128; $i++) {
			$this->render($mapData, $player);
		}
	}

	/**
	 * Renders a map
	 */
	public function render(MapData $mapData, Player $player) : void
	{
		if ($mapData->getLevelName() === "") {
			$mapData->setLevelName($player->level->getFolderName());
		}

		if ($mapData->getLevelName() === $player->level->getFolderName() && $player->level->getDimension() === $mapData->getDimension() && !$mapData->isLocked()) {
			$realScale = 1 << $mapData->getScale();

			$centerX = $mapData->getCenterX();
			$centerZ = $mapData->getCenterZ();

			$playerMapX = (int) (floor($player->x - $centerX) / $realScale + 64);
			$playerMapY = (int) (floor($player->z - $centerZ) / $realScale + 64);
			$maxCheckDistance = 128 / $realScale;

			$world = $player->level;
			$air = new Air();

			$avgY = 0;

			for ($y = max(0, $playerMapY - $maxCheckDistance + 1); $y < min(128, $playerMapY + $maxCheckDistance); $y++) {
				$mapX = $this->currentCheckX;
				$mapY = $y;

				$distX = $mapX - $playerMapX;
				$distY = $mapY - $playerMapY;

				$isTooFar = $distX ** 2 + $distY ** 2 > ($maxCheckDistance - 2) ** 2;

				$worldX = ($centerX / $realScale + $mapX - 64) * $realScale;
				$worldZ = ($centerZ / $realScale + $mapY - 64) * $realScale;

				if ($world->isChunkLoaded($worldX >> Chunk::COORD_BIT_SIZE, $worldZ >> Chunk::COORD_BIT_SIZE)) {
					$liquidDepth = 0;
					$nextAvgY = 0;

					$chunk = $world->getChunk($worldX >> Chunk::COORD_BIT_SIZE, $worldZ >> Chunk::COORD_BIT_SIZE);
					$worldY = $chunk->getHeightMap($worldX & 15, $worldZ & 15) + 1;
					$block = clone $air;

					if ($worldY > 1) {
						while (true) {
							$worldY--;
							$block = $world->getBlockAt($worldX, $worldY, $worldZ);

							if ($block->getId() !== Block::AIR || $worldY <= 0) {
								break;
							}
						}
						if ($worldY > 0 && $block instanceof Liquid) {
							$attempt = 0;
							$worldY2 = $worldY - 1;

							while ($attempt++ <= 10) {
								$b = $world->getBlockAt($worldX, $worldY2--, $worldZ);
								$liquidDepth++;

								if ($worldY2 <= 0 || !($b instanceof Liquid)) {
									break;
								}
							}
						}

						$nextAvgY += (int) $worldY / (int) ($realScale * $realScale);
						$mapColor = self::getMapColorByBlock($block);
					} else {
						$mapColor = new Color(0, 0, 0);
					}

					$liquidDepth /= ($realScale * $realScale);
					$avgYDifference = ($nextAvgY - $avgY) * 4 / (int) ($realScale + 4) + ((int) ($mapX + $mapY & 1) - 0.5) * 0.4;
					$colorDepth = 1;

					if ($avgYDifference > 0.6) {
						$colorDepth = 2;
					}

					if ($avgYDifference < -0.6) {
						$colorDepth = 0;
					}

					if ($mapColor->getR() === 64 && $mapColor->getG() === 64 && $mapColor->getB() === 255) { // water color
						$avgYDifference = (int) $liquidDepth * 0.1 + (int) ($mapX + $mapY & 1) * 0.2;
						$colorDepth = 1;

						if ($avgYDifference < 0.5) {
							$colorDepth = 2;
						}

						if ($avgYDifference > 0.9) {
							$colorDepth = 0;
						}
					}

					$avgY = $nextAvgY;

					if (($distX ** 2 + $distY ** 2) < $maxCheckDistance ** 2 && (!$isTooFar || ($mapX + $mapY & 1) !== 0)) {
						$oldColor = $mapData->getColorAt($mapX, $mapY);
						$newColor = self::colorizeMapColor($mapColor, $colorDepth);

						if (!$oldColor->equals($newColor)) {
							$mapData->setColorAt($mapX, $mapY, $newColor);

							$mapData->updateTextureAt($mapX, $mapY);
						}
					}
				}
			}

			$this->currentCheckX++;
			$this->currentCheckX %= 128;
		}
	}

	public const COLOR_BLOCK_WHITE = 0;
	public const COLOR_BLOCK_ORANGE = 1;
	public const COLOR_BLOCK_MAGENTA = 2;
	public const COLOR_BLOCK_LIGHT_BLUE = 3;
	public const COLOR_BLOCK_YELLOW = 4;
	public const COLOR_BLOCK_LIME = 5;
	public const COLOR_BLOCK_PINK = 6;
	public const COLOR_BLOCK_GRAY = 7;
	public const COLOR_BLOCK_LIGHT_GRAY = 8;
	public const COLOR_BLOCK_CYAN = 9;
	public const COLOR_BLOCK_PURPLE = 10;
	public const COLOR_BLOCK_BLUE = 11;
	public const COLOR_BLOCK_BROWN = 12;
	public const COLOR_BLOCK_GREEN = 13;
	public const COLOR_BLOCK_RED = 14;
	public const COLOR_BLOCK_BLACK = 15;

	/**
	 * TODO: Separate map colors to blocks
	 */
	public static function getMapColorByBlock(Block $block) : Color
	{
		$meta = $block->getDamage();
		$id = $block->getId();
		if ($id === Block::AIR) {
			return new Color(0, 0, 0);
		} elseif ($id === Block::GRASS || $id === Block::SLIME_BLOCK) {
			return new Color(127, 178, 56);
		} elseif ($id === Block::SAND || $id === Block::SANDSTONE || $id === Block::SANDSTONE_STAIRS || ($id === Block::STONE_SLAB && ($meta & 0x07) == StoneSlab::SANDSTONE) || ($id === Block::DOUBLE_STONE_SLAB && $meta == StoneSlab::SANDSTONE) || $id === Block::GLOWSTONE || $id === Block::END_STONE || ($id === Block::PLANKS && $meta == Planks::BIRCH) || ($id === Block::LOG && ($meta & 0x03) == Log::BIRCH) || $id === Block::BIRCH_FENCE_GATE || ($id === Block::FENCE && $meta = Planks::BIRCH) || $id === Block::BIRCH_STAIRS || ($id === Block::WOODEN_SLAB && ($meta & 0x07) == Planks::BIRCH) || $id === Block::BONE_BLOCK || $id === Block::END_BRICKS) {
			return new Color(247, 233, 163);
		} elseif ($id === Block::BED_BLOCK || $id === Block::COBWEB) {
			return new Color(199, 199, 199);
		} elseif ($id === Block::LAVA || $id === Block::STILL_LAVA || $id === Block::FLOWING_LAVA || $id === Block::TNT || $id === Block::FIRE || $id === Block::REDSTONE_BLOCK) {
			return new Color(255, 0, 0);
		} elseif ($id === Block::ICE || $id === Block::PACKED_ICE || $id === Block::FROSTED_ICE) {
			return new Color(160, 160, 255);
		} elseif ($id === Block::IRON_BLOCK || $id === Block::IRON_DOOR_BLOCK || $id === Block::IRON_TRAPDOOR || $id === Block::IRON_BARS || $id === Block::BREWING_STAND_BLOCK || $id === Block::ANVIL || $id === Block::HEAVY_WEIGHTED_PRESSURE_PLATE) {
			return new Color(167, 167, 167);
		} elseif ($id === Block::SAPLING || $id === Block::LEAVES || $id === Block::LEAVES2 || $id === Block::TALL_GRASS || $id === Block::DEAD_BUSH || $id === Block::RED_FLOWER || $id === Block::DOUBLE_PLANT || $id === Block::BROWN_MUSHROOM || $id === Block::RED_MUSHROOM || $id === Block::WHEAT_BLOCK || $id === Block::CARROT_BLOCK || $id === Block::POTATO_BLOCK || $id === Block::BEETROOT_BLOCK || $id === Block::CACTUS || $id === Block::SUGARCANE_BLOCK || $id === Block::PUMPKIN_STEM || $id === Block::MELON_STEM || $id === Block::VINE || $id === Block::LILY_PAD) {
			return new Color(0, 124, 0);
		} elseif (($id === Block::WOOL && $meta == self::COLOR_BLOCK_WHITE) || ($id === Block::CARPET && $meta == self::COLOR_BLOCK_WHITE) || ($id === Block::STAINED_HARDENED_CLAY && $meta == self::COLOR_BLOCK_WHITE) || $id === Block::SNOW_LAYER || $id === Block::SNOW_BLOCK) {
			return new Color(255, 255, 255);
		} elseif ($id === Block::CLAY_BLOCK || $id === Block::MONSTER_EGG) {
			return new Color(164, 168, 184);
		} elseif ($id === Block::DIRT || $id === Block::FARMLAND || ($id === Block::STONE && $meta == Stone::GRANITE) || ($id === Block::STONE && $meta == Stone::POLISHED_GRANITE) || ($id === Block::SAND && $meta == 1) || $id === Block::RED_SANDSTONE || $id === Block::RED_SANDSTONE_STAIRS || ($id === Block::STONE_SLAB2 && ($meta & 0x07) == StoneSlab::RED_SANDSTONE) || ($id === Block::LOG && ($meta & 0x03) == Log::JUNGLE) || ($id === Block::PLANKS && $meta == Planks::JUNGLE) || $id === Block::JUNGLE_FENCE_GATE || ($id === Block::FENCE && $meta == Planks::JUNGLE) || $id === Block::JUNGLE_STAIRS || ($id === Block::WOODEN_SLAB && ($meta & 0x07) == Planks::JUNGLE)) {
			return new Color(151, 109, 77);
		} elseif ($id === Block::STONE || ($id === Block::STONE_SLAB && ($meta & 0x07) == StoneSlab::STONE) || $id === Block::COBBLESTONE || $id === Block::COBBLESTONE_STAIRS || ($id === Block::STONE_SLAB && ($meta & 0x07) == StoneSlab::COBBLESTONE) || $id === Block::COBBLESTONE_WALL || $id === Block::MOSS_STONE || ($id === Block::STONE && $meta == Stone::ANDESITE) || ($id === Block::STONE && $meta == Stone::POLISHED_ANDESITE) || $id === Block::BEDROCK || $id === Block::GOLD_ORE || $id === Block::IRON_ORE || $id === Block::COAL_ORE || $id === Block::LAPIS_ORE || $id === Block::DISPENSER || $id === Block::DROPPER || $id === Block::STICKY_PISTON || $id === Block::PISTON || $id === Block::PISTON_ARM_COLLISION || $id === Block::MOVINGBLOCK || $id === Block::MONSTER_SPAWNER || $id === Block::DIAMOND_ORE || $id === Block::FURNACE || $id === Block::STONE_PRESSURE_PLATE || $id === Block::REDSTONE_ORE || $id === Block::STONE_BRICK || $id === Block::STONE_BRICK_STAIRS || ($id === Block::STONE_SLAB && ($meta & 0x07) == StoneSlab::STONE_BRICK) || $id === Block::ENDER_CHEST || $id === Block::HOPPER_BLOCK || $id === Block::GRAVEL || $id === Block::OBSERVER) {
			return new Color(112, 112, 112);
		} elseif ($id === Block::WATER || $id === Block::STILL_WATER || $id === Block::FLOWING_WATER) {
			return new Color(64, 64, 255);
		} elseif (($id === Block::LOG && ($meta & 0x03) == Log::OAK) || ($id === Block::PLANKS && $meta == Planks::OAK) || ($id === Block::FENCE && $meta == Planks::OAK) || $id === Block::OAK_FENCE_GATE || $id === Block::OAK_STAIRS || ($id === Block::WOODEN_SLAB && ($meta & 0x07) == Planks::OAK) || $id === Block::NOTEBLOCK || $id === Block::BOOKSHELF || $id === Block::CHEST || $id === Block::TRAPPED_CHEST || $id === Block::CRAFTING_TABLE || $id === Block::WOODEN_DOOR_BLOCK || $id === Block::BIRCH_DOOR_BLOCK || $id === Block::SPRUCE_DOOR_BLOCK || $id === Block::JUNGLE_DOOR_BLOCK || $id === Block::ACACIA_DOOR_BLOCK || $id === Block::DARK_OAK_DOOR_BLOCK || $id === Block::SIGN_POST || $id === Block::WALL_SIGN || $id === Block::WOODEN_PRESSURE_PLATE || $id === Block::JUKEBOX || $id === Block::WOODEN_TRAPDOOR || $id === Block::BROWN_MUSHROOM_BLOCK || $id === Block::STANDING_BANNER || $id === Block::WALL_BANNER || $id === Block::DAYLIGHT_SENSOR || $id === Block::DAYLIGHT_SENSOR_INVERTED) {
			return new Color(143, 119, 72);
		} elseif ($id === Block::QUARTZ_BLOCK || ($id === Block::STONE_SLAB && ($meta & 0x07) == 6) || $id === Block::QUARTZ_STAIRS || ($id === Block::STONE && $meta == Stone::DIORITE) || ($id === Block::STONE && $meta == Stone::POLISHED_DIORITE) || $id === Block::SEA_LANTERN) {
			return new Color(255, 252, 245);
		} elseif (($id === Block::CONCRETE && $meta == self::COLOR_BLOCK_ORANGE) || ($id === Block::CONCRETE_POWDER && $meta == self::COLOR_BLOCK_ORANGE) || $id === Block::ORANGE_GLAZED_TERRACOTTA || ($id === Block::WOOL && $meta == self::COLOR_BLOCK_ORANGE) || ($id === Block::CARPET && $meta == self::COLOR_BLOCK_ORANGE) || ($id === Block::STAINED_HARDENED_CLAY && $meta == self::COLOR_BLOCK_ORANGE) || $id === Block::PUMPKIN || $id === Block::JACK_O_LANTERN || $id === Block::HARDENED_CLAY || ($id === Block::WOOD2 && ($meta & 0x03) == Log2::ACACIA) || ($id === Block::PLANKS && $meta == Planks::ACACIA) || ($id === Block::FENCE && $meta == Planks::ACACIA) || $id === Block::ACACIA_FENCE_GATE || $id === Block::ACACIA_STAIRS || ($id === Block::WOODEN_SLAB && ($meta & 0x07) == Planks::ACACIA)) {
			return new Color(216, 127, 51);
		} elseif (($id === Block::CONCRETE && $meta == self::COLOR_BLOCK_MAGENTA) || ($id === Block::CONCRETE_POWDER && $meta == self::COLOR_BLOCK_MAGENTA) || $id === Block::MAGENTA_GLAZED_TERRACOTTA || ($id === Block::WOOL && $meta == self::COLOR_BLOCK_MAGENTA) || ($id === Block::CARPET && $meta == self::COLOR_BLOCK_MAGENTA) || ($id === Block::STAINED_HARDENED_CLAY && $meta == self::COLOR_BLOCK_MAGENTA) || $id === Block::PURPUR_BLOCK || $id === Block::PURPUR_STAIRS || ($id === Block::STONE_SLAB2 && ($meta & 0x07) == Stone::PURPUR_BLOCK)) {
			return new Color(178, 76, 216);
		} elseif (($id === Block::CONCRETE && $meta == self::COLOR_BLOCK_LIGHT_BLUE) || ($id === Block::CONCRETE_POWDER && $meta == self::COLOR_BLOCK_LIGHT_BLUE) || $id === Block::LIGHT_BLUE_GLAZED_TERRACOTTA || ($id === Block::WOOL && $meta == self::COLOR_BLOCK_LIGHT_BLUE) || ($id === Block::CARPET && $meta == self::COLOR_BLOCK_LIGHT_BLUE) || ($id === Block::STAINED_HARDENED_CLAY && $meta == self::COLOR_BLOCK_LIGHT_BLUE)) {
			return new Color(102, 153, 216);
		} elseif (($id === Block::CONCRETE && $meta == self::COLOR_BLOCK_YELLOW) || ($id === Block::CONCRETE_POWDER && $meta == self::COLOR_BLOCK_YELLOW) || $id === Block::YELLOW_GLAZED_TERRACOTTA || ($id === Block::WOOL && $meta == self::COLOR_BLOCK_YELLOW) || ($id === Block::CARPET && $meta == self::COLOR_BLOCK_YELLOW) || ($id === Block::STAINED_HARDENED_CLAY && $meta == self::COLOR_BLOCK_YELLOW) || $id === Block::HAY_BALE || $id === Block::SPONGE) {
			return new Color(229, 229, 51);
		} elseif (($id === Block::CONCRETE && $meta == self::COLOR_BLOCK_LIME) || ($id === Block::CONCRETE_POWDER && $meta == self::COLOR_BLOCK_LIME) || $id === Block::LIME_GLAZED_TERRACOTTA || ($id === Block::WOOL && $meta == self::COLOR_BLOCK_LIME) || ($id === Block::CARPET && $meta == self::COLOR_BLOCK_LIME) || ($id === Block::STAINED_HARDENED_CLAY && $meta == self::COLOR_BLOCK_LIME) || $id === Block::MELON_BLOCK) {
			return new Color(229, 229, 51);
		} elseif (($id === Block::CONCRETE && $meta == self::COLOR_BLOCK_PINK) || ($id === Block::CONCRETE_POWDER && $meta == self::COLOR_BLOCK_PINK) || $id === Block::PINK_GLAZED_TERRACOTTA || ($id === Block::WOOL && $meta == self::COLOR_BLOCK_PINK) || ($id === Block::CARPET && $meta == self::COLOR_BLOCK_PINK) || ($id === Block::STAINED_HARDENED_CLAY && $meta == self::COLOR_BLOCK_PINK)) {
			return new Color(242, 127, 165);
		} elseif (($id === Block::CONCRETE && $meta == self::COLOR_BLOCK_GRAY) || ($id === Block::CONCRETE_POWDER && $meta == self::COLOR_BLOCK_GRAY) || $id === Block::GRAY_GLAZED_TERRACOTTA || ($id === Block::WOOL && $meta == self::COLOR_BLOCK_GRAY) || ($id === Block::CARPET && $meta == self::COLOR_BLOCK_GRAY) || ($id === Block::STAINED_HARDENED_CLAY && $meta == self::COLOR_BLOCK_GRAY) || $id === Block::CAULDRON_BLOCK) {
			return new Color(76, 76, 76);
		} elseif (($id === Block::CONCRETE && $meta == self::COLOR_BLOCK_LIGHT_GRAY) || ($id === Block::CONCRETE_POWDER && $meta == self::COLOR_BLOCK_LIGHT_GRAY) || ($id === Block::WOOL && $meta == self::COLOR_BLOCK_LIGHT_GRAY) || ($id === Block::CARPET && $meta == self::COLOR_BLOCK_LIGHT_GRAY) || ($id === Block::STAINED_HARDENED_CLAY && $meta == self::COLOR_BLOCK_LIGHT_GRAY) || $id === Block::STRUCTURE_BLOCK) {
			return new Color(153, 153, 153);
		} elseif (($id === Block::CONCRETE && $meta == self::COLOR_BLOCK_CYAN) || ($id === Block::CONCRETE_POWDER && $meta == self::COLOR_BLOCK_CYAN) || $id === Block::CYAN_GLAZED_TERRACOTTA || ($id === Block::WOOL && $meta == self::COLOR_BLOCK_CYAN) || ($id === Block::CARPET && $meta == self::COLOR_BLOCK_CYAN) || ($id === Block::STAINED_HARDENED_CLAY && $meta == self::COLOR_BLOCK_CYAN) || ($id === Block::PRISMARINE && $meta == Prismarine::NORMAL)) {
			return new Color(76, 127, 153);
		} elseif (($id === Block::CONCRETE && $meta == self::COLOR_BLOCK_PURPLE) || ($id === Block::CONCRETE_POWDER && $meta == self::COLOR_BLOCK_PURPLE) || $id === Block::PURPLE_GLAZED_TERRACOTTA || ($id === Block::WOOL && $meta == self::COLOR_BLOCK_PURPLE) || ($id === Block::CARPET && $meta == self::COLOR_BLOCK_PURPLE) || ($id === Block::STAINED_HARDENED_CLAY && $meta == self::COLOR_BLOCK_PURPLE) || $id === Block::MYCELIUM || $id === Block::REPEATING_COMMAND_BLOCK || $id === Block::CHORUS_PLANT || $id === Block::CHORUS_FLOWER) {
			return new Color(127, 63, 178);
		} elseif (($id === Block::CONCRETE && $meta == self::COLOR_BLOCK_BLUE) || ($id === Block::CONCRETE_POWDER && $meta == self::COLOR_BLOCK_BLUE) || $id === Block::BLUE_GLAZED_TERRACOTTA || ($id === Block::WOOL && $meta == self::COLOR_BLOCK_BLUE) || ($id === Block::CARPET && $meta == self::COLOR_BLOCK_BLUE) || ($id === Block::STAINED_HARDENED_CLAY && $meta == self::COLOR_BLOCK_BLUE)) {
			return new Color(51, 76, 178);
		} elseif (($id === Block::CONCRETE && $meta == self::COLOR_BLOCK_BROWN) || ($id === Block::CONCRETE_POWDER && $meta == self::COLOR_BLOCK_BROWN) || $id === Block::BROWN_GLAZED_TERRACOTTA || ($id === Block::WOOL && $meta == self::COLOR_BLOCK_BROWN) || ($id === Block::CARPET && $meta == self::COLOR_BLOCK_BROWN) || ($id === Block::STAINED_HARDENED_CLAY && $meta == self::COLOR_BLOCK_BROWN) || $id === Block::SOUL_SAND || ($id === Block::WOOD2 && ($meta & 0x03) == Log2::DARK_OAK) || ($id === Block::PLANKS && $meta == Planks::DARK_OAK) || ($id === Block::FENCE && $meta == Planks::DARK_OAK) || $id === Block::DARK_OAK_FENCE_GATE || $id === Block::DARK_OAK_STAIRS || ($id === Block::WOODEN_SLAB && ($meta & 0x07) == Planks::DARK_OAK) || $id === Block::COMMAND_BLOCK) {
			return new Color(102, 76, 51);
		} elseif (($id === Block::CONCRETE && $meta == self::COLOR_BLOCK_GREEN) || ($id === Block::CONCRETE_POWDER && $meta == self::COLOR_BLOCK_GREEN) || $id === Block::GREEN_GLAZED_TERRACOTTA || ($id === Block::WOOL && $meta == self::COLOR_BLOCK_GREEN) || ($id === Block::CARPET && $meta == self::COLOR_BLOCK_GREEN) || ($id === Block::STAINED_HARDENED_CLAY && $meta == self::COLOR_BLOCK_GREEN) || $id === Block::END_PORTAL_FRAME || $id === Block::CHAIN_COMMAND_BLOCK) {
			return new Color(102, 127, 51);
		} elseif (($id === Block::CONCRETE && $meta == self::COLOR_BLOCK_RED) || ($id === Block::CONCRETE_POWDER && $meta == self::COLOR_BLOCK_RED) || $id === Block::RED_GLAZED_TERRACOTTA || ($id === Block::WOOL && $meta == self::COLOR_BLOCK_RED) || ($id === Block::CARPET && $meta == self::COLOR_BLOCK_RED) || ($id === Block::STAINED_HARDENED_CLAY && $meta == self::COLOR_BLOCK_RED) || $id === Block::RED_MUSHROOM_BLOCK || $id === Block::BRICK_BLOCK || ($id === Block::STONE_SLAB && ($meta & 0x07) == 4) || $id === Block::BRICK_STAIRS || $id === Block::ENCHANTING_TABLE || $id === Block::NETHER_WART_BLOCK || $id === Block::NETHER_WART_PLANT) {
			return new Color(153, 51, 51);
		} elseif (($id === Block::WOOL && $meta == 0) || ($id === Block::CARPET && $meta == 0) || ($id === Block::STAINED_HARDENED_CLAY && $meta == 0) || $id === Block::DRAGON_EGG || $id === Block::COAL_BLOCK || $id === Block::OBSIDIAN || $id === Block::END_PORTAL) {
			return new Color(25, 25, 25);
		} elseif ($id === Block::GOLD_BLOCK || $id === Block::LIGHT_WEIGHTED_PRESSURE_PLATE) {
			return new Color(250, 238, 77);
		} elseif ($id === Block::DIAMOND_BLOCK || ($id === Block::PRISMARINE && $meta == Prismarine::DARK) || ($id === Block::PRISMARINE && $meta == Prismarine::BRICKS) || $id === Block::BEACON) {
			return new Color(92, 219, 213);
		} elseif ($id === Block::LAPIS_BLOCK) {
			return new Color(74, 128, 255);
		} elseif ($id === Block::EMERALD_BLOCK) {
			return new Color(0, 217, 58);
		} elseif ($id === Block::PODZOL || ($id === Block::LOG && ($meta & 0x03) == Log::SPRUCE) || ($id === Block::PLANKS && $meta == Planks::SPRUCE) || ($id === Block::FENCE && $meta == Planks::SPRUCE) || $id === Block::SPRUCE_FENCE_GATE || $id === Block::SPRUCE_STAIRS || ($id === Block::WOODEN_SLAB && ($meta & 0x07) == Planks::SPRUCE)) {
			return new Color(129, 86, 49);
		} elseif ($id === Block::NETHERRACK || $id === Block::NETHER_QUARTZ_ORE || $id === Block::NETHER_BRICK_FENCE || $id === Block::NETHER_BRICK_BLOCK || $id === Block::MAGMA || $id === Block::NETHER_BRICK_STAIRS || ($id === Block::STONE_SLAB && ($meta & 0x07) == 7)) {
			return new Color(112, 2, 0);
		} else {
			return new Color(0, 0, 0, 0);
		}
	}

	/**
	 * @param int $colorLevel colorization level
	 */
	public static function colorizeMapColor(Color $color, int $colorLevel) : Color
	{
		$colorDepth = match ($colorLevel) {
			3 => 135,
			2 => 255,
			0 => 180,
			default => 220,
		};

		$abgr = $color->toABGR();

		$r = (int) (($abgr >> 16 & 255) * $colorDepth / 255);
		$g = (int) (($abgr >> 8 & 255) * $colorDepth / 255);
		$b = (int) (($abgr & 255) * $colorDepth / 255);

		return Color::fromABGR(-16777216 | $r << 16 | $g << 8 | $b);
	}
}
