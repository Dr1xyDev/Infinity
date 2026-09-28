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

namespace pocketmine\level\generator\vanilla\entity;

interface EntityIds
{
	public const CHICKEN = 10;
	public const COW = 11;
	public const PIG = 12;
	public const SHEEP = 13;
	public const WOLF = 14;
	public const VILLAGER = 15;
	public const MOOSHROOM = 16;
	public const SQUID = 17;
	public const RABBIT = 18;
	public const BAT = 19;
	public const IRON_GOLEM = 20;
	public const SNOW_GOLEM = 21;
	public const OCELOT = 22;
	public const HORSE = 23;
	public const DONKEY = 24;
	public const MULE = 25;
	public const SKELETON_HORSE = 26;
	public const ZOMBIE_HORSE = 27;
	public const POLAR_BEAR = 28;
	public const LLAMA = 29;
	public const PARROT = 30;
	public const DOLPHIN = 31;
	public const ZOMBIE = 32;
	public const CREEPER = 33;
	public const SKELETON = 34;
	public const SPIDER = 35;
	public const ZOMBIFIED_PIGLIN = 36, ZOMBIE_PIGMAN = 36;
	public const SLIME = 37;
	public const ENDERMAN = 38;
	public const SILVERFISH = 39;
	public const CAVE_SPIDER = 40;
	public const GHAST = 41;
	public const MAGMA_CUBE = 42;
	public const BLAZE = 43;
	public const ZOMBIE_VILLAGER = 44;
	public const WITCH = 45;
	public const STRAY = 46;
	public const HUSK = 47;
	public const WITHER_SKELETON = 48;
	public const GUARDIAN = 49;
	public const ELDER_GUARDIAN = 50;
	public const NPC = 51;
	public const WITHER = 52;
	public const ENDER_DRAGON = 53;
	public const SHULKER = 54;
	public const ENDERMITE = 55;
	public const AGENT = 56, LEARN_TO_CODE_MASCOT = 56;
	public const VINDICATOR = 57;
	public const PHANTOM = 58;
	public const RAVAGER = 59;

	public const ARMOR_STAND = 61;
	public const TRIPOD_CAMERA = 62;
	public const ITEM = 64;
	public const TNT = 65;
	public const FALLING_BLOCK = 66;
	// 67 moving block?
	public const EXPERIENCE_BOTTLE = 68, XP_BOTTLE = 68;
	public const EXPERIENCE_ORB = 69, XP_ORB = 69;
	public const EYE_OF_ENDER = 70, EYE_OF_ENDER_SIGNAL = 70;
	public const END_CRYSTAL = 71, ENDER_CRYSTAL = 71;
	public const FIREWORK_ROCKET = 72, FIREWORKS_ROCKET = 72;
	public const THROWN_TRIDENT = 73, TRIDENT = 73;
	public const TURTLE = 74;
	public const CAT = 75;
	public const SHULKER_BULLET = 76;
	public const FISHING_BOBBER = 77, FISHING_HOOK = 77;
	// 78 chalkboard?
	public const DRAGON_FIREBALL = 79;
	public const ARROW = 80;
	public const SNOWBALL = 81;
	public const EGG = 82;
	public const PAINTING = 83;
	public const MINECART = 84;
	public const FIREBALL = 85, LARGE_FIREBALL = 85;
	public const SPLASH_POTION = 86;
	public const ENDER_PEARL = 87;
	public const LEASH_KNOT = 88;
	public const WITHER_SKULL = 89;
	public const BOAT = 90;
	public const WITHER_SKULL_DANGEROUS = 91;
	public const LIGHTNING_BOLT = 93;
	public const SMALL_FIREBALL = 94;
	public const AREA_EFFECT_CLOUD = 95;
	public const HOPPER_MINECART = 96;
	public const TNT_MINECART = 97;
	public const CHEST_MINECART = 98;
	// 99 unknown
	public const COMMAND_BLOCK_MINECART = 100;
	public const LINGERING_POTION = 101;
	public const LLAMA_SPIT = 102;
	public const EVOKER_FANGS = 103, EVOCATION_FANG = 103;
	public const EVOKER = 104, EVOCATION_ILLAGER = 104;
	public const VEX = 105;
	public const ICE_BOMB = 106;
	public const BALLOON = 107;
	public const PUFFERFISH = 108;
	public const SALMON = 109;
	public const DROWNED = 110;
	public const TROPICALFISH = 111, TROPICAL_FISH = 111;
	public const COD = 112, FISH = 112;
	public const PANDA = 113;
	public const PILLAGER = 114;
	public const VILLAGER_V2 = 115;
	public const ZOMBIE_VILLAGER_V2 = 116;
	// 117 unknown
	public const WANDERING_TRADER = 118;
	// 119 unknown
	public const ELDER_GUARDIAN_GHOST = 120;
	public const FOX = 121;
	public const BEE = 122;
	public const PIGLIN = 123;
	public const HOGLIN = 124;
	public const STRIDER = 125;
	public const ZOGLIN = 126;
	public const PIGLIN_BRUTE = 127;
	public const GOAT = 128;
	public const GLOW_SQUID = 129;
	public const AXOLOTL = 130;
	public const WARDEN = 131;
	public const FROG = 132;
	public const TADPOLE = 133;
	public const ALLAY = 134;
	public const CAMEL = 138;
	public const SNIFFER = 139;
	public const BREEZE = 140;
	public const BREEZE_WIND_CHARGE = 141, BREEZE_WIND_CHARGE_PROJECTILE = 141;
	public const ARMADILLO = 142;
	public const WIND_CHARGE = 143, WIND_CHARGE_PROJECTILE = 143;
	public const BOGGED = 144;
	public const OMINOUS_ITEM_SPAWNER = 145;
	public const CREAKING = 146;
	public const HAPPY_GHAST = 147;
	public const COPPER_GOLEM = 148;
	public const NAUTILUS = 149;
	public const ZOMBIE_NAUTILUS = 150;
	public const PARCHED = 151;
	public const CAMEL_HUSK = 152;
	public const TRADER_LLAMA = 157;
	public const CHEST_BOAT = 218;
	public const PLAYER = 257;
}
