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

/**
 * Implementation of MCPE-style chunks with subchunks with XZY ordering.
 */
declare(strict_types=1);

namespace pocketmine\level\generator\vanilla\level\format;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\BlockFactory;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\level\format\io\FastChunkSerializer;
use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\tile\Tile;
use SplFixedArray;
use function array_fill;
use function array_filter;
use function assert;
use function chr;
use function count;
use function ord;
use function str_repeat;
use function strlen;

class Chunk
{
	public const MAX_SUBCHUNKS = 16;

	public const EDGE_LENGTH = SubChunk::EDGE_LENGTH;
	public const COORD_BIT_SIZE = SubChunk::COORD_BIT_SIZE;
	public const COORD_MASK = SubChunk::COORD_MASK;

	/** @var int */
	protected $x;
	/** @var int */
	protected $z;

	/** @var bool */
	protected $hasChanged = false;

	/** @var bool */
	protected $isInit = false;

	/** @var bool */
	protected $lightPopulated = false;
	/** @var bool */
	protected $terrainGenerated = false;
	/** @var bool */
	protected $terrainPopulated = false;

	/** @var int */
	protected $height = Chunk::MAX_SUBCHUNKS;

	/** @var SplFixedArray|SubChunkInterface[] */
	protected $subChunks;

	/** @var EmptySubChunk */
	protected $emptySubChunk;

	/** @var Tile[] */
	protected $tiles = [];
	/** @var Tile[] */
	protected $tileList = [];

	/** @var Entity[] */
	protected $entities = [];

	/** @var SplFixedArray|int[] */
	protected $heightMap;

	/** @var string */
	protected $biomeIds;

	/** @var CompoundTag[] */
	protected $NBTtiles = [];

	/** @var CompoundTag[] */
	protected $NBTentities = [];

	/** @var bool */
	protected $protect = false;

	/**
	 * @param SubChunkInterface[] $subChunks
	 * @param CompoundTag[]       $entities
	 * @param CompoundTag[]       $tiles
	 * @param int[]               $heightMap
	 */
	public function __construct(int $chunkX, int $chunkZ, array $subChunks = [], array $entities = [], array $tiles = [], string $biomeIds = "", array $heightMap = [])
	{
		$this->x = $chunkX;
		$this->z = $chunkZ;

		$this->height = Chunk::MAX_SUBCHUNKS; //TODO: add a way of changing this

		$this->subChunks = new SplFixedArray($this->height);
		$this->emptySubChunk = EmptySubChunk::getInstance();

		foreach ($this->subChunks as $y => $null) {
			$this->subChunks[$y] = $subChunks[$y] ?? $this->emptySubChunk;
		}

		if (count($heightMap) === 256) {
			$this->heightMap = SplFixedArray::fromArray($heightMap);
		} else {
			assert(count($heightMap) === 0, "Wrong HeightMap value count, expected 256, got " . count($heightMap));
			$val = ($this->height * 16);
			$this->heightMap = SplFixedArray::fromArray(array_fill(0, 256, $val));
		}

		if (strlen($biomeIds) === 256) {
			$this->biomeIds = $biomeIds;
		} else {
			assert($biomeIds === "", "Wrong BiomeIds value count, expected 256, got " . strlen($biomeIds));
			$this->biomeIds = str_repeat("\x00", 256);
		}

		$this->NBTtiles = $tiles;
		$this->NBTentities = $entities;
	}

	public function isInit() : bool {
		return $this->isInit;
	}

	public function setProtect(bool $protect = true) : void
	{
		$this->protect = $protect;
	}

	public function isProtected() : bool
	{
		return $this->protect;
	}

	public function getX() : int
	{
		return $this->x;
	}

	public function getZ() : int
	{
		return $this->z;
	}

	public function setX(int $x)
	{
		$this->x = $x;
	}

	public function setZ(int $z)
	{
		$this->z = $z;
	}

	/**
	 * Returns the chunk height in count of subchunks.
	 */
	public function getHeight() : int
	{
		return $this->height;
	}

	/**
	 * Returns a bitmap of block ID and meta at the specified chunk block coordinates
	 *
	 * @param int $x 0-15
	 * @param int $z 0-15
	 *
	 * @return int bitmap, (id << 4) | meta
	 */
	public function getFullBlock(int $x, int $y, int $z) : int
	{
		return $this->getSubChunk($y >> Chunk::COORD_BIT_SIZE)->getFullBlock($x & Chunk::COORD_MASK, $y & 0x0f, $z & Chunk::COORD_MASK);
	}

	/**
	 * Returns a bitmap of block ID and meta at the specified chunk block coordinates
	 *
	 * @param int $x 0-15
	 * @param int $z 0-15
	 */
	public function setFullBlock(int $x, int $y, int $z, int $fullId) : bool
	{
		if ($this->getSubChunk($y >> Chunk::COORD_BIT_SIZE, true)->setFullBlock($x & Chunk::COORD_MASK, $y & 0x0f, $z & Chunk::COORD_MASK, $fullId)) {
			$this->hasChanged = true;
			return true;
		}
		return false;
	}

	/**
	 * Sets block ID and meta in one call at the specified chunk block coordinates
	 *
	 * @param int      $x       0-15
	 * @param int      $z       0-15
	 * @param int|null $blockId 0-255 if null, does not change
	 * @param int|null $meta    0-15 if null, does not change
	 */
	public function setBlock(int $x, int $y, int $z, ?int $blockId = null, ?int $meta = null) : bool
	{
		if ($this->getSubChunk($y >> Chunk::COORD_BIT_SIZE, true)->setBlock($x & Chunk::COORD_MASK, $y & 0x0f, $z & Chunk::COORD_MASK, $blockId, $meta)) {
			$this->hasChanged = true;
			return true;
		}
		return false;
	}

	/**
	 * Returns the block ID at the specified chunk block coordinates
	 *
	 * @param int $x 0-15
	 * @param int $z 0-15
	 *
	 * @return int 0-255
	 */
	public function getBlockId(int $x, int $y, int $z) : int
	{
		return $this->getSubChunk($y >> Chunk::COORD_BIT_SIZE)->getBlockId($x & Chunk::COORD_MASK, $y & 0x0f, $z & Chunk::COORD_MASK);
	}

	/**
	 * Sets the block ID at the specified chunk block coordinates
	 *
	 * @param int $x  0-15
	 * @param int $z  0-15
	 * @param int $id 0-255
	 */
	public function setBlockId(int $x, int $y, int $z, int $id)
	{
		if ($this->getSubChunk($y >> Chunk::COORD_BIT_SIZE, true)->setBlockId($x & Chunk::COORD_MASK, $y & 0x0f, $z & Chunk::COORD_MASK, $id)) {
			$this->hasChanged = true;
		}
	}

	/**
	 * Returns the block meta value at the specified chunk block coordinates
	 *
	 * @param int $x 0-15
	 * @param int $z 0-15
	 *
	 * @return int 0-15
	 */
	public function getBlockData(int $x, int $y, int $z) : int
	{
		return $this->getSubChunk($y >> Chunk::COORD_BIT_SIZE)->getBlockData($x & Chunk::COORD_MASK, $y & 0x0f, $z & Chunk::COORD_MASK);
	}

	/**
	 * Sets the block meta value at the specified chunk block coordinates
	 *
	 * @param int $x    0-15
	 * @param int $z    0-15
	 * @param int $data 0-15
	 */
	public function setBlockData(int $x, int $y, int $z, int $data)
	{
		if ($this->getSubChunk($y >> Chunk::COORD_BIT_SIZE, true)->setBlockData($x & Chunk::COORD_MASK, $y & 0x0f, $z & Chunk::COORD_MASK, $data)) {
			$this->hasChanged = true;
		}
	}

	/**
	 * Returns the sky light level at the specified chunk block coordinates
	 *
	 * @param int $x 0-15
	 * @param int $z 0-15
	 *
	 * @return int 0-15
	 */
	public function getBlockSkyLight(int $x, int $y, int $z) : int
	{
		return $this->getSubChunk($y >> Chunk::COORD_BIT_SIZE)->getBlockSkyLight($x & Chunk::COORD_MASK, $y & 0x0f, $z & Chunk::COORD_MASK);
	}

	/**
	 * Sets the sky light level at the specified chunk block coordinates
	 *
	 * @param int $x     0-15
	 * @param int $z     0-15
	 * @param int $level 0-15
	 */
	public function setBlockSkyLight(int $x, int $y, int $z, int $level)
	{
		if ($this->getSubChunk($y >> Chunk::COORD_BIT_SIZE, true)->setBlockSkyLight($x & Chunk::COORD_MASK, $y & 0x0f, $z & Chunk::COORD_MASK, $level)) {
			$this->hasChanged = true;
		}
	}

	public function setAllBlockSkyLight(int $level)
	{
		$char = chr(($level & 0x0f) | ($level << 4));
		$data = str_repeat($char, 2048);
		for ($y = $this->getHighestSubChunkIndex(); $y >= 0; --$y) {
			$this->getSubChunk($y, true)->setBlockSkyLightArray($data);
		}
	}

	/**
	 * Returns the block light level at the specified chunk block coordinates
	 *
	 * @param int $x 0-15
	 * @param int $y 0-15
	 * @param int $z 0-15
	 *
	 * @return int 0-15
	 */
	public function getBlockLight(int $x, int $y, int $z) : int
	{
		return $this->getSubChunk($y >> Chunk::COORD_BIT_SIZE)->getBlockLight($x & Chunk::COORD_MASK, $y & 0x0f, $z & Chunk::COORD_MASK);
	}

	/**
	 * Sets the block light level at the specified chunk block coordinates
	 *
	 * @param int $x     0-15
	 * @param int $y     0-15
	 * @param int $z     0-15
	 * @param int $level 0-15
	 */
	public function setBlockLight(int $x, int $y, int $z, int $level)
	{
		if ($this->getSubChunk($y >> Chunk::COORD_BIT_SIZE, true)->setBlockLight($x & Chunk::COORD_MASK, $y & 0x0f, $z & Chunk::COORD_MASK, $level)) {
			$this->hasChanged = true;
		}
	}

	public function setAllBlockLight(int $level)
	{
		$char = chr(($level & 0x0f) | ($level << 4));
		$data = str_repeat($char, 2048);
		for ($y = $this->getHighestSubChunkIndex(); $y >= 0; --$y) {
			$this->getSubChunk($y, true)->setBlockLightArray($data);
		}
	}

	/**
	 * Returns the Y coordinate of the highest non-air block at the specified X/Z chunk block coordinates
	 *
	 * @param int $x 0-15
	 * @param int $z 0-15
	 *
	 * @return int 0-255, or -1 if there are no blocks in the column
	 */
	public function getHighestBlockAt(int $x, int $z) : int
	{
		$index = $this->getHighestSubChunkIndex();
		if ($index === -1) {
			return -1;
		}

		for ($y = $index; $y >= 0; --$y) {
			$height = $this->getSubChunk($y)->getHighestBlockAt($x, $z) | ($y << 4);
			if ($height !== -1) {
				return $height;
			}
		}

		return -1;
	}

	public function getMaxY() : int
	{
		return ($this->getHighestSubChunkIndex() << 4) | 0x0f;
	}

	/**
	 * Returns the heightmap value at the specified X/Z chunk block coordinates
	 *
	 * @param int $x 0-15
	 * @param int $z 0-15
	 */
	public function getHeightMap(int $x, int $z) : int
	{
		return $this->heightMap[($z << 4) | $x];
	}

	/**
	 * Returns the heightmap value at the specified X/Z chunk block coordinates
	 *
	 * @param int $x 0-15
	 * @param int $z 0-15
	 */
	public function setHeightMap(int $x, int $z, int $value)
	{
		$this->heightMap[($z << 4) | $x] = $value;
	}

	/**
	 * Recalculates the heightmap for the whole chunk.
	 */
	public function recalculateHeightMap()
	{
		for ($z = 0; $z < 16; ++$z) {
			for ($x = 0; $x < 16; ++$x) {
				$this->recalculateHeightMapColumn($x, $z);
			}
		}
	}

	/**
	 * Recalculates the heightmap for the block column at the specified X/Z chunk coordinates
	 *
	 * @param int $x 0-15
	 * @param int $z 0-15
	 *
	 * @return int New calculated heightmap value (0-256 inclusive)
	 */
	public function recalculateHeightMapColumn(int $x, int $z) : int
	{
		$max = $this->getHighestBlockAt($x, $z);
		for ($y = $max; $y >= 0; --$y) {
			if (BlockFactory::$lightFilter[$id = $this->getBlockId($x, $y, $z)] > 1 || BlockFactory::$diffusesSkyLight[$id]) {
				break;
			}
		}

		$this->setHeightMap($x, $z, $y + 1);
		return $y + 1;
	}

	/**
	 * Performs basic sky light population on the chunk.
	 * This does not cater for adjacent sky light, this performs direct sky light population only. This may cause some strange visual artifacts
	 * if the chunk is light-populated after being terrain-populated.
	 *
	 * TODO: fast adjacent light spread
	 */
	public function populateSkyLight()
	{
		$maxY = $this->getMaxY();

		$this->setAllBlockSkyLight(0);

		for ($x = 0; $x < 16; ++$x) {
			for ($z = 0; $z < 16; ++$z) {
				$heightMap = $this->getHeightMap($x, $z);

				for ($y = $maxY; $y >= $heightMap; --$y) {
					$this->setBlockSkyLight($x, $y, $z, 15);
				}

				$light = 15;
				for (; $y >= 0; --$y) {
					if ($light > 0) {
						$light -= BlockFactory::$lightFilter[$this->getBlockId($x, $y, $z)];
						if ($light <= 0) {
							break;
						}
					}
					$this->setBlockSkyLight($x, $y, $z, $light);
				}
			}
		}
	}

	/**
	 * Returns the biome ID at the specified X/Z chunk block coordinates
	 *
	 * @param int $x 0-15
	 * @param int $z 0-15
	 *
	 * @return int 0-255
	 */
	public function getBiomeId(int $x, int $z) : int
	{
		return ord($this->biomeIds[($z << 4) | $x]);
	}

	/**
	 * Sets the biome ID at the specified X/Z chunk block coordinates
	 *
	 * @param int $x       0-15
	 * @param int $z       0-15
	 * @param int $biomeId 0-255
	 */
	public function setBiomeId(int $x, int $z, int $biomeId)
	{
		$this->hasChanged = true;
		$this->biomeIds[($z << 4) | $x] = chr($biomeId & 0xff);
	}

	/**
	 * Returns a column of sky light values from bottom to top at the specified X/Z chunk block coordinates.
	 *
	 * @param int $x 0-15
	 * @param int $z 0-15
	 */
	public function getBlockSkyLightColumn(int $x, int $z) : string
	{
		$result = "";
		foreach ($this->subChunks as $subChunk) {
			$result .= $subChunk->getBlockSkyLightColumn($x, $z);
		}
		return $result;
	}

	/**
	 * Returns a column of block light values from bottom to top at the specified X/Z chunk block coordinates.
	 *
	 * @param int $x 0-15
	 * @param int $z 0-15
	 */
	public function getBlockLightColumn(int $x, int $z) : string
	{
		$result = "";
		foreach ($this->subChunks as $subChunk) {
			$result .= $subChunk->getBlockLightColumn($x, $z);
		}
		return $result;
	}

	public function isLightPopulated() : bool
	{
		return $this->lightPopulated;
	}

	public function setLightPopulated(bool $value = true)
	{
		$this->lightPopulated = $value;
		$this->hasChanged = true;
	}

	public function isPopulated() : bool
	{
		return $this->terrainPopulated;
	}

	public function setPopulated(bool $value = true)
	{
		$this->terrainPopulated = $value;
		$this->hasChanged = true;
	}

	public function isGenerated() : bool
	{
		return $this->terrainGenerated;
	}

	public function setGenerated(bool $value = true)
	{
		$this->terrainGenerated = $value;
		$this->hasChanged = true;
	}

	public function getNBTTiles() : array {
		return $this->NBTtiles;
	}

	public function getNBTEntities() : array {
		return $this->NBTentities;
	}

	public function addNBTTile(CompoundTag $nbt) : void {
		if ($this->isInit) {
			throw new \InvalidArgumentException("Cannot add chunk twice");
		}

		$this->NBTtiles[] = $nbt;
	}

	public function addNBTEntity(CompoundTag $nbt) : void {
		if ($this->isInit) {
			throw new \InvalidArgumentException("Cannot add chunk twice");
		}

		$this->NBTentities[] = $nbt;
	}

	public function addEntity(Entity $entity)
	{
		if ($entity->isClosed()) {
			throw new \InvalidArgumentException("Attempted to add a garbage closed Entity to a chunk");
		}
		$this->entities[$entity->getId()] = $entity;
		if (!($entity instanceof Player) && $this->isInit) {
			$this->hasChanged = true;
		}
	}

	public function removeEntity(Entity $entity)
	{
		unset($this->entities[$entity->getId()]);
		if (!($entity instanceof Player) && $this->isInit) {
			$this->hasChanged = true;
		}
	}

	public function addTile(Tile $tile)
	{
		if ($tile->isClosed()) {
			throw new \InvalidArgumentException("Attempted to add a garbage closed Tile to a chunk");
		}
		$this->tiles[$tile->getId()] = $tile;
		if (isset($this->tileList[$index = (($tile->x & 0x0f) << 12) | (($tile->z & 0x0f) << 8) | ($tile->y & 0xff)]) && $this->tileList[$index] !== $tile) {
			$this->tileList[$index]->close();
		}
		$this->tileList[$index] = $tile;
		if ($this->isInit) {
			$this->hasChanged = true;
		}
	}

	public function removeTile(Tile $tile)
	{
		unset($this->tiles[$tile->getId()]);
		unset($this->tileList[(($tile->x & 0x0f) << 12) | (($tile->z & 0x0f) << 8) | ($tile->y & 0xff)]);
		if ($this->isInit) {
			$this->hasChanged = true;
		}
	}

	/**
	 * Returns an array of entities currently using this chunk.
	 *
	 * @return Entity[]
	 */
	public function getEntities() : array
	{
		return $this->entities;
	}

	/**
	 * @return Entity[]
	 */
	public function getSavableEntities() : array
	{
		return array_filter($this->entities, function (Entity $entity) : bool { return $entity->canSaveWithChunk() && !$entity->isClosed(); });
	}

	/**
	 * @return Tile[]
	 */
	public function getTiles() : array
	{
		return $this->tiles;
	}

	/**
	 * Returns the tile at the specified chunk block coordinates, or null if no tile exists.
	 *
	 * @param int $x 0-15
	 * @param int $z 0-15
	 *
	 * @return Tile|null
	 */
	public function getTile(int $x, int $y, int $z)
	{
		$index = ($x << 12) | ($z << 8) | $y;
		return $this->tileList[$index] ?? null;
	}

	/**
	 * Called when the chunk is unloaded, closing entities and tiles.
	 */
	public function onUnload() : void
	{
		foreach ($this->getEntities() as $entity) {
			if ($entity instanceof Player) {
				continue;
			}
			$entity->close();
		}

		foreach ($this->getTiles() as $tile) {
			$tile->close();
		}
	}

	/**
	 * Deserializes tiles and entities from NBT
	 */
	public function initChunk(Level $level)
	{
		if (!$this->isInit) {
			$changed = false;

			$level->timings->syncChunkLoadEntities->startTiming();
			foreach ($this->NBTentities as $nbt) {
				if ($nbt instanceof CompoundTag) {
					if (!$nbt->hasTag("id")) { //allow mixed types (because of leveldb)
						$changed = true;
						continue;
					}

					try {
						$entity = Entity::createEntity($nbt->getTag("id")->getValue(), $level, $nbt);
						if (!($entity instanceof Entity)) {
							$changed = true;
							continue;
						}
					} catch (\Throwable $t) {
						$level->getServer()->getLogger()->logException($t);
						$changed = true;
						continue;
					}
				}
			}
			$this->NBTentities = [];
			$level->timings->syncChunkLoadEntities->stopTiming();

			$level->timings->syncChunkLoadTileEntities->startTiming();
			foreach ($this->NBTtiles as $nbt) {
				if ($nbt instanceof CompoundTag) {
					if (!$nbt->hasTag(Tile::TAG_ID, StringTag::class)) {
						$changed = true;
						continue;
					}

					if (Tile::createTile($nbt->getString(Tile::TAG_ID), $level, $nbt) === null) {
						$changed = true;
					}
				}
			}

			$this->NBTtiles = [];
			$level->timings->syncChunkLoadTileEntities->stopTiming();

			$this->hasChanged = $changed;

			$this->isInit = true;
		}
	}

	public function getBiomeIdArray() : string
	{
		return $this->biomeIds;
	}

	/**
	 * @return int[]
	 */
	public function getHeightMapArray() : array
	{
		return $this->heightMap->toArray();
	}

	public function hasChanged() : bool
	{
		return $this->hasChanged;
	}

	public function setChanged(bool $value = true)
	{
		$this->hasChanged = $value;
	}

	/**
	 * Returns the subchunk at the specified subchunk Y coordinate, or an empty, unmodifiable stub if it does not exist or the coordinate is out of range.
	 *
	 * @param bool $generateNew Whether to create a new, modifiable subchunk if there is not one in place
	 */
	public function getSubChunk(int $y, bool $generateNew = false) : SubChunkInterface
	{
		if ($y < 0 || $y >= $this->height) {
			return $this->emptySubChunk;
		} elseif ($generateNew && $this->subChunks[$y] instanceof EmptySubChunk) {
			$this->subChunks[$y] = new SubChunk(Block::AIR << Block::INTERNAL_METADATA_BITS, []);
		}

		return $this->subChunks[$y];
	}

	/**
	 * Sets a subchunk in the chunk index
	 *
	 * @param bool $allowEmpty Whether to check if the chunk is empty, and if so replace it with an empty stub
	 */
	public function setSubChunk(int $y, ?SubChunkInterface $subChunk = null, bool $allowEmpty = false) : bool
	{
		if ($y < 0 || $y >= $this->height) {
			return false;
		}
		if ($subChunk === null || ($subChunk->isEmpty() && !$allowEmpty)) {
			$this->subChunks[$y] = $this->emptySubChunk;
		} else {
			$this->subChunks[$y] = $subChunk;
		}
		$this->hasChanged = true;
		return true;
	}

	/**
	 * @return SplFixedArray|SubChunkInterface[]
	 */
	public function getSubChunks() : SplFixedArray
	{
		return $this->subChunks;
	}

	/**
	 * Returns the Y coordinate of the highest non-empty subchunk in this chunk.
	 */
	public function getHighestSubChunkIndex() : int
	{
		for ($y = $this->subChunks->count() - 1; $y >= 0; --$y) {
			if ($this->subChunks[$y] instanceof EmptySubChunk) {
				//No need to thoroughly prune empties at runtime, this will just reduce performance.
				continue;
			}
			break;
		}

		return $y;
	}

	/**
	 * Returns the count of subchunks that need sending to players
	 */
	public function getSubChunkSendCount() : int
	{
		return $this->getHighestSubChunkIndex() + 1;
	}

	/**
	 * Disposes of empty subchunks and frees data where possible
	 */
	public function collectGarbage() : void
	{
		foreach ($this->subChunks as $y => $subChunk) {
			if ($subChunk instanceof SubChunk) {
				if ($subChunk->isEmpty()) {
					$this->subChunks[$y] = $this->emptySubChunk;
				} else {
					$subChunk->collectGarbage();
				}
			}
		}
	}

	/**
	 * Fast-serializes the chunk for passing between threads
	 *
	 * @deprecated
	 */
	public function fastSerialize() : string
	{
		return FastChunkSerializer::serializeTerrain($this);
	}

	/**
	 * Deserializes a fast-serialized chunk
	 *
	 * @deprecated
	 */
	public static function fastDeserialize(string $data) : Chunk
	{
		return FastChunkSerializer::deserializeTerrain($data);
	}
}
