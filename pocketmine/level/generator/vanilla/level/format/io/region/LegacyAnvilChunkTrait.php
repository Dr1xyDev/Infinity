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

use pocketmine\level\generator\vanilla\level\biome\BiomeIds;
use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\level\format\io\ChunkUtils;
use pocketmine\level\generator\vanilla\level\format\io\exception\CorruptedChunkException;
use pocketmine\level\generator\vanilla\level\format\SubChunk;
use pocketmine\level\generator\vanilla\level\LevelException;
use pocketmine\level\generator\vanilla\nbt\BigEndianNBTStream;
use pocketmine\level\generator\vanilla\nbt\tag\ByteArrayTag;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\nbt\tag\IntArrayTag;
use pocketmine\level\generator\vanilla\nbt\tag\ListTag;
use UnexpectedValueException;
use function chr;
use function str_repeat;
use function zlib_decode;

/**
 * Trait containing I/O methods for handling legacy Anvil-style chunks.
 *
 * Motivation: In the future PMAnvil will become a legacy read-only format, but Anvil will continue to exist for the sake
 * of handling worlds in the PC 1.13 format. Thus, we don't want PMAnvil getting accidentally influenced by changes
 * happening to the underlying Anvil, because it only uses the legacy part.
 *
 * @internal
 */
trait LegacyAnvilChunkTrait
{
	/**
	 * @throws CorruptedChunkException
	 */
	protected function deserializeChunk(string $data) : ?Chunk
	{
		$decompressed = @zlib_decode($data);
		if ($decompressed === false) {
			throw new CorruptedChunkException("Failed to decompress chunk NBT");
		}
		$nbt = new BigEndianNBTStream();
		try {
			$chunk = $nbt->read($decompressed);
			/** @var CompoundTag $chunk */
		} catch (UnexpectedValueException $e) {
			throw new LevelException($e->getMessage(), 0, $e);
		}

		$chunk = $chunk->getTag("Level");
		if (!($chunk instanceof CompoundTag)) {
			throw new CorruptedChunkException("'Level' key is missing from chunk NBT");
		}

		$subChunks = [];
		$subChunksTag = $chunk->getListTag("Sections") ?? [];
		foreach ($subChunksTag as $subChunk) {
			if ($subChunk instanceof CompoundTag) {
				$subChunks[$subChunk->getByte("Y")] = $this->deserializeSubChunk($subChunk);
			}
		}

		if (($biomeColorsTag = $chunk->getTag("BiomeColors")) instanceof IntArrayTag) {
			$biomeIds = ChunkUtils::convertBiomeColors($biomeColorsTag->getValue()); //Convert back to original format
		} elseif (($biomesTag = $chunk->getTag("Biomes")) instanceof ByteArrayTag) {
			$biomeIds = $biomesTag->getValue();
		} else {
			$biomeIds = str_repeat(chr(BiomeIds::OCEAN), 256);
		}

		$result = new Chunk(
			$chunk->getInt("xPos"),
			$chunk->getInt("zPos"),
			$subChunks,
			($entitiesTag = $chunk->getTag("Entities")) instanceof ListTag ? self::getCompoundList("Entities", $entitiesTag) : [],
			($tilesTag = $chunk->getTag("TileEntities")) instanceof ListTag ? self::getCompoundList("TileEntities", $tilesTag) : [],
			$biomeIds,
			$chunk->getIntArray("HeightMap", [])
		);
		$result->setLightPopulated($chunk->getByte("LightPopulated", 0) !== 0);
		$result->setPopulated($chunk->getByte("TerrainPopulated", 0) !== 0);
		$result->setGenerated();
		return $result;
	}

	abstract protected function deserializeSubChunk(CompoundTag $subChunk) : SubChunk;

}
