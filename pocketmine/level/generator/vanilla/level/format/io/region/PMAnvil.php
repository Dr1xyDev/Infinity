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

namespace pocketmine\level\generator\vanilla\level\format\io\region;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\level\format\SubChunk;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\utils\BinaryStream;
use pocketmine\world\format\PalettedBlockArray;

/**
 * This format is exactly the same as the PC Anvil format, with the only difference being that the stored data order
 * is XZY instead of YZX for more performance loading and saving worlds.
 */
class PMAnvil extends RegionLevelProvider
{
	use LegacyAnvilChunkTrait;

	protected function deserializeSubChunk(CompoundTag $subChunk) : SubChunk
	{
		if ($subChunk->hasTag("Data")) {
			return new SubChunk(BlockIds::AIR << Block::INTERNAL_METADATA_BITS, [$this->palettizeLegacySubChunkXZY(
				self::readFixedSizeByteArray($subChunk, "Blocks", 4096),
				self::readFixedSizeByteArray($subChunk, "Data", 2048)
			)]);
		} else {
			$stream = new BinaryStream($subChunk->getByteArray("Blocks"));
			[$emptyBlockId, $blockLayers] = $this->deserializeBlockLayers($stream);
			return new SubChunk($emptyBlockId, [$this->translatePalette($blockLayers[0] ?? new PalettedBlockArray($emptyBlockId))]);
		}
	}

	protected static function getRegionFileExtension() : string
	{
		return "mcapm";
	}

	protected static function getPcWorldFormatVersion() : int
	{
		return -1; //Not a PC format, only PocketMine-MP
	}

	public function getWorldHeight() : int
	{
		return 256;
	}
}
