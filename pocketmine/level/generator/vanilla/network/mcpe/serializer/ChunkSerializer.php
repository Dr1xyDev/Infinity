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

namespace pocketmine\level\generator\vanilla\network\mcpe\serializer;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\level\biome\BiomeIds;
use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\level\format\SubChunkInterface;
use pocketmine\level\generator\vanilla\network\mcpe\convert\block\DynamicBlockStateResolver;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\DimensionIds;
use pocketmine\level\generator\vanilla\utils\AssumptionFailedError;
use pocketmine\level\generator\vanilla\utils\Binary;
use pocketmine\level\generator\vanilla\utils\BinaryStream;
use pocketmine\world\format\PalettedBlockArray;
use function array_flip;
use function chr;
use function count;
use function file_get_contents;
use function is_array;
use function json_decode;
use function ord;
use function pack;
use function str_repeat;

final class ChunkSerializer
{
	public static ?string $emptyBlockLegacyId = null;
	public static ?string $emptyBlockLegacyMeta = null;
	public static ?string $emptySkyLight = null;
	public static ?string $emptyBlockLight = null;

	private function __construct(){
		//NOOP
	}

	/**
	 * Returns the min/max subchunk index expected in the protocol.
	 * This has no relation to the world height supported by PM.
	 *
	 * @phpstan-param DimensionIds::* $dimensionId
	 * @return int[]
	 * @phpstan-return array{int, int}
	 */
	public static function getDimensionChunkBounds(int $dimensionId, int $playerProtocol) : array
	{
		if ($playerProtocol >= ProtocolInfo::PROTOCOL_475) {
			return match ($dimensionId) {
				DimensionIds::OVERWORLD => [-4, 19],
				DimensionIds::NETHER => [0, 7],
				DimensionIds::THE_END => [0, 15],
				default => throw new \InvalidArgumentException("Unknown dimension ID $dimensionId"),
			};
		}

		return match ($dimensionId) {
			DimensionIds::OVERWORLD, DimensionIds::THE_END => [0, 15],
			DimensionIds::NETHER => [0, 7],
			default => throw new \InvalidArgumentException("Unknown dimension ID $dimensionId"),
		};
	}

	/**
	 * Returns the number of subchunks that will be sent from the given chunk.
	 * Chunks are sent in a stack, so every chunk below the top non-empty one must be sent.
	 *
	 * @phpstan-param DimensionIds::* $dimensionId
	 */
	public static function getSubChunkCount(Chunk $chunk, int $dimensionId, int $playerProtocol) : int
	{
		//if the protocol world bounds ever exceed the PM supported bounds again in the future, we might need to
		//polyfill some stuff here
		[$minSubChunkIndex, $maxSubChunkIndex] = self::getDimensionChunkBounds($dimensionId, $playerProtocol);
		for ($y = $maxSubChunkIndex, $count = $maxSubChunkIndex - $minSubChunkIndex + 1; $y >= $minSubChunkIndex; --$y, --$count) {
			if ($chunk->getSubChunk($y)->isEmpty(false)) {
				continue;
			}
			return $count;
		}

		return 0;
	}

	/**
	 * Serializes the chunk for sending to players
	 *
	 * @param int[] $dynamicStates neighbour-dependent network states (1.26.50+), see DynamicBlockStateResolver::computeChunk()
	 */
	public static function serializeFullChunk(Chunk $chunk, \Closure $blockLegacyToRuntime, \Closure $biomeLegacyToRuntime, int $dimensionId, int $playerProtocol, array $dynamicStates = []) : string
	{
		$dynamicStatesBySubChunk = [];
		foreach ($dynamicStates as $key => $runtimeId) {
			$dynamicStatesBySubChunk[$key >> 12][$key & 0xfff] = $runtimeId;
		}

		$stream = new BinaryStream();

		$subChunkCount = self::getSubChunkCount($chunk, $dimensionId, $playerProtocol);
		if ($playerProtocol < ProtocolInfo::PROTOCOL_407) {
			$stream->putByte($subChunkCount);
		}

		$writtenCount = 0;

		[$minSubChunkIndex, $maxSubChunkIndex] = self::getDimensionChunkBounds($dimensionId, $playerProtocol);
		for ($y = $minSubChunkIndex; $writtenCount < $subChunkCount; ++$y, ++$writtenCount) {
			self::serializeSubChunk($chunk->getSubChunk($y), $blockLegacyToRuntime, $playerProtocol, $stream, $dynamicStatesBySubChunk[$y] ?? []);
		}

		if ($playerProtocol >= ProtocolInfo::PROTOCOL_475) {
			//TODO: right now we don't support 3D natively, so we just 3Dify our 2D biomes so they fill the column
			$encodedBiomePalette = self::networkSerializeBiomesAsPalette($chunk, $biomeLegacyToRuntime);
			for ($y = $minSubChunkIndex; $y <= $maxSubChunkIndex; ++$y) {
				$stream->put($encodedBiomePalette);
			}
		} else {
			if ($playerProtocol < ProtocolInfo::PROTOCOL_407) {
				$stream->put(pack("v*", ...$chunk->getHeightMapArray()));
			}

			$biomes = $chunk->getBiomeIdArray();
			for ($x = 0; $x < 16; ++$x) {
				for ($z = 0; $z < 16; ++$z) {
					$biomes[($z << 4) | $x] = chr($biomeLegacyToRuntime(ord($biomes[($z << 4) | $x])));
				}
			}
			$stream->put($biomes);
		}

		$stream->putByte(0); //border block array count
		//Border block entry format: 1 byte (4 bits X, 4 bits Z). These are however useless since they crash the regular client.

		if ($playerProtocol < ProtocolInfo::PROTOCOL_407) {
			$stream->putVarInt(0); // extraData (WTF)
		}

		return $stream->getBuffer();
	}

	/**
	 * @param int[] $dynamicStates (x << 8 | z << 4 | y) => network runtime ID overriding the first layer's mapping
	 */
	public static function serializeSubChunk(SubChunkInterface $subChunk, \Closure $blockLegacyToRuntime, int $playerProtocol, BinaryStream $stream, array $dynamicStates = []) : void{
		if ($playerProtocol < ProtocolInfo::PROTOCOL_407) {
			$stream->putByte(0); //storage version

			$idArray = self::$emptyBlockLegacyId ?? self::$emptyBlockLegacyId = str_repeat("\00", 4096);
			$metaArray = self::$emptyBlockLegacyMeta ?? self::$emptyBlockLegacyMeta = str_repeat("\00", 2048);

			$layers = $subChunk->getBlockLayers();
			if (count($layers) !== 0 && isset($layers[0])) {
				$mainLayer = $layers[0];

				for ($x = 0; $x < 16; ++$x) {
					for ($z = 0; $z < 16; ++$z) {
						for ($y = 0; $y < 16; ++$y) {
							$fullState = $blockLegacyToRuntime($mainLayer->get($x, $y, $z));

							[$legacyId, $legacyMeta] = [$fullState >> Block::INTERNAL_METADATA_BITS, $fullState & Block::INTERNAL_METADATA_MASK];
							if ($legacyId > 255) {
								$legacyId = BlockIds::INFO_UPDATE;
								$legacyMeta = 0;
							}

							$idArray[($x << 8) | ($z << 4) | $y] = chr($legacyId);
							$indexData = ($x << 7) | ($z << 3) | ($y >> 1);
							if (($y & 1) === 0) {
								$metaArray[$indexData] = chr((ord($metaArray[$indexData] ?? chr(0)) & 0xf0) | ($legacyMeta & 0x0f));
							} else {
								$metaArray[$indexData] = chr((($legacyMeta & 0x0f) << 4) | (ord($metaArray[$indexData] ?? chr(0)) & 0x0f));
							}
						}
					}
				}
			}

			$stream->put($idArray);
			$stream->put($metaArray);

			// HACK! No shadows for 1.1
			$stream->put(self::$emptySkyLight ?? self::$emptySkyLight = str_repeat("\xff", 2048)); // sky light
			$stream->put(self::$emptyBlockLight ?? self::$emptyBlockLight = str_repeat("\x00", 2048)); // block light
		} else {
			$stream->putByte(8); // storage version

			$blockLayers = $subChunk->getBlockLayers();
			$stream->putByte(count($blockLayers)); // layer count

			foreach ($blockLayers as $layerIndex => $blocks) {
				if ($layerIndex === 0 && count($dynamicStates) > 0) {
					//neighbour-dependent states have no legacy id:meta, so they're put into a copy of the layer as
					//already-translated runtime IDs, flagged with a marker bit
					$blocks = clone $blocks;
					foreach ($dynamicStates as $position => $runtimeId) {
						$blocks->set($position >> 8, $position & 0x0f, ($position >> 4) & 0x0f, DynamicBlockStateResolver::RUNTIME_ID_MARKER | $runtimeId);
					}
				}
				$bitsPerBlock = $blocks->getBitsPerBlock();
				$words = $blocks->getWordArray();
				$stream->putByte(($bitsPerBlock << 1) | 1);
				$stream->put($words);
				$palette = $blocks->getPalette();

				if ($bitsPerBlock !== 0) {
					//these LSHIFT by 1 uvarints are optimizations: the client expects zigzag varints here
					//but since we know they are always unsigned, we can avoid the extra fcall overhead of
					//zigzag and just shift directly.
					$stream->putUnsignedVarInt(count($palette) << 1); //yes, this is intentionally zigzag
				}

				foreach ($palette as $p) {
					$runtimeId = ($p & DynamicBlockStateResolver::RUNTIME_ID_MARKER) !== 0 ? $p & DynamicBlockStateResolver::RUNTIME_ID_MASK : $blockLegacyToRuntime($p);
					$stream->put(Binary::writeUnsignedVarInt($runtimeId << 1));
				}
			}
		}
	}

	private static function networkSerializeBiomesAsPalette(Chunk $chunk, \Closure $biomeLegacyToRuntime) : string{
		/** @var string[]|null $biomeIdMap */
		static $biomeIdMap = null;
		if ($biomeIdMap === null) {
			$biomeIdMapRaw = \pocketmine\level\generator\vanilla\utils\Filesystem::resourceGetContents(\pocketmine\BEDROCK_DATA_PATH . 'biome_id_map.json');
			if ($biomeIdMapRaw === false) {
				throw new AssumptionFailedError();
			}
			$biomeIdMapDecoded = json_decode($biomeIdMapRaw, true);
			if (!is_array($biomeIdMapDecoded)) {
				throw new AssumptionFailedError();
			}
			$biomeIdMap = array_flip($biomeIdMapDecoded);
		}
		$biomePalette = new PalettedBlockArray($chunk->getBiomeId(0, 0));
		for ($x = 0; $x < 16; ++$x) {
			for ($z = 0; $z < 16; ++$z) {
				$biomeId = $chunk->getBiomeId($x, $z);
				if (!isset($biomeIdMap[$biomeId])) {
					//make sure we aren't sending bogus biomes - the 1.18.0 client crashes if we do this
					$biomeId = BiomeIds::OCEAN;
				}
				for ($y = 0; $y < 16; ++$y) {
					$biomePalette->set($x, $y, $z, $biomeLegacyToRuntime($biomeId));
				}
			}
		}

		$stream = new BinaryStream();
		$biomePaletteBitsPerBlock = $biomePalette->getBitsPerBlock();
		$stream->putByte(($biomePaletteBitsPerBlock << 1) | 1); //the last bit is non-persistence (like for blocks), though it has no effect on biomes since they always use integer IDs
		$stream->put($biomePalette->getWordArray());

		//these LSHIFT by 1 uvarints are optimizations: the client expects zigzag varints here
		//but since we know they are always unsigned, we can avoid the extra fcall overhead of
		//zigzag and just shift directly.
		$biomePaletteArray = $biomePalette->getPalette();
		if($biomePaletteBitsPerBlock !== 0){
			$stream->putUnsignedVarInt(count($biomePaletteArray) << 1);
		}

		foreach($biomePaletteArray as $p){
			$stream->put(Binary::writeUnsignedVarInt($p << 1));
		}

		return $stream->getBuffer();
	}

}
