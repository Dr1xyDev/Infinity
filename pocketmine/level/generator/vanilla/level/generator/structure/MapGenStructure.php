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

namespace pocketmine\level\generator\vanilla\level\generator\structure;

use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\utils\Random;
use function array_key_exists;
use function array_key_first;
use function count;

/**
 * Base of structures which are laid out deterministically from the world seed (vanilla MapGenStructure).
 * Every chunk being generated looks for structure starts in the chunks around it and places the parts of
 * them which are inside of it, so no neighbour chunk has to exist.
 */
abstract class MapGenStructure
{
	/** Chunks around a chunk which may contain the start of a structure reaching into it */
	public const RANGE = 8;

	private const MAX_CACHED_STARTS = 64;
	private const MAX_CACHED_EMPTY = 8192;

	/** @var StructureStart[] */
	private array $starts = [];
	/** @var true[] chunks known to have no start */
	private array $empty = [];
	private int $seedX;
	private int $seedZ;

	public function __construct(protected int $worldSeed)
	{
		$random = new Random($worldSeed);
		$this->seedX = $random->nextSignedInt() | 1;
		$this->seedZ = $random->nextSignedInt() | 1;
	}

	abstract protected function canSpawnStructureAtCoords(int $chunkX, int $chunkZ, Random $random) : bool;

	abstract protected function createStart(int $chunkX, int $chunkZ, Random $random) : StructureStart;

	public function getStart(int $chunkX, int $chunkZ) : ?StructureStart
	{
		$key = Level::chunkHash($chunkX, $chunkZ);
		if (isset($this->empty[$key])) {
			return null;
		}
		if (array_key_exists($key, $this->starts)) {
			return $this->starts[$key];
		}

		$random = new Random(($chunkX * $this->seedX) ^ ($chunkZ * $this->seedZ) ^ $this->worldSeed);
		$random->nextInt();
		$start = null;
		if ($this->canSpawnStructureAtCoords($chunkX, $chunkZ, $random)) {
			$start = $this->createStart($chunkX, $chunkZ, $random);
			if (!$start->isValid()) {
				$start = null;
			}
		}

		if ($start === null) {
			if (count($this->empty) >= self::MAX_CACHED_EMPTY) {
				$this->empty = [];
			}
			$this->empty[$key] = true;
		} else {
			if (count($this->starts) >= self::MAX_CACHED_STARTS) {
				unset($this->starts[array_key_first($this->starts)]);
			}
			$this->starts[$key] = $start;
		}
		return $start;
	}

	/**
	 * Places everything of the structures around which is inside of the chunk
	 */
	public function generate(StructureWorld $world, StructureBoundingBox $chunkBox, int $chunkX, int $chunkZ) : void
	{
		$random = new Random(($chunkX * 0x4f9939f5) ^ ($chunkZ * 0x1ef1565b) ^ $this->worldSeed);
		for ($x = $chunkX - self::RANGE; $x <= $chunkX + self::RANGE; ++$x) {
			for ($z = $chunkZ - self::RANGE; $z <= $chunkZ + self::RANGE; ++$z) {
				$start = $this->getStart($x, $z);
				if ($start !== null && $start->getBoundingBox()->intersectsWith($chunkBox)) {
					$start->generateStructure($world, $random, $chunkBox);
				}
			}
		}
	}

	/**
	 * Returns the structure which has a piece at the given position, if any
	 */
	public function getStructureAt(int $x, int $y, int $z) : ?StructureStart
	{
		$chunkX = $x >> Chunk::COORD_BIT_SIZE;
		$chunkZ = $z >> Chunk::COORD_BIT_SIZE;
		for ($cx = $chunkX - self::RANGE; $cx <= $chunkX + self::RANGE; ++$cx) {
			for ($cz = $chunkZ - self::RANGE; $cz <= $chunkZ + self::RANGE; ++$cz) {
				$start = $this->getStart($cx, $cz);
				if ($start !== null && $start->getBoundingBox()->isVecInside($x, $y, $z)) {
					foreach ($start->getComponents() as $component) {
						if ($component->getBoundingBox()->isVecInside($x, $y, $z)) {
							return $start;
						}
					}
				}
			}
		}
		return null;
	}
}
