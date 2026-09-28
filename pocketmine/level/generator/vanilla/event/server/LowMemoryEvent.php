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

namespace pocketmine\level\generator\vanilla\event\server;

use pocketmine\level\generator\vanilla\utils\Process;

/**
 * Called when the server is in a low-memory state as defined by the properties
 * Plugins should free caches or other non-essential data.
 */
class LowMemoryEvent extends ServerEvent
{
	/** @var int */
	private $memory;
	/** @var int */
	private $memoryLimit;
	/** @var int */
	private $triggerCount;
	/** @var bool */
	private $global;

	public function __construct(int $memory, int $memoryLimit, bool $isGlobal = false, int $triggerCount = 0)
	{
		$this->memory = $memory;
		$this->memoryLimit = $memoryLimit;
		$this->global = $isGlobal;
		$this->triggerCount = $triggerCount;
	}

	/**
	 * Returns the memory usage at the time of the event call (in bytes)
	 */
	public function getMemory() : int
	{
		return $this->memory;
	}

	/**
	 * Returns the memory limit defined (in bytes)
	 */
	public function getMemoryLimit() : int
	{
		return $this->memoryLimit;
	}

	/**
	 * Returns the times this event has been called in the current low-memory state
	 */
	public function getTriggerCount() : int
	{
		return $this->triggerCount;
	}

	public function isGlobal() : bool
	{
		return $this->global;
	}

	/**
	 * Amount of memory already freed
	 */
	public function getMemoryFreed() : int
	{
		$usage = Process::getAdvancedMemoryUsage();
		return $this->getMemory() - ($this->isGlobal() ? $usage[1] : $usage[0]);
	}
}
