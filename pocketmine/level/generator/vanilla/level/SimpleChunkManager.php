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

namespace pocketmine\level\generator\vanilla\level;

use InvalidStateException;
use pocketmine\level\generator\vanilla\block\Air;
use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\BlockFactory;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\tile\Tile;
use function get_class;
use const INT32_MAX;
use const INT32_MIN;

class SimpleChunkManager implements ChunkManager
{
	/** @var Chunk[] */
	protected array $chunks = [];

	/** @var Entity[] */
	protected array $entities = [];

	/** @var Tile[] */
	protected array $tiles = [];

	public function __construct(
		private int $seed,
		private int $worldHeight
	) {}

	public function getBlockAt(int $x, int $y, int $z) : Block{
		if ($this->isInWorld($x, $y, $z) && ($chunk = $this->getChunk($x >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE)) !== null) {
			return BlockFactory::fromFullBlock($chunk->getFullBlock($x & Chunk::COORD_MASK, $y, $z & Chunk::COORD_MASK));
		}

		return new Air();
	}

	public function setBlockAt(int $x, int $y, int $z, Block $block) : bool{
		if (($chunk = $this->getChunk($x >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE)) !== null) {
			return $chunk->setFullBlock($x & Chunk::COORD_MASK, $y, $z & Chunk::COORD_MASK, $block->getFullId());
		} else {
			return false;
		}
	}

	public function addEntity(Entity $entity) : void{
		$chunkX = $entity->getFloorX() >> Chunk::COORD_BIT_SIZE;
		$chunkZ = $entity->getFloorZ() >> Chunk::COORD_BIT_SIZE;

		if (isset($this->chunks[Level::chunkHash($chunkX, $chunkZ)])) {
			$this->entities[] = $entity;
		} else {
			throw new InvalidStateException("Attempted to create entity " . get_class($entity) . " in unloaded chunk $chunkX $chunkZ");
		}
	}

	public function getEntities() : array{
		return $this->entities;
	}

	public function addTile(Tile $tile) : void{
		$chunkX = $tile->getFloorX() >> Chunk::COORD_BIT_SIZE;
		$chunkZ = $tile->getFloorZ() >> Chunk::COORD_BIT_SIZE;

		if (isset($this->chunks[Level::chunkHash($chunkX, $chunkZ)])) {
			$this->tiles[] = $tile;
		} else {
			throw new InvalidStateException("Attempted to create tile " . get_class($tile) . " in unloaded chunk $chunkX $chunkZ");
		}
	}

	public function getTiles() : array{
		return $this->tiles;
	}

	public function getChunk(int $chunkX, int $chunkZ) : ?Chunk{
		return $this->chunks[Level::chunkHash($chunkX, $chunkZ)] ?? null;
	}

	public function setChunk(int $chunkX, int $chunkZ, ?Chunk $chunk = null) : void{
		if($chunk === null){
			unset($this->chunks[Level::chunkHash($chunkX, $chunkZ)]);
			return;
		}

		$this->chunks[Level::chunkHash($chunkX, $chunkZ)] = $chunk;
	}

	public function cleanChunks() : void{
		$this->chunks = [];
	}

	/**
	 * Gets the level seed
	 */
	public function getSeed() : int{
		return $this->seed;
	}

	public function getWorldHeight() : int{
		return $this->worldHeight;
	}

	public function isInWorld(int $x, int $y, int $z) : bool{
		return (
			$x <= INT32_MAX && $x >= INT32_MIN &&
			$y < $this->worldHeight && $y >= Level::Y_MIN &&
			$z <= INT32_MAX && $z >= INT32_MIN
		);
	}
}
