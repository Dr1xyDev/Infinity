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
use pocketmine\level\generator\vanilla\level\biome\BiomeIds;
use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\level\format\io\ChunkUtils;
use pocketmine\level\generator\vanilla\level\format\io\exception\CorruptedChunkException;
use pocketmine\level\generator\vanilla\level\format\SubChunk;
use pocketmine\level\generator\vanilla\nbt\BigEndianNBTStream;
use pocketmine\level\generator\vanilla\nbt\tag\ByteArrayTag;
use pocketmine\level\generator\vanilla\nbt\tag\ByteTag;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\nbt\tag\IntArrayTag;
use pocketmine\level\generator\vanilla\nbt\tag\ListTag;
use function array_values;
use function chr;
use function str_repeat;
use function unpack;
use function zlib_decode;

class McRegion extends RegionLevelProvider
{
	/**
	 * @throws CorruptedChunkException
	 */
	protected function deserializeChunk(string $data) : ?Chunk
	{
		$decompressed = @zlib_decode($data);
		if ($decompressed === false) {
			throw new CorruptedChunkException("Failed to decompress chunk data");
		}
		$nbt = new BigEndianNBTStream();
		$chunk = $nbt->read($decompressed);
		if (!($chunk instanceof CompoundTag) || !$chunk->hasTag("Level")) {
			throw new CorruptedChunkException("'Level' key is missing from chunk NBT");
		}

		$chunk = $chunk->getTag("Level");
		if (!($chunk instanceof CompoundTag)) {
			throw new CorruptedChunkException("'Level' key is missing from chunk NBT");
		}

		$legacyGeneratedTag = $chunk->getTag("TerrainGenerated");
		if ($legacyGeneratedTag instanceof ByteTag && $legacyGeneratedTag->getValue() === 0) {
			//In legacy PM before 3.0, PM used to save MCRegion chunks even when they weren't generated. In these cases
			//(we'll see them in old worlds), some of the tags which we expect to always be present, will be missing.
			//If TerrainGenerated (PM-specific tag from the olden days) is false, toss the chunk data and don't bother
			//trying to read it.
			return null;
		}
		$subChunks = [];
		$fullIds = self::readFixedSizeByteArray($chunk, "Blocks", 32768);
		$fullData = self::readFixedSizeByteArray($chunk, "Data", 16384);

		for ($y = 0; $y < 8; ++$y) {
			$subChunks[$y] = new SubChunk(Block::AIR << Block::INTERNAL_METADATA_BITS, [
				$this->palettizeLegacySubChunkFromColumn(
					$fullIds,
					$fullData,
					$y
				)]);
		}

		if (($biomeColorsTag = $chunk->getTag("BiomeColors")) instanceof IntArrayTag) {
			$biomeIds = ChunkUtils::convertBiomeColors($biomeColorsTag->getValue()); //Convert back to original format
		} elseif (($biomesTag = $chunk->getTag("Biomes")) instanceof ByteArrayTag) {
			$biomeIds = $biomesTag->getValue();
		} else {
			$biomeIds = str_repeat(chr(BiomeIds::OCEAN), 256);
		}

		$heightMap = [];
		if ($chunk->hasTag("HeightMap", ByteArrayTag::class)) {
			$heightMap = array_values(unpack("C*", $chunk->getByteArray("HeightMap")));
		} elseif ($chunk->hasTag("HeightMap", IntArrayTag::class)) {
			$heightMap = $chunk->getIntArray("HeightMap"); #blameshoghicp
		}

		$result = new Chunk(
			$chunk->getInt("xPos"),
			$chunk->getInt("zPos"),
			$subChunks,
			($entitiesTag = $chunk->getTag("Entities")) instanceof ListTag ? self::getCompoundList("Entities", $entitiesTag) : [],
			($tilesTag = $chunk->getTag("TileEntities")) instanceof ListTag ? self::getCompoundList("TileEntities", $tilesTag) : [],
			$biomeIds,
			$heightMap
		);
		$result->setLightPopulated($chunk->getByte("LightPopulated", 0) !== 0);
		$result->setPopulated($chunk->getByte("TerrainPopulated", 0) !== 0);
		$result->setGenerated(true);
		return $result;
	}

	protected static function getRegionFileExtension() : string
	{
		return "mcr";
	}

	/**
	 * Returns the storage version as per Minecraft PC world formats.
	 */
	public static function getPcWorldFormatVersion() : int
	{
		return 19132; //mcregion
	}

	public function getWorldHeight() : int
	{
		//TODO: add world height options
		return 128;
	}
}
