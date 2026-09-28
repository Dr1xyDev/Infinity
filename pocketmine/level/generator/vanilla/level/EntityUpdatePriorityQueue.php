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

use function array_keys;
use function count;
use function max;

class EntityUpdatePriorityQueue
{
	/** @var int[] */
	private array $alwaysUpdateIds = [];
	/** @var int[] */
	private array $priorityMap = [];
	/** @var int[][] */
	private array $buckets = [];
	/** @var int[] */
	private array $updatedThisTick = [];
	private int $maxPriority = 0;

	public function addAlways(int $id) : void
	{
		if (!isset($this->alwaysUpdateIds[$id])) {
			$this->alwaysUpdateIds[$id] = true;
			$this->removeFromPriorityQueue($id);
		}
	}

	public function getAlwaysUpdateIds() : array
	{
		return $this->alwaysUpdateIds;
	}

	public function add(int $id) : void
	{
		if (isset($this->alwaysUpdateIds[$id]) || isset($this->priorityMap[$id])) {
			return;
		}
		$this->insertWithPriority($id, 0);
	}

	public function beginTick() : void
	{
		$this->updatedThisTick = [];

		if (empty($this->priorityMap)) {
			return;
		}

		$newBuckets = [];
		$newMax = 0;
		foreach ($this->buckets as $priority => $ids) {
			$newP = $priority + 1;
			$newBuckets[$newP] = $ids;
			if ($newP > $newMax) {
				$newMax = $newP;
			}
		}
		$this->buckets = $newBuckets;
		$this->maxPriority = $newMax;

		foreach ($this->priorityMap as $id => $_) {
			$this->priorityMap[$id]++;
		}
	}

	public function markUpdated(int $id) : void
	{
		if (!isset($this->priorityMap[$id])) {
			return;
		}

		$this->updatedThisTick[$id] = true;

		$oldPriority = $this->priorityMap[$id];
		if ($oldPriority !== 0) {
			$this->removeFromBucket($id, $oldPriority);
			$this->insertWithPriority($id, 0);
		}
	}

	public function pop() : ?int
	{
		for ($p = $this->maxPriority; $p >= 0; $p--) {
			if (empty($this->buckets[$p])) {
				unset($this->buckets[$p]);
				continue;
			}

			foreach ($this->buckets[$p] as $id => $_) {
				if (isset($this->updatedThisTick[$id])) {
					continue;
				}

				unset($this->buckets[$p][$id]);
				if (empty($this->buckets[$p])) {
					unset($this->buckets[$p]);
				}
				unset($this->priorityMap[$id]);
				$this->maxPriority = empty($this->buckets) ? 0 : max(array_keys($this->buckets));
				return $id;
			}
		}

		return null;
	}

	public function removeAny(int $id) : void
	{
		unset($this->alwaysUpdateIds[$id]);
		unset($this->updatedThisTick[$id]);
		$this->removeFromPriorityQueue($id);
	}

	public function contains(int $id) : bool
	{
		return isset($this->alwaysUpdateIds[$id]) || isset($this->priorityMap[$id]);
	}

	public function priorityQueueSize() : int
	{
		return count($this->priorityMap);
	}

	private function insertWithPriority(int $id, int $priority) : void
	{
		$this->priorityMap[$id] = $priority;
		$this->buckets[$priority][$id] = true;
		if ($priority > $this->maxPriority) {
			$this->maxPriority = $priority;
		}
	}

	private function removeFromBucket(int $id, int $priority) : void
	{
		unset($this->buckets[$priority][$id]);
		if (empty($this->buckets[$priority])) {
			unset($this->buckets[$priority]);
			if ($priority === $this->maxPriority) {
				$this->recalcMaxPriority();
			}
		}
	}

	private function removeFromPriorityQueue(int $id) : void
	{
		if (!isset($this->priorityMap[$id])) {
			return;
		}
		$priority = $this->priorityMap[$id];
		unset($this->priorityMap[$id]);
		$this->removeFromBucket($id, $priority);
	}

	private function recalcMaxPriority() : void
	{
		$this->maxPriority = 0;
		if (!empty($this->buckets)) {
			$this->maxPriority = max(array_keys($this->buckets));
		}
	}
}
