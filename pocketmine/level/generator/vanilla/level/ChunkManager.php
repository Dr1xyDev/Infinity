<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | | | | (_| | |__| |  __/
 * |_|   \_\_|\___|_|\___|_|\__|_| |_| |_|_| |_|\__,_|\____|_| |_|\___|
 *
 * Interface de ChunkManager vanilla re-declarada para el arbol vendorizado
 * (identica a la del arbol SMC; los adaptadores legacy la implementan).
 *
*/

declare(strict_types=1);

namespace pocketmine\level\generator\vanilla\level;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\tile\Tile;

interface ChunkManager
{
	/**
	 * Returns a Block object representing the block state at the given coordinates.
	 */
	public function getBlockAt(int $x, int $y, int $z) : Block;

	/**
	 * Sets the block at the given coordinates to the block state specified.
	 *
	 * @throws \InvalidArgumentException
	 */
	public function setBlockAt(int $x, int $y, int $z, Block $block) : bool;

	public function addEntity(Entity $entity) : void;

	public function addTile(Tile $tile) : void;

	public function getChunk(int $chunkX, int $chunkZ) : ?Chunk;

	public function setChunk(int $chunkX, int $chunkZ, ?Chunk $chunk = null) : void;

	/**
	 * Gets the level seed
	 */
	public function getSeed() : int;

	/**
	 * Returns the height of the world
	 */
	public function getWorldHeight() : int;

	/**
	 * Returns whether the specified coordinates are within the valid world boundaries, taking world format limitations
	 * into account.
	 */
	public function isInWorld(int $x, int $y, int $z) : bool;
}
