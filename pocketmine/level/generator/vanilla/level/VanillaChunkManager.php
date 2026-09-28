<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | | | | (_| | |__| |  __/
 * |_|   \_\_|\___|_|\___|_|\__|_| |_| |_|_| |_|\__,_|\____|_| |_|\___|
 *
 * Manager de chunks vanilla que sirve los chunks legacy de Infinity a traves
 * de wrappers GenChunk. El generador SMC opera sobre esta vista sin conocer
 * el formato legacy.
 *
*/

declare(strict_types=1);

namespace pocketmine\level\generator\vanilla\level;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\BlockFactory;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\GenChunk;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\tile\Tile;
use pocketmine\level\format\FullChunk;

class VanillaChunkManager implements ChunkManager{

	/** @var GenChunk[] */
	private $chunks = [];

	/** @var int */
	private $seed;

	/** @var int */
	private $worldHeight;

	/** @var CompoundTag[] */
	private $pendingTiles = [];

	/** @var CompoundTag[] */
	private $pendingEntities = [];

	/** @var callable|null */
	private $legacyProvider; // fn(int $cx, int $cz) : ?FullChunk

	/**
	 * @param callable|null $legacyProvider fn(int $cx, int $cz) : ?FullChunk
	 *        usado para resolver chunks vecinos en populateChunk()
	 */
	public function __construct(int $seed, int $worldHeight, ?callable $legacyProvider = null){
		$this->seed = $seed;
		$this->worldHeight = $worldHeight;
		$this->legacyProvider = $legacyProvider;
	}

	public function setChunkFromLegacy(int $chunkX, int $chunkZ, FullChunk $legacyChunk) : void{
		$key = self::chunkHash($chunkX, $chunkZ);
		$this->chunks[$key] = new GenChunk($legacyChunk, $this->worldHeight - 1);
	}

	public function getGenChunk(int $chunkX, int $chunkZ) : ?GenChunk{
		return $this->chunks[self::chunkHash($chunkX, $chunkZ)] ?? null;
	}

	public function getBlockAt(int $x, int $y, int $z) : Block{
		if($this->isInWorld($x, $y, $z) && ($chunk = $this->getChunk($x >> 4, $z >> 4)) !== null){
			return BlockFactory::fromFullBlock($chunk->getFullBlock($x & 0xf, $y, $z & 0xf));
		}
		return BlockFactory::fromFullBlock(0);
	}

	public function setBlockAt(int $x, int $y, int $z, Block $block) : bool{
		if(($chunk = $this->getChunk($x >> 4, $z >> 4)) !== null){
			return $chunk->setFullBlock($x & 0xf, $y, $z & 0xf, $block->getFullId());
		}
		return false;
	}

	public function addEntity(Entity $entity) : void{
		// no-op: el generador nunca instancia entidades vivas (solo NBT por addEntity(NBT) en StructureWorld)
	}

	public function addTile(Tile $tile) : void{
		// no-op
	}

	public function getChunk(int $chunkX, int $chunkZ) : ?Chunk{
		$key = self::chunkHash($chunkX, $chunkZ);
		if(isset($this->chunks[$key])){
			return $this->chunks[$key];
		}
		// Chunk vecino: resolver contra el nivel legacy si es posible.
		if($this->legacyProvider !== null){
			$legacy = ($this->legacyProvider)($chunkX, $chunkZ);
			if($legacy !== null){
				return $this->chunks[$key] = new GenChunk($legacy, $this->worldHeight - 1);
			}
		}
		return null;
	}

	public function setChunk(int $chunkX, int $chunkZ, ?Chunk $chunk = null) : void{
		if($chunk === null){
			unset($this->chunks[self::chunkHash($chunkX, $chunkZ)]);
			return;
		}
		$this->chunks[self::chunkHash($chunkX, $chunkZ)] = $chunk;
	}

	/** @return CompoundTag[] */
	public function takePendingTiles() : array{
		try{ return $this->pendingTiles; } finally{ $this->pendingTiles = []; }
	}

	/** @return CompoundTag[] */
	public function takePendingEntities() : array{
		try{ return $this->pendingEntities; } finally{ $this->pendingEntities = []; }
	}

	public function cleanChunks() : void{
		$this->chunks = [];
	}

	public function getSeed() : int{
		return $this->seed;
	}

	public function getWorldHeight() : int{
		return $this->worldHeight;
	}

	public function isInWorld(int $x, int $y, int $z) : bool{
		return $y < $this->worldHeight && $y >= 0 &&
			$x <= 2147483647 && $x >= -2147483648 &&
			$z <= 2147483647 && $z >= -2147483648;
	}

	private static function chunkHash(int $x, int $z) : string{
		return $x . ":" . $z;
	}
}
