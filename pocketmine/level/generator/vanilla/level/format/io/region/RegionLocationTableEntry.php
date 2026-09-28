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

namespace pocketmine\level\generator\vanilla\level\format\io\region;

use InvalidArgumentException;
use function range;

class RegionLocationTableEntry
{
	/** @var int */
	private $firstSector;
	/** @var int */
	private $sectorCount;
	/** @var int */
	private $timestamp;

	/**
	 * @throws InvalidArgumentException
	 */
	public function __construct(int $firstSector, int $sectorCount, int $timestamp)
	{
		if ($firstSector < 0 || $firstSector >= 2 ** 24) {
			throw new InvalidArgumentException("Start sector must be positive, got $firstSector");
		}
		$this->firstSector = $firstSector;
		if ($sectorCount < 1) {
			throw new InvalidArgumentException("Sector count must be positive, got $sectorCount");
		}
		$this->sectorCount = $sectorCount;
		$this->timestamp = $timestamp;
	}

	public function getFirstSector() : int
	{
		return $this->firstSector;
	}

	public function getLastSector() : int
	{
		return $this->firstSector + $this->sectorCount - 1;
	}

	/**
	 * Returns an array of sector offsets reserved by this chunk.
	 * @return int[]
	 */
	public function getUsedSectors() : array
	{
		return range($this->getFirstSector(), $this->getLastSector());
	}

	public function getSectorCount() : int
	{
		return $this->sectorCount;
	}

	public function getTimestamp() : int
	{
		return $this->timestamp;
	}

	public function overlaps(RegionLocationTableEntry $other) : bool
	{
		$overlapCheck = static function (RegionLocationTableEntry $entry1, RegionLocationTableEntry $entry2) : bool {
			$entry1Last = $entry1->getLastSector();
			$entry2Last = $entry2->getLastSector();

			return (
				($entry2->firstSector >= $entry1->firstSector && $entry2->firstSector <= $entry1Last) ||
				($entry2Last >= $entry1->firstSector && $entry2Last <= $entry1Last)
			);
		};
		return $overlapCheck($this, $other) || $overlapCheck($other, $this);
	}
}
