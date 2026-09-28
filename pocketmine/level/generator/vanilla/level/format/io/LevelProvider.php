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

namespace pocketmine\level\generator\vanilla\level\format\io;

use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\level\format\io\exception\CorruptedChunkException;

interface LevelProvider
{
	/**
	 * Gets the build height limit of this world
	 */
	public function getWorldHeight() : int;

	public function getPath() : string;

	/**
	 * Loads a chunk (usually from disk storage) and returns it. If the chunk does not exist, null is returned.
	 *
	 * @throws CorruptedChunkException
	 */
	public function loadChunk(int $chunkX, int $chunkZ) : ?Chunk;

	/**
	 * Performs garbage collection in the world provider, such as cleaning up regions in Region-based worlds.
	 */
	public function doGarbageCollection() : void;

	/**
	 * Returns information about the world
	 */
	public function getLevelData() : LevelData;

	/**
	 * Performs cleanups necessary when the world provider is closed and no longer needed.
	 */
	public function close() : void;

	/**
	 * Returns a generator which yields all the chunks in this world.
	 *
	 * @return \Generator|Chunk[]
	 * @phpstan-return \Generator<array{int, int}, Chunk, void, void>
	 * @throws CorruptedChunkException
	 */
	public function getAllChunks(bool $skipCorrupted = false, ?\Logger $logger = null) : \Generator;

	/**
	 * Returns the number of chunks in the provider. Used for world conversion time estimations.
	 */
	public function calculateChunkCount() : int;

}
