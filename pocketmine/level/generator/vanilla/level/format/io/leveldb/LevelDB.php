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

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\level\format\io\BaseLevelProvider;
use pocketmine\level\generator\vanilla\level\format\io\ChunkUtils;
use pocketmine\level\generator\vanilla\level\format\io\data\BedrockLevelData;
use pocketmine\level\generator\vanilla\level\format\io\exception\CorruptedChunkException;
use pocketmine\level\generator\vanilla\level\format\io\LevelData;
use pocketmine\level\generator\vanilla\level\format\io\leveldb\states\BlockStateData;
use pocketmine\level\generator\vanilla\level\format\io\leveldb\upgrade\BlockDataUpgrade;
use pocketmine\level\generator\vanilla\level\format\io\WritableLevelProvider;
use pocketmine\level\generator\vanilla\level\format\SubChunk;
use pocketmine\level\generator\vanilla\level\LevelCreationOptions;
use pocketmine\level\generator\vanilla\level\LevelException;
use pocketmine\level\generator\vanilla\nbt\LittleEndianNBTStream;
use pocketmine\level\generator\vanilla\nbt\NBT;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\nbt\tag\IntTag;
use pocketmine\level\generator\vanilla\nbt\tag\ShortTag;
use pocketmine\level\generator\vanilla\nbt\tag\StringTag;
use pocketmine\level\generator\vanilla\network\mcpe\convert\block\RuntimeBlockMapping;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\utils\Binary;
use pocketmine\level\generator\vanilla\utils\BinaryDataException;
use pocketmine\level\generator\vanilla\utils\BinaryStream;
use pocketmine\world\format\PalettedBlockArray;
use Symfony\Component\Filesystem\Path;
use function array_flip;
use function array_values;
use function chr;
use function count;
use function defined;
use function extension_loaded;
use function file_exists;
use function file_get_contents;
use function implode;
use function is_array;
use function is_dir;
use function json_decode;
use function mkdir;
use function ord;
use function str_repeat;
use function strlen;
use function substr;
use function trim;
use function unpack;
use const LEVELDB_ZLIB_RAW_COMPRESSION;
use const pocketmine\BEDROCK_DATA_PATH;

class LevelDB extends BaseLevelProvider implements WritableLevelProvider
{
	//According to Tomasso, these aren't supposed to be readable anymore. Thankfully he didn't change the readable ones...
	protected const TAG_DATA_2D = ChunkDataKey::HEIGHTMAP_AND_2D_BIOMES;
	protected const TAG_DATA_2D_LEGACY = ChunkDataKey::HEIGHTMAP_AND_2D_BIOME_COLORS;
	protected const TAG_SUBCHUNK_PREFIX = ChunkDataKey::SUBCHUNK;
	protected const TAG_LEGACY_TERRAIN = ChunkDataKey::LEGACY_TERRAIN;
	protected const TAG_BLOCK_ENTITY = ChunkDataKey::BLOCK_ENTITIES;
	protected const TAG_ENTITY = ChunkDataKey::ENTITIES;
	protected const TAG_PENDING_TICK = ChunkDataKey::PENDING_SCHEDULED_TICKS;
	protected const TAG_BLOCK_EXTRA_DATA = ChunkDataKey::LEGACY_BLOCK_EXTRA_DATA;
	protected const TAG_BIOME_STATE = ChunkDataKey::BIOME_STATES;
	protected const TAG_STATE_FINALISATION = ChunkDataKey::FINALIZATION;

	protected const TAG_BORDER_BLOCKS = ChunkDataKey::BORDER_BLOCKS;
	protected const TAG_HARDCODED_SPAWNERS = ChunkDataKey::HARDCODED_SPAWNERS;

	protected const FINALISATION_NEEDS_INSTATICKING = 0;
	protected const FINALISATION_NEEDS_POPULATION = 1;
	protected const FINALISATION_DONE = 2;

	protected const TAG_VERSION = ChunkDataKey::OLD_VERSION;

	protected const ENTRY_FLAT_WORLD_LAYERS = "game_flatworldlayers";

	protected const CURRENT_LEVEL_CHUNK_VERSION = ChunkVersion::v1_2_0; //yes, I know this is wrong, but it ensures vanilla auto-fixes stuff we currently don't
	protected const CURRENT_LEVEL_SUBCHUNK_VERSION = SubChunkVersion::PALETTED_MULTI;

	private const CAVES_CLIFFS_EXPERIMENTAL_SUBCHUNK_KEY_OFFSET = 4;

	private static ?array $cacheBlockIdMap = null;
	private static ?array $cacheBlockIdMapFlip = null;

	/** @var \LevelDB */
	protected $db;

	private static function checkForLevelDBExtension() : void
	{
		if (!extension_loaded('leveldb')) {
			throw new LevelException("The leveldb PHP extension is required to use this world format");
		}

		if (!defined('LEVELDB_ZLIB_RAW_COMPRESSION')) {
			throw new LevelException("Given version of php-leveldb doesn't support zlib raw compression");
		}
	}

	/**
	 * @throws \LevelDBException
	 */
	private static function createDB(string $path, int $compressionType = LEVELDB_ZLIB_RAW_COMPRESSION) : \LevelDB
	{
		return new \LevelDB(Path::join($path, "db"), [
			"compression" => $compressionType,
			"block_size" => 64 * 1024 //64KB, big enough for most chunks
		]);
	}

	public function __construct(string $path)
	{
		self::checkForLevelDBExtension();
		parent::__construct($path);

		try {
			$this->db = self::createDB($path, $this->levelData instanceof BedrockLevelData ? $this->levelData->getCompressionType() : LEVELDB_ZLIB_RAW_COMPRESSION);
		} catch (\LevelDBException $e) {
			//we can't tell the difference between errors caused by bad permissions and actual corruption :(
			throw new LevelException(trim($e->getMessage()), 0, $e);
		}
	}

	protected function loadLevelData() : LevelData
	{
		return new BedrockLevelData(Path::join($this->getPath(), "level.dat"));
	}

	public function getWorldHeight() : int
	{
		return 256;
	}

	public static function isValid(string $path) : bool
	{
		return file_exists(Path::join($path, "level.dat")) && is_dir(Path::join($path, "db"));
	}

	public static function generate(string $path, string $name, LevelCreationOptions $options) : void
	{
		self::checkForLevelDBExtension();

		$dbPath = Path::join($path, "db");
		if (!file_exists($dbPath)) {
			mkdir($dbPath, 0777, true);
		}

		BedrockLevelData::generate($path, $name, $options);
	}

	/**
	 * @throws CorruptedChunkException
	 */
	protected function deserializePaletted(BinaryStream $stream) : PalettedBlockArray
	{
		$bitsPerBlock = $stream->getByte() >> 1;

		try {
			$words = $stream->get(PalettedBlockArray::getExpectedWordArraySize($bitsPerBlock));
		} catch (\InvalidArgumentException $e) {
			throw new CorruptedChunkException("Failed to deserialize paletted storage: " . $e->getMessage(), 0, $e);
		}
		$nbt = new LittleEndianNBTStream();
		$blockDataUpdate = BlockDataUpgrade::getInstance();
		$runtimeBlockMapping = RuntimeBlockMapping::getInstance(ProtocolInfo::CURRENT_PROTOCOL);
		$palette = [];
		$blockDecodeErrors = [];

		if (self::$cacheBlockIdMap === null) {
			self::$cacheBlockIdMap = json_decode(\pocketmine\level\generator\vanilla\utils\Filesystem::resourceGetContents(BEDROCK_DATA_PATH . "block/block_id_map.json"), true);
		}

		if ($bitsPerBlock === 0) {
			$paletteSize = 1;
			/*
			 * Due to code copy-paste in a public plugin, some PM4 worlds have 0 bpb palettes with a length prefix.
			 * This is invalid and does not happen in vanilla.
			 * These palettes were accepted by PM4 despite being invalid, but PM5 considered them corrupt, causing loss
			 * of data. Since many users were affected by this, a workaround is therefore necessary to allow PM5 to read
			 * these worlds without data loss.
			 *
			 * References:
			 * - https://github.com/Refaltor77/CustomItemAPI/issues/68
			 * - https://github.com/pmmp/PocketMine-MP/issues/5911
			 */
			$offset = $stream->getOffset();
			$byte1 = $stream->getByte();
			$stream->setOffset($offset); //reset offset

			if ($byte1 !== NBT::TAG_Compound) { //normally the first byte would be the NBT of the blockstate
				$susLength = $stream->getLInt();
				if ($susLength !== 1) { //make sure the data isn't complete garbage
					throw new CorruptedChunkException("CustomItemAPI borked 0 bpb palette should always have a length of 1");
				}
			}
		} else {
			$paletteSize = $stream->getLInt();
		}

		for ($i = 0; $i < $paletteSize; ++$i) {
			try {
				$offset = $stream->getOffset();
				$blockStateNbt = $nbt->read($stream->getBuffer(), false, $offset);
				/** @var CompoundTag $blockStateNbt */
				$stream->setOffset($offset);
			} catch (\Exception $e) {
				throw new CorruptedChunkException("Invalid blockstate NBT at offset $i in paletted storage: " . $e->getMessage(), 0, $e);
			}

			if ($blockStateNbt->getTag("name") !== null && $blockStateNbt->getTag("val") !== null) {
				//Legacy (pre-1.13) blockstate - upgrade it to a version we understand
				$id = self::$cacheBlockIdMap[$blockStateNbt->getString("name")] ?? BlockIds::INFO_UPDATE;
				$data = $blockStateNbt->getShort("val");
				$palette[] = ($id << Block::INTERNAL_METADATA_BITS) | $data;
			} else {
				try {
					$blockStateData = $blockDataUpdate->upgrade(BlockStateData::fromNbt($blockStateNbt));
				} catch (\Exception $e) {
					//while not ideal, this is not a fatal error
					$blockDecodeErrors[] = "Palette offset $i / Upgrade error: " . $e->getMessage() . ", NBT: " . $blockStateNbt->toString();
					$palette[] = BlockIds::INFO_UPDATE << Block::INTERNAL_METADATA_BITS;
					continue;
				}

				try {
					$palette[] = $runtimeBlockMapping->fromRuntimeId($runtimeBlockMapping->fromNbtBlock($blockStateData->toVanillaNbt()));
				} catch (\Exception $e) {
					$blockDecodeErrors[] = "Palette offset $i / " . $e->getMessage();
					$palette[] = BlockIds::INFO_UPDATE << Block::INTERNAL_METADATA_BITS;
				}
			}
		}

		if (count($blockDecodeErrors) > 0) {
			\GlobalLogger::get()->error("Errors decoding blocks:\n - " . implode("\n - ", $blockDecodeErrors));
		}

		//TODO: exceptions
		return PalettedBlockArray::fromData($bitsPerBlock, $words, $palette);
	}

	/**
	 * @phpstan-param-out int $x
	 * @phpstan-param-out int $y
	 * @phpstan-param-out int $z
	 */
	protected static function deserializeExtraDataKey(int $chunkVersion, int $key, ?int &$x, ?int &$y, ?int &$z) : void
	{
		if ($chunkVersion >= ChunkVersion::v1_0_0) {
			$x = ($key >> 12) & 0xf;
			$z = ($key >> 8) & 0xf;
			$y = $key & 0xff;
		} else { //pre-1.0, 7 bits were used because the build height limit was lower
			$x = ($key >> 11) & 0xf;
			$z = ($key >> 7) & 0xf;
			$y = $key & 0x7f;
		}
	}

	/**
	 * @return PalettedBlockArray[]
	 */
	protected function deserializeLegacyExtraData(string $index, int $chunkVersion) : array
	{
		if (($extraRawData = $this->db->get($index . ChunkDataKey::LEGACY_BLOCK_EXTRA_DATA)) === false || $extraRawData === "") {
			return [];
		}

		/** @var PalettedBlockArray[] $extraDataLayers */
		$extraDataLayers = [];
		$binaryStream = new BinaryStream($extraRawData);
		$count = $binaryStream->getLInt();
		for ($i = 0; $i < $count; ++$i) {
			$key = $binaryStream->getLInt();
			$value = $binaryStream->getLShort();

			self::deserializeExtraDataKey($chunkVersion, $key, $x, $fullY, $z);

			$ySub = ($fullY >> SubChunk::COORD_BIT_SIZE);
			$y = $key & SubChunk::COORD_MASK;

			$blockId = $value & 0xff;
			$blockData = ($value >> 8) & 0xf;
			if (!isset($extraDataLayers[$ySub])) {
				$extraDataLayers[$ySub] = new PalettedBlockArray(Block::AIR << Block::INTERNAL_METADATA_BITS);
			}
			$extraDataLayers[$ySub]->set($x, $y, $z, ($blockId << Block::INTERNAL_METADATA_BITS) | $blockData);
		}

		return $extraDataLayers;
	}

	private function readVersion(int $chunkX, int $chunkZ) : ?int
	{
		$index = self::chunkIndex($chunkX, $chunkZ);
		$chunkVersionRaw = $this->db->get($index . ChunkDataKey::NEW_VERSION);
		if ($chunkVersionRaw === false) {
			$chunkVersionRaw = $this->db->get($index . ChunkDataKey::OLD_VERSION);
			if ($chunkVersionRaw === false) {
				return null;
			}
		}

		return ord($chunkVersionRaw);
	}

	private static function hasOffsetCavesAndCliffsSubChunks(int $chunkVersion) : bool
	{
		return $chunkVersion >= ChunkVersion::v1_16_220_50_unused && $chunkVersion <= ChunkVersion::v1_16_230_50_unused;
	}

	/**
	 * @throws CorruptedChunkException
	 */
	public function loadChunk(int $chunkX, int $chunkZ) : ?Chunk
	{
		$index = LevelDB::chunkIndex($chunkX, $chunkZ);

		$chunkVersion = $this->readVersion($chunkX, $chunkZ);
		if ($chunkVersion === null) {
			//TODO: this might be a slightly-corrupted chunk with a missing version field
			return null;
		}

		/** @var SubChunk[] $subChunks */
		$subChunks = [];

		/** @var int[] $heightMap */
		$heightMap = [];
		$biomeIds = "";

		$lightPopulated = true;

		$hasBeenUpgraded = $chunkVersion < self::CURRENT_LEVEL_CHUNK_VERSION;

		switch ($chunkVersion) {
			case ChunkVersion::v1_21_40:
				//TODO: BiomeStates became shorts instead of bytes
			case ChunkVersion::v1_18_30:
			case ChunkVersion::v1_18_0_25_beta:
			case ChunkVersion::v1_18_0_24_unused:
			case ChunkVersion::v1_18_0_24_beta:
			case ChunkVersion::v1_18_0_22_unused:
			case ChunkVersion::v1_18_0_22_beta:
			case ChunkVersion::v1_18_0_20_unused:
			case ChunkVersion::v1_18_0_20_beta:
			case ChunkVersion::v1_17_40_unused:
			case ChunkVersion::v1_17_40_20_beta_experimental_caves_cliffs:
			case ChunkVersion::v1_17_30_25_unused:
			case ChunkVersion::v1_17_30_25_beta_experimental_caves_cliffs:
			case ChunkVersion::v1_17_30_23_unused:
			case ChunkVersion::v1_17_30_23_beta_experimental_caves_cliffs:
			case ChunkVersion::v1_16_230_50_unused:
			case ChunkVersion::v1_16_230_50_beta_experimental_caves_cliffs:
			case ChunkVersion::v1_16_220_50_unused:
			case ChunkVersion::v1_16_220_50_beta_experimental_caves_cliffs:
			case ChunkVersion::v1_16_210:
			case ChunkVersion::v1_16_100_57_beta:
			case ChunkVersion::v1_16_100_52_beta:
			case ChunkVersion::v1_16_0:
			case ChunkVersion::v1_16_0_51_beta:
				//TODO: check walls
			case ChunkVersion::v1_12_0_unused2:
			case ChunkVersion::v1_12_0_unused1:
			case ChunkVersion::v1_12_0_4_beta:
			case ChunkVersion::v1_11_1:
			case ChunkVersion::v1_11_0_4_beta:
			case ChunkVersion::v1_11_0_3_beta:
			case ChunkVersion::v1_11_0_1_beta:
			case ChunkVersion::v1_9_0:
			case ChunkVersion::v1_8_0:
			case ChunkVersion::v1_2_13:
			case ChunkVersion::v1_2_0:
			case ChunkVersion::v1_2_0_2_beta:
			case ChunkVersion::v1_1_0_converted_from_console:
			case ChunkVersion::v1_1_0:
				//TODO: check beds
			case ChunkVersion::v1_0_0:
				$convertedLegacyExtraData = $this->deserializeLegacyExtraData($index, $chunkVersion);

				$subChunkKeyOffset = self::hasOffsetCavesAndCliffsSubChunks($chunkVersion) ? self::CAVES_CLIFFS_EXPERIMENTAL_SUBCHUNK_KEY_OFFSET : 0;
				for ($y = 0; $y < Chunk::MAX_SUBCHUNKS; ++$y) {
					if (($data = $this->db->get($index . ChunkDataKey::SUBCHUNK . chr($y + $subChunkKeyOffset))) === false) {
						continue;
					}

					$binaryStream = new BinaryStream($data);
					if ($binaryStream->feof()) {
						throw new CorruptedChunkException("Unexpected empty data for subchunk $y");
					}
					$subChunkVersion = $binaryStream->getByte();
					if ($subChunkVersion < self::CURRENT_LEVEL_SUBCHUNK_VERSION) {
						$hasBeenUpgraded = true;
					}

					switch ($subChunkVersion) {
						case SubChunkVersion::CLASSIC:
						case SubChunkVersion::CLASSIC_BUG_2: //these are all identical to version 0, but vanilla respects these so we should also
						case SubChunkVersion::CLASSIC_BUG_3:
						case SubChunkVersion::CLASSIC_BUG_4:
						case SubChunkVersion::CLASSIC_BUG_5:
						case SubChunkVersion::CLASSIC_BUG_6:
						case SubChunkVersion::CLASSIC_BUG_7:
							try {
								$blocks = $binaryStream->get(4096);
								$blockData = $binaryStream->get(2048);

								if ($chunkVersion < ChunkVersion::v1_1_0) {
									$blockSkyLight = $binaryStream->get(2048);
									$blockLight = $binaryStream->get(2048);
									$hasBeenUpgraded = true; //drop saved light
								} else {
									//Mojang didn't bother changing the subchunk version when they stopped saving sky light -_-
									$blockSkyLight = "";
									$blockLight = "";
									$lightPopulated = false;
								}
							} catch (BinaryDataException $e) {
								throw new CorruptedChunkException($e->getMessage(), 0, $e);
							}

							$storages = [$this->palettizeLegacySubChunkXZY($blocks, $blockData)];
							if (isset($convertedLegacyExtraData[$y])) {
								$storages[] = $convertedLegacyExtraData[$y];
							}

							$subChunks[$y] = new SubChunk(Block::AIR << Block::INTERNAL_METADATA_BITS, $storages, $blockSkyLight, $blockLight);
							break;
						case SubChunkVersion::PALETTED_SINGLE:
							$storages = [$this->deserializePaletted($binaryStream)];
							if (isset($convertedLegacyExtraData[$y])) {
								$storages[] = $convertedLegacyExtraData[$y];
							}
							$subChunks[$y] = new SubChunk(Block::AIR << Block::INTERNAL_METADATA_BITS, $storages);
							break;
						case SubChunkVersion::PALETTED_MULTI:
						case SubChunkVersion::PALETTED_MULTI_WITH_OFFSET:
							//legacy extradata layers intentionally ignored because they aren't supposed to exist in v8

							$storageCount = $binaryStream->getByte();
							if ($subChunkVersion >= SubChunkVersion::PALETTED_MULTI_WITH_OFFSET) {
								//height ignored; this seems pointless since this is already in the key anyway
								$binaryStream->getByte();
							}

							if ($storageCount > 0) {
								$storages = [];

								for ($k = 0; $k < $storageCount; ++$k) {
									$storages[] = $this->deserializePaletted($binaryStream);
								}
								$subChunks[$y] = new SubChunk(Block::AIR << Block::INTERNAL_METADATA_BITS, $storages);
							}
							break;
						default:
							//TODO: set chunks read-only so the version on disk doesn't get overwritten
							throw new CorruptedChunkException("don't know how to decode LevelDB subchunk format version $subChunkVersion");
					}
				}

				if (($maps2d = $this->db->get($index . ChunkDataKey::HEIGHTMAP_AND_2D_BIOMES)) !== false) {
					$binaryStream = new BinaryStream($maps2d);

					try {
						$heightMap = array_values(unpack("v*", $binaryStream->get(512)));
						$biomeIds = $binaryStream->get(256);
					} catch (BinaryDataException $e) {
						throw new CorruptedChunkException($e->getMessage(), 0, $e);
					}
				}

				break;
			case ChunkVersion::v0_9_5:
			case ChunkVersion::v0_9_2:
			case ChunkVersion::v0_9_0:
				$convertedLegacyExtraData = $this->deserializeLegacyExtraData($index, $chunkVersion);

				$legacyTerrain = $this->db->get($index . ChunkDataKey::LEGACY_TERRAIN);
				if ($legacyTerrain === false) {
					throw new CorruptedChunkException("Missing expected LEGACY_TERRAIN tag for format version $chunkVersion");
				}
				$binaryStream = new BinaryStream($legacyTerrain);
				try {
					$fullIds = $binaryStream->get(32768);
					$fullData = $binaryStream->get(16384);
					$binaryStream->get(32768); //legacy light info, discard it
				} catch (BinaryDataException $e) {
					throw new CorruptedChunkException($e->getMessage(), 0, $e);
				}

				for ($yy = 0; $yy < 8; ++$yy) {
					$storages = [$this->palettizeLegacySubChunkFromColumn($fullIds, $fullData, $yy)];
					if (isset($convertedLegacyExtraData[$yy])) {
						$storages[] = $convertedLegacyExtraData[$yy];
					}
					$subChunks[$yy] = new SubChunk(Block::AIR << Block::INTERNAL_METADATA_BITS, $storages);
				}

				try {
					$heightMap = array_values(unpack("C*", $binaryStream->get(256)));
					$biomeIds = ChunkUtils::convertBiomeColors(array_values(unpack("N*", $binaryStream->get(1024))));
				} catch (BinaryDataException $e) {
					throw new CorruptedChunkException($e->getMessage(), 0, $e);
				}
				break;
			default:
				//TODO: set chunks read-only so the version on disk doesn't get overwritten
				throw new CorruptedChunkException("don't know how to decode chunk format version $chunkVersion");
		}

		$nbt = new LittleEndianNBTStream();

		/** @var CompoundTag[] $entities */
		$entities = [];
		if (($entityData = $this->db->get($index . self::TAG_ENTITY)) !== false && $entityData !== "") {
			$entityTags = $nbt->read($entityData, true);
			foreach ((is_array($entityTags) ? $entityTags : [$entityTags]) as $entityTag) {
				if (!($entityTag instanceof CompoundTag)) {
					throw new CorruptedChunkException("Entity root tag should be TAG_Compound");
				}
				if ($entityTag->hasTag("id", IntTag::class)) {
					$entityTag->setInt("id", $entityTag->getInt("id") & 0xff); //remove type flags - TODO: use these instead of removing them)
				}
				$entities[] = $entityTag;
			}
		}

		/** @var CompoundTag[] $tiles */
		$tiles = [];
		if (($tileData = $this->db->get($index . self::TAG_BLOCK_ENTITY)) !== false && $tileData !== "") {
			$tileTags = $nbt->read($tileData, true);
			foreach ((is_array($tileTags) ? $tileTags : [$tileTags]) as $tileTag) {
				if (!($tileTag instanceof CompoundTag)) {
					throw new CorruptedChunkException("Tile root tag should be TAG_Compound");
				}
				$tiles[] = $tileTag;
			}
		}

		$finalisationChr = $this->db->get($index . ChunkDataKey::FINALIZATION);
		if($finalisationChr !== false){
			$finalisation = ord($finalisationChr);
			$terrainPopulated = $finalisation === self::FINALISATION_DONE;
		}else{ //older versions didn't have this tag
			$terrainPopulated = true;
		}

		$chunk = new Chunk(
			$chunkX,
			$chunkZ,
			$subChunks,
			$entities,
			$tiles,
			$biomeIds,
			$heightMap
		);

		//TODO: tile ticks, biome states (?)

		$chunk->setGenerated(true);
		$chunk->setPopulated($terrainPopulated);
		$chunk->setLightPopulated($lightPopulated);
		$chunk->setChanged($hasBeenUpgraded); //trigger rewriting chunk to disk if it was converted from an older format

		return $chunk;
	}

	public function saveChunk(Chunk $chunk) : void
	{
		$chunkX = $chunk->getX();
		$chunkZ = $chunk->getZ();

		if (self::$cacheBlockIdMapFlip === null) {
			self::$cacheBlockIdMapFlip = array_flip(json_decode(\pocketmine\level\generator\vanilla\utils\Filesystem::resourceGetContents(BEDROCK_DATA_PATH . "block/block_id_map.json"), true));
		}
		$index = LevelDB::chunkIndex($chunkX, $chunkZ);

		$write = new \LevelDBWriteBatch();
		$write->put($index . ChunkDataKey::OLD_VERSION, chr(self::CURRENT_LEVEL_CHUNK_VERSION));

		$subChunks = $chunk->getSubChunks();
		foreach ($subChunks as $y => $subChunk) {
			$key = $index . ChunkDataKey::SUBCHUNK . chr($y);
			if ($subChunk->isEmpty(false)) { //MCPE doesn't save light anymore as of 1.1
				$write->delete($key);
			} else {
				$subStream = new BinaryStream();
				$subStream->putByte(self::CURRENT_LEVEL_SUBCHUNK_VERSION);

				$layers = $subChunk->getBlockLayers();
				$subStream->putByte(count($layers));
				foreach ($layers as $blocks) {
					if ($blocks->getBitsPerBlock() !== 0) {
						$subStream->putByte($blocks->getBitsPerBlock() << 1);
						$subStream->put($blocks->getWordArray());
					} else {
						//TODO: we use these in-memory, but they aren't supported on disk by the game yet
						//polyfill them with a zero'd 1-bpb instead
						$subStream->putByte(1 << 1);
						$subStream->put(str_repeat("\x00", PalettedBlockArray::getExpectedWordArraySize(1)));
					}

					$palette = $blocks->getPalette();
					$subStream->putLInt(count($palette));
					$tags = [];
					foreach ($palette as $p) {
						$tags[] = new CompoundTag("", [
							new StringTag("name", self::$cacheBlockIdMapFlip[$p >> Block::INTERNAL_METADATA_BITS] ?? "minecraft:info_update"),
							new IntTag("oldid", $p >> Block::INTERNAL_METADATA_BITS), //PM only (debugging), vanilla doesn't have this
							new ShortTag("val", $p & Block::INTERNAL_METADATA_MASK),
						]);
					}

					$subStream->put((new LittleEndianNBTStream())->write($tags));
				}

				$write->put($key, $subStream->getBuffer());
			}
		}

		$write->put($index . ChunkDataKey::HEIGHTMAP_AND_2D_BIOMES, str_repeat("\x00", 512) . $chunk->getBiomeIdArray());

		//TODO: use this properly
		$write->put($index . ChunkDataKey::FINALIZATION, chr($chunk->isPopulated() ? self::FINALISATION_DONE : self::FINALISATION_NEEDS_POPULATION));

		/** @var CompoundTag[] $tiles */
		$tiles = [];
		foreach ($chunk->getTiles() as $tile) {
			$tiles[] = $tile->saveNBT();
		}
		$this->writeTags($tiles, $index . ChunkDataKey::BLOCK_ENTITIES, $write);

		/** @var CompoundTag[] $entities */
		$entities = [];
		foreach ($chunk->getSavableEntities() as $entity) {
			$entity->saveNBT();
			$entities[] = $entity->namedtag;
		}
		$this->writeTags($entities, $index . ChunkDataKey::ENTITIES, $write);

		$write->delete($index . ChunkDataKey::HEIGHTMAP_AND_2D_BIOME_COLORS);
		$write->delete($index . ChunkDataKey::LEGACY_TERRAIN);

		$this->db->write($write);
	}

	/**
	 * @param CompoundTag[] $targets
	 */
	private function writeTags(array $targets, string $index, \LevelDBWriteBatch $write) : void
	{
		if (count($targets) > 0) {
			$nbt = new LittleEndianNBTStream();
			$write->put($index, $nbt->write($targets));
		} else {
			$write->delete($index);
		}
	}

	public function getDatabase() : \LevelDB
	{
		return $this->db;
	}

	public static function chunkIndex(int $chunkX, int $chunkZ) : string
	{
		return Binary::writeLInt($chunkX) . Binary::writeLInt($chunkZ);
	}

	public function doGarbageCollection() : void
	{

	}

	public function close() : void
	{
		unset($this->db);
	}

	public function getAllChunks(bool $skipCorrupted = false, ?\Logger $logger = null) : \Generator
	{
		foreach ($this->db->getIterator() as $key => $_) {
			if (strlen($key) === 9 && ($key[8] === ChunkDataKey::NEW_VERSION || $key[8] === ChunkDataKey::OLD_VERSION)) {
				$chunkX = Binary::readLInt(substr($key, 0, 4));
				$chunkZ = Binary::readLInt(substr($key, 4, 4));
				try {
					if (($chunk = $this->loadChunk($chunkX, $chunkZ)) !== null) {
						yield [$chunkX, $chunkZ] => $chunk;
					}
				} catch (CorruptedChunkException $e) {
					if (!$skipCorrupted) {
						throw $e;
					}
					if ($logger !== null) {
						$logger->error("Skipped corrupted chunk $chunkX $chunkZ (" . $e->getMessage() . ")");
					}
				}
			}
		}
	}

	public function calculateChunkCount() : int
	{
		$count = 0;
		foreach ($this->db->getIterator() as $key => $_) {
			if (strlen($key) === 9 && substr($key, -1) === ChunkDataKey::OLD_VERSION) {
				$count++;
			}
		}
		return $count;
	}
}
