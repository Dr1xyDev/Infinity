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

namespace pocketmine\level\generator\vanilla\level\format\io\leveldb;

use pocketmine\VersionInfo;

final class ChunkDataKey
{
	private function __construct()
	{
		//NOOP
	}

	public const HEIGHTMAP_AND_3D_BIOMES = "\x2b";
	public const NEW_VERSION = "\x2c"; //since 1.16.100?
	public const HEIGHTMAP_AND_2D_BIOMES = "\x2d"; //obsolete since 1.18
	public const HEIGHTMAP_AND_2D_BIOME_COLORS = "\x2e"; //obsolete since 1.0
	public const SUBCHUNK = "\x2f";
	public const LEGACY_TERRAIN = "\x30"; //obsolete since 1.0
	public const BLOCK_ENTITIES = "\x31";
	public const ENTITIES = "\x32";
	public const PENDING_SCHEDULED_TICKS = "\x33";
	public const LEGACY_BLOCK_EXTRA_DATA = "\x34"; //obsolete since 1.2.13
	public const BIOME_STATES = "\x35"; //TODO: is this still applicable to 1.18.0?
	public const FINALIZATION = "\x36";
	public const CONVERTER_TAG = "\x37"; //???
	public const BORDER_BLOCKS = "\x38";
	public const HARDCODED_SPAWNERS = "\x39";
	public const PENDING_RANDOM_TICKS = "\x3a";
	public const XXHASH_CHECKSUMS = "\x3b"; //obsolete since 1.18
	public const GENERATION_SEED = "\x3c";
	public const GENERATED_BEFORE_CNC_BLENDING = "\x3d";

	public const OLD_VERSION = "\x76";

	public const PM_DATA_VERSION = VersionInfo::TAG_WORLD_DATA_VERSION;

}
