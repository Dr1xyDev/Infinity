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
use pocketmine\level\generator\vanilla\utils\AssumptionFailedError;
use function end;
use function ksort;
use function time;
use const SORT_NUMERIC;

final class RegionGarbageMap
{
	/** @var RegionLocationTableEntry[] */
	private $entries = [];
	/** @var bool */
	private $clean = false;

	/**
	 * @param RegionLocationTableEntry[] $entries
	 */
	public function __construct(array $entries)
	{
		foreach ($entries as $entry) {
			$this->entries[$entry->getFirstSector()] = $entry;
		}
	}

	/**
	 * @param RegionLocationTableEntry[]|null[] $locationTable
	 */
	public static function buildFromLocationTable(array $locationTable) : self
	{
		/** @var RegionLocationTableEntry[] $usedMap */
		$usedMap = [];
		foreach ($locationTable as $entry) {
			if ($entry === null) {
				continue;
			}
			if (isset($usedMap[$entry->getFirstSector()])) {
				throw new AssumptionFailedError("Overlapping entries detected");
			}
			$usedMap[$entry->getFirstSector()] = $entry;
		}

		ksort($usedMap, SORT_NUMERIC);

		/** @var RegionLocationTableEntry[] $garbageMap */
		$garbageMap = [];

		/** @var RegionLocationTableEntry|null $prevEntry */
		$prevEntry = null;
		foreach ($usedMap as $firstSector => $entry) {
			$prevEndPlusOne = ($prevEntry !== null ? $prevEntry->getLastSector() + 1 : RegionLoader::FIRST_SECTOR);
			$currentStart = $entry->getFirstSector();
			if ($prevEndPlusOne < $currentStart) {
				//found a gap in the table
				$garbageMap[$prevEndPlusOne] = new RegionLocationTableEntry($prevEndPlusOne, $currentStart - $prevEndPlusOne, 0);
			} elseif ($prevEndPlusOne > $currentStart) {
				//current entry starts inside the previous. This would be a bug since RegionLoader should prevent this
				throw new AssumptionFailedError("Overlapping entries detected");
			}
			$prevEntry = $entry;
		}

		return new self($garbageMap);
	}

	/**
	 * @return RegionLocationTableEntry[]
	 * @phpstan-return array<int, RegionLocationTableEntry>
	 */
	public function getArray() : array
	{
		if (!$this->clean) {
			ksort($this->entries, SORT_NUMERIC);

			/** @var int|null $prevIndex */
			$prevIndex = null;
			foreach ($this->entries as $k => $entry) {
				if ($prevIndex !== null && $this->entries[$prevIndex]->getLastSector() + 1 === $entry->getFirstSector()) {
					//this SHOULD overwrite the previous index and not appear at the end
					$this->entries[$prevIndex] = new RegionLocationTableEntry(
						$this->entries[$prevIndex]->getFirstSector(),
						$this->entries[$prevIndex]->getSectorCount() + $entry->getSectorCount(),
						0
					);
					unset($this->entries[$k]);
				} else {
					$prevIndex = $k;
				}
			}
			$this->clean = true;
		}
		return $this->entries;
	}

	public function add(RegionLocationTableEntry $entry) : void
	{
		if (isset($this->entries[$k = $entry->getFirstSector()])) {
			throw new InvalidArgumentException("Overlapping entry starting at " . $k);
		}
		$this->entries[$k] = $entry;
		$this->clean = false;
	}

	public function remove(RegionLocationTableEntry $entry) : void
	{
		if (isset($this->entries[$k = $entry->getFirstSector()])) {
			//removal doesn't affect ordering and shouldn't affect fragmentation
			unset($this->entries[$k]);
		}
	}

	public function end() : ?RegionLocationTableEntry
	{
		$array = $this->getArray();
		$end = end($array);
		return $end !== false ? $end : null;
	}

	public function allocate(int $newSize) : ?RegionLocationTableEntry
	{
		foreach ($this->getArray() as $start => $candidate) {
			$candidateSize = $candidate->getSectorCount();
			if ($candidateSize < $newSize) {
				continue;
			}

			$newLocation = new RegionLocationTableEntry($candidate->getFirstSector(), $newSize, time());
			$this->remove($candidate);

			if ($candidateSize > $newSize) { //we're not using the whole area, just take part of it
				$newGarbageStart = $candidate->getFirstSector() + $newSize;
				$newGarbageSize = $candidateSize - $newSize;
				$this->add(new RegionLocationTableEntry($newGarbageStart, $newGarbageSize, 0));
			}
			return $newLocation;

		}

		return null;
	}
}
