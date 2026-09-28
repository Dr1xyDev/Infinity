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

namespace pocketmine\level\generator\vanilla\level\portal;

use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\level\Position;
use pocketmine\level\generator\vanilla\math\Vector3;
use function intdiv;
use function min;

/**
 * A pending move of an entity to another dimension. The terrain at the destination is generated
 * asynchronously, so the destination is only resolved once the chunks around it are populated.
 */
final class PortalTravel
{
	/** Link to (or build) a nether portal around the scaled position */
	public const MODE_NETHER_PORTAL = 0;
	/** Arrive on the obsidian platform of the end */
	public const MODE_END_SPAWN = 1;
	/** Go to a spawn point (leaving the end) */
	public const MODE_SPAWN = 2;

	/** Give up waiting for the terrain after this many ticks and use the world spawn */
	private const TIMEOUT_TICKS = 600;
	/** Chunks around the destination which are populated before a portal is built */
	private const MAX_CHUNK_RADIUS = 2;

	private int $ticks = 0;
	private bool $indexSearched = false;
	/** @var int[][] */
	private array $chunks = [];

	public function __construct(
		private int $mode,
		private Level $target,
		private Vector3 $center,
		private int $axis = PortalShape::AXIS_X
	) {
		$chunkX = $center->getFloorX() >> Chunk::COORD_BIT_SIZE;
		$chunkZ = $center->getFloorZ() >> Chunk::COORD_BIT_SIZE;
		$radius = $mode === self::MODE_NETHER_PORTAL ? min(self::MAX_CHUNK_RADIUS, intdiv(PortalManager::getCreateRadius() + Chunk::EDGE_LENGTH - 1, Chunk::EDGE_LENGTH)) : 0;
		for ($x = -$radius; $x <= $radius; ++$x) {
			for ($z = -$radius; $z <= $radius; ++$z) {
				$this->chunks[] = [$chunkX + $x, $chunkZ + $z];
			}
		}
	}

	public function getMode() : int
	{
		return $this->mode;
	}

	public function getTargetLevel() : Level
	{
		return $this->target;
	}

	/**
	 * Returns whether the destination world was unloaded meanwhile
	 */
	public function isCancelled() : bool
	{
		return $this->target->isClosed();
	}

	/**
	 * Advances the travel. Returns the destination once it is known, null while the terrain is still being generated.
	 */
	public function tick() : ?Position
	{
		if ($this->target->isClosed()) {
			return null;
		}
		++$this->ticks;

		if ($this->mode === self::MODE_NETHER_PORTAL && !$this->indexSearched) {
			//known portals only need the chunks to be loaded from the disk
			$this->indexSearched = true;
			$portal = PortalManager::findIndexedPortal($this->target, $this->center, PortalManager::getSearchRadius($this->target));
			if ($portal !== null) {
				return $portal->getDestination($this->target);
			}
		}

		$ready = true;
		foreach ($this->chunks as [$chunkX, $chunkZ]) {
			if (!$this->target->populateChunk($chunkX, $chunkZ, true)) {
				$ready = false;
			}
		}
		if (!$ready) {
			if ($this->ticks < self::TIMEOUT_TICKS) {
				return null;
			}
			$centerChunk = $this->target->getChunk($this->center->getFloorX() >> Chunk::COORD_BIT_SIZE, $this->center->getFloorZ() >> Chunk::COORD_BIT_SIZE);
			if ($centerChunk === null || !$centerChunk->isPopulated()) {
				//the generator is too busy, don't build anything into terrain which will be overwritten later
				return $this->target->getSafeSpawn();
			}
		}

		switch ($this->mode) {
			case self::MODE_END_SPAWN:
				return PortalManager::prepareEndSpawn($this->target);
			case self::MODE_SPAWN:
				return $this->target->getSafeSpawn($this->center);
			default:
				$radius = PortalManager::getSearchRadius($this->target);
				$portal = PortalManager::scanLoadedChunks($this->target, $this->center, $radius) ?? PortalManager::createPortal($this->target, $this->center, PortalManager::getCreateRadius(), $this->axis);
				return $portal->getDestination($this->target);
		}
	}
}
