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

namespace pocketmine\level\generator\vanilla\level\generator;

use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\level\format\io\FastChunkSerializer;
use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\level\SimpleChunkManager;
use pocketmine\level\generator\vanilla\scheduler\AsyncTask;
use pocketmine\Server;
use function intdiv;
use function serialize;
use function unserialize;

class PopulationTask extends AsyncTask
{
	public $state;
	public $levelId;
	public $chunk;
	/** @var int radius of the neighbour chunks, see chunk-generation.population-radius */
	public $radius;
	/**
	 * Serialized array: neighbour index => serialized terrain or null.
	 * Stored as one string because arrays can't be shared with the worker thread safely.
	 * @var string
	 */
	public $neighbours;

	public function __construct(Level $level, Chunk $chunk)
	{
		$this->state = true;
		$this->levelId = $level->getId();
		$this->radius = $level->getChunkPopulationRadius();
		$this->chunk = FastChunkSerializer::serializeTerrain($chunk);

		$neighbours = [];
		foreach ($level->getAdjacentChunks($chunk->getX(), $chunk->getZ(), $this->radius) as $i => $c) {
			$neighbours[$i] = $c !== null ? FastChunkSerializer::serializeTerrain($c) : null;
		}
		$this->neighbours = serialize($neighbours);
	}

	public function onRun() : void{
		$manager = $this->getFromThreadStore("generation.level{$this->levelId}.manager");
		$generator = $this->getFromThreadStore("generation.level{$this->levelId}.generator");
		if(!($manager instanceof SimpleChunkManager) || !($generator instanceof Generator)){
			$this->state = false;
			return;
		}

		$radius = (int) $this->radius;
		$size = $radius * 2 + 1;
		/** @var (string|null)[] $serialized */
		$serialized = unserialize($this->neighbours, ["allowed_classes" => false]);

		/** @var Chunk[] $chunks */
		$chunks = [];

		$chunk = FastChunkSerializer::deserializeTerrain($this->chunk);

		foreach ($serialized as $i => $ck) {
			$xx = -$radius + $i % $size;
			$zz = -$radius + intdiv($i, $size);
			if ($ck === null) {
				$chunks[$i] = new Chunk($chunk->getX() + $xx, $chunk->getZ() + $zz);
			} else {
				$chunks[$i] = FastChunkSerializer::deserializeTerrain($ck);
			}
		}

		$manager->setChunk($chunk->getX(), $chunk->getZ(), $chunk);
		if(!$chunk->isGenerated()){
			$generator->generateChunk($chunk->getX(), $chunk->getZ());
			$chunk = $manager->getChunk($chunk->getX(), $chunk->getZ());
			$chunk->setGenerated();
		}

		foreach($chunks as $i => $c){
			$manager->setChunk($c->getX(), $c->getZ(), $c);
			if(!$c->isGenerated()){
				$generator->generateChunk($c->getX(), $c->getZ());
				$chunks[$i] = $manager->getChunk($c->getX(), $c->getZ());
				$chunks[$i]->setGenerated();
			}
		}

		$generator->populateChunk($chunk->getX(), $chunk->getZ());
		foreach ($manager->getEntities() as $entity) {
			$chunkX = $entity->getFloorX() >> Chunk::COORD_BIT_SIZE;
			$chunkZ = $entity->getFloorZ() >> Chunk::COORD_BIT_SIZE;

			$entityChunk = $manager->getChunk($chunkX, $chunkZ);
			if ($entityChunk !== null) {
				$entity->saveNBT();
				$entityChunk->addNBTEntity($entity->namedtag);
			}
		}

		foreach ($manager->getTiles() as $tile) {
			$chunkX = $tile->getFloorX() >> Chunk::COORD_BIT_SIZE;
			$chunkZ = $tile->getFloorZ() >> Chunk::COORD_BIT_SIZE;

			$tileChunk = $manager->getChunk($chunkX, $chunkZ);
			if ($tileChunk !== null) {
				$tileChunk->addNBTTile($tile->saveNBT());
			}
		}

		$chunk = $manager->getChunk($chunk->getX(), $chunk->getZ());
		$chunk->setPopulated();

		$chunk->recalculateHeightMap();
		$chunk->populateSkyLight();
		$chunk->setLightPopulated();

		$this->chunk = FastChunkSerializer::serializeTerrain($chunk, true);

		$manager->setChunk($chunk->getX(), $chunk->getZ(), null);

		//only chunks which were changed are sent back
		$result = [];
		foreach($chunks as $i => $c){
			$c = $manager->getChunk($c->getX(), $c->getZ());
			$result[$i] = $c !== null && $c->hasChanged() ? FastChunkSerializer::serializeTerrain($c, true) : null;
		}

		$manager->cleanChunks();

		$this->neighbours = serialize($result);
	}

	public function onCompletion(Server $server) : void{
		$level = $server->getLevel($this->levelId);
		if ($level !== null) {
			if (!$this->state) {
				$level->registerGeneratorToWorker($this->getWorker()->getAsyncWorkerId());
			}

			$chunk = FastChunkSerializer::deserializeTerrain($this->chunk, true);

			if ($this->state) {
				/** @var (string|null)[] $serialized */
				$serialized = unserialize($this->neighbours, ["allowed_classes" => false]);
				foreach ($serialized as $c) {
					if ($c !== null) {
						$c = FastChunkSerializer::deserializeTerrain($c, true);
						$level->generateChunkCallback($c->getX(), $c->getZ(), $c);
					}
				}
			}

			$level->generateChunkCallback($chunk->getX(), $chunk->getZ(), $this->state ? $chunk : null);
		}
	}
}
