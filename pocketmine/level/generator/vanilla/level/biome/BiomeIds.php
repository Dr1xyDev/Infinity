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

namespace pocketmine\level\generator\vanilla\level\biome;

final class BiomeIds
{
	private function __construct()
	{
		//NOOP
	}

	public const int OCEAN = 0;
	public const int PLAINS = 1;
	public const int DESERT = 2;
	public const int EXTREME_HILLS = 3;
	public const int FOREST = 4;
	public const int TAIGA = 5;
	public const int SWAMPLAND = 6;
	public const int RIVER = 7;
	public const int HELL = 8;
	public const int SKY = 9;
	public const int FROZEN_OCEAN = 10;
	public const int FROZEN_RIVER = 11;
	public const int ICE_FLATS = 12, ICE_PLAINS = 12;
	public const int ICE_MOUNTAINS = 13;
	public const int MUSHROOM_ISLAND = 14;
	public const int MUSHROOM_ISLAND_SHORE = 15;
	public const int BEACHES = 16;
	public const int DESERT_HILLS = 17;
	public const int FOREST_HILLS = 18;
	public const int TAIGA_HILLS = 19;
	public const int SMALLER_EXTREME_HILLS = 20;
	public const int JUNGLE = 21;
	public const int JUNGLE_HILLS = 22;
	public const int JUNGLE_EDGE = 23;
	public const int DEEP_OCEAN = 24;
	public const int STONE_BEACH = 25;
	public const int COLD_BEACH = 26;
	public const int BIRCH_FOREST = 27;
	public const int BIRCH_FOREST_HILLS = 28;
	public const int ROOFED_FOREST = 29;
	public const int TAIGA_COLD = 30;
	public const int TAIGA_COLD_HILLS = 31;
	public const int REDWOOD_TAIGA = 32;
	public const int REDWOOD_TAIGA_HILLS = 33;
	public const int EXTREME_HILLS_WITH_TREES = 34;
	public const int SAVANNA = 35;
	public const int SAVANNA_ROCK = 36;
	public const int MESA = 37;
	public const int MESA_ROCK = 38;
	public const int MESA_CLEAR_ROCK = 39;

	public const int MUTATED_OFFSET = 128;
	public const int SUNFLOWER_PLAINS = 129;
	public const int DESERT_LAKES = 130;
	public const int GRAVELLY_MOUNTAINS = 131;
	public const int FLOWER_FOREST = 132;
	public const int TAIGA_MOUNTAINS = 133;
	public const int SWAMP_HILLS = 134;
	public const int ICE_SPIKES = 140;
	public const int MODIFIED_JUNGLE = 149;
	public const int MODIFIED_JUNGLE_EDGE = 151;
	public const int TALL_BIRCH_FOREST = 155;
	public const int TALL_BIRCH_HILLS = 156;
	public const int DARK_FOREST_HILLS = 157;
	public const int SNOWY_TAIGA_MOUNTAINS = 158;
	public const int GIANT_SPRUCE_TAIGA = 160;
	public const int GIANT_SPRUCE_TAIGA_HILLS = 161;
	public const int MODIFIED_GRAVELLY_MOUNTAINS = 162;
	public const int SHATTERED_SAVANNA = 163;
	public const int SHATTERED_SAVANNA_PLATEAU = 164;
	public const int ERODED_BADLANDS = 165;
	public const int MODIFIED_WOODED_BADLANDS_PLATEAU = 166;
	public const int MODIFIED_BADLANDS_PLATEAU = 167;

	public const int BIOMES_COUNT = 256;
}
