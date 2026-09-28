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

namespace pocketmine\level\generator\vanilla\scheduler;

use pocketmine\level\generator\vanilla\timings\Timings;
use pocketmine\level\generator\vanilla\timings\TimingsHandler;

class TaskHandler
{
	/** @var Task */
	protected $task;

	/** @var int */
	protected $taskId;

	/** @var int */
	protected $delay;

	/** @var int */
	protected $period;

	/** @var int */
	protected $nextRun;

	/** @var bool */
	protected $cancelled = false;

	/** @var TimingsHandler */
	private $timings;

	/** @var string */
	private $taskName;
	/** @var string */
	private $ownerName;

	public function __construct(Task $task, int $taskId, int $delay = -1, int $period = -1, ?string $ownerName = null)
	{
		$this->task = $task;
		$this->taskId = $taskId;
		$this->delay = $delay;
		$this->period = $period;
		$this->taskName = $task->getName();
		$this->ownerName = $ownerName ?? "Unknown";
		$this->timings = Timings::getScheduledTaskTimings($this, $period);
		$this->task->setHandler($this);
	}

	public function isCancelled() : bool
	{
		return $this->cancelled;
	}

	public function getNextRun() : int
	{
		return $this->nextRun;
	}

	public function setNextRun(int $ticks)
	{
		$this->nextRun = $ticks;
	}

	public function getTaskId() : int
	{
		return $this->taskId;
	}

	public function getTask() : Task
	{
		return $this->task;
	}

	public function getDelay() : int
	{
		return $this->delay;
	}

	public function isDelayed() : bool
	{
		return $this->delay > 0;
	}

	public function isRepeating() : bool
	{
		return $this->period > 0;
	}

	public function getPeriod() : int
	{
		return $this->period;
	}

	public function cancel()
	{
		try {
			if (!$this->isCancelled()) {
				$this->task->onCancel();
			}
		} finally {
			$this->remove();
		}
	}

	public function remove()
	{
		$this->cancelled = true;
		$this->task->setHandler(null);
	}

	public function run(int $currentTick)
	{
		$this->timings->startTiming();
		try {
			$this->task->onRun($currentTick);
		} finally {
			$this->timings->stopTiming();
		}
	}

	public function getTaskName() : string
	{
		return $this->taskName;
	}

	public function getOwnerName() : string
	{
		return $this->ownerName;
	}
}
