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

use pocketmine\level\generator\vanilla\timings\TimingsHandler;

class LevelTimings
{
	public TimingsHandler $setBlock;
	public TimingsHandler $doBlockLightUpdates;
	public TimingsHandler $doBlockSkyLightUpdates;

	public TimingsHandler $doChunkUnload;
	public TimingsHandler $scheduledBlockUpdates;
	public TimingsHandler $neighbourBlockUpdates;
	public TimingsHandler $randomChunkUpdates;
	public TimingsHandler $tickBlocks;
	public TimingsHandler $doChunkGC;
	public TimingsHandler $entityTickAlways;
	public TimingsHandler $entityTickQueue;
	public TimingsHandler $tileTick;
	public TimingsHandler $doTick;

	public TimingsHandler $syncChunkSend;
	public TimingsHandler $syncChunkSendPrepare;

	public TimingsHandler $syncChunkLoad;
	public TimingsHandler $syncChunkLoadData;
	public TimingsHandler $syncChunkLoadFixInvalidBlocks;
	public TimingsHandler $syncChunkLoadEntities;
	public TimingsHandler $syncChunkLoadTileEntities;

	public TimingsHandler $syncDataSave;
	public TimingsHandler $syncChunkSave;

	public TimingsHandler $chunkPopulationOrder;
	public TimingsHandler $chunkPopulationCompletion;

	public TimingsHandler $population;

	/**
	 * @var TimingsHandler[]
	 * @phpstan-var array<string, TimingsHandler>
	 */
	private static array $aggregators = [];

	/** @var TimingsHandler[] */
	public static array $randomTickBlocks = [];

	private static function newTimer(string $worldName, string $timerName) : TimingsHandler
	{
		$aggregator = self::$aggregators[$timerName] ??= new TimingsHandler("Worlds - $timerName"); //displayed in Minecraft primary table

		return new TimingsHandler("$worldName - $timerName", $aggregator);
	}

	public function getRandomTickBlocks(string $block) : TimingsHandler
	{
		return self::$randomTickBlocks[$block] ??= new TimingsHandler("Random Tick Block - $block", $this->tickBlocks);
	}

	public function __construct(Level $level)
	{
		$name = $level->getFolderName();

		$this->setBlock = self::newTimer($name, "Set Blocks");
		$this->doBlockLightUpdates = self::newTimer($name, "Block Light Updates");
		$this->doBlockSkyLightUpdates = self::newTimer($name, "Sky Light Updates");

		$this->doChunkUnload = self::newTimer($name, "Unload Chunks");
		$this->scheduledBlockUpdates = self::newTimer($name, "Scheduled Block Updates");
		$this->neighbourBlockUpdates = self::newTimer($name, "Neighbour Block Updates");
		$this->randomChunkUpdates = self::newTimer($name, "Random Chunk Updates");
		$this->tickBlocks = self::newTimer($name, "Tick Blocks");
		$this->doChunkGC = self::newTimer($name, "Garbage Collection");
		$this->entityTickAlways = self::newTimer($name, "Entity Tick Always");
		$this->entityTickQueue = self::newTimer($name, "Entity Tick Queue");
		$this->tileTick = self::newTimer($name, "Block Entity Tick");
		$this->doTick = self::newTimer($name, "World Tick");

		$this->syncChunkSend = self::newTimer($name, "Player Send Chunks");
		$this->syncChunkSendPrepare = self::newTimer($name, "Player Send Chunk Prepare");

		$this->syncChunkLoad = self::newTimer($name, "Chunk Load");
		$this->syncChunkLoadData = self::newTimer($name, "Chunk Load - Data");
		$this->syncChunkLoadFixInvalidBlocks = self::newTimer($name, "Chunk Load - Fix Invalid Blocks");
		$this->syncChunkLoadEntities = self::newTimer($name, "Chunk Load - Entities");
		$this->syncChunkLoadTileEntities = self::newTimer($name, "Chunk Load - Block Entities");

		$this->syncDataSave = self::newTimer($name, "Data Save");
		$this->syncChunkSave = self::newTimer($name, "Chunk Save");

		$this->chunkPopulationOrder = self::newTimer($name, "Chunk Population - Order");
		$this->chunkPopulationCompletion = self::newTimer($name, "Chunk Population - Completion");

		$this->population = self::newTimer($name, "Population");
	}
}
