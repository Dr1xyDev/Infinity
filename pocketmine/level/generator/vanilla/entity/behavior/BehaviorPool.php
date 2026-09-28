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

namespace pocketmine\level\generator\vanilla\entity\behavior;

use function count;
use function spl_object_id;

class BehaviorPool
{
	/** @var BehaviorEntry[] */
	protected $behaviorEntries = [];
	/** @var BehaviorEntry[] */
	protected $workingBehaviors = [];
	/** @var int */
	protected $tickRate = 10;
	/** @var int */
	protected $tickCounter = 0;

	public function setBehavior(int $priority, Behavior $behavior) : void
	{
		$this->behaviorEntries[spl_object_id($behavior)] = new BehaviorEntry($priority, $behavior);
	}

	public function removeBehavior(Behavior $behavior) : void
	{
		unset($this->behaviorEntries[spl_object_id($behavior)]);
	}

	/**
	 * Updates behaviors
	 */
	public function onUpdate() : bool
	{
		if ($this->tickCounter++ % $this->tickRate === 0) {
			foreach ($this->behaviorEntries as $id => $entry) {
				$behavior = $entry->getBehavior();

				if (isset($this->workingBehaviors[$id])) {
					if (!$this->canUse($entry) || !$behavior->canContinue()) {
						$behavior->onEnd();

						unset($this->workingBehaviors[$id]);
					}
				}elseif ($this->canUse($entry) && $behavior->canStart()) {
					$behavior->onStart();

					$this->workingBehaviors[$id] = $entry;
				}
			}
		} else {
			foreach ($this->workingBehaviors as $id => $entry) {
				if (!$entry->getBehavior()->canContinue()) {
					$entry->getBehavior()->onEnd();

					unset($this->workingBehaviors[$id]);
				}else{
					$entry->getBehavior()->onTick();
				}
			}
		}

		return count($this->workingBehaviors) > 0;
	}

	public function canUse(BehaviorEntry $entry) : bool
	{
		foreach ($this->behaviorEntries as $id => $behaviorEntry) {
			if ($behaviorEntry->getBehavior() !== $entry->getBehavior()) {
				if ($entry->getPriority() >= $behaviorEntry->getPriority()) {
					if (!$this->theyCanWorkCompatible($entry->getBehavior(), $behaviorEntry->getBehavior()) && isset($this->workingBehaviors[$id])) {
						return false;
					}
				} elseif (!$behaviorEntry->getBehavior()->isMutable() && isset($this->workingBehaviors[$id])) {
					return false;
				}
			}
		}

		return true;
	}

	public function theyCanWorkCompatible(Behavior $b1, Behavior $b2) : bool
	{
		return ($b1->getMutexBits() & $b2->getMutexBits()) === 0;
	}

	public function getTickRate() : int
	{
		return $this->tickRate;
	}

	public function setTickRate(int $tickRate) : void
	{
		$this->tickRate = $tickRate;
	}

	/**
	 * @return BehaviorEntry[]
	 */
	public function getBehaviorEntries() : array
	{
		return $this->behaviorEntries;
	}

	public function clearBehaviors() : void
	{
		$this->behaviorEntries = [];
		$this->workingBehaviors = [];
	}
}
