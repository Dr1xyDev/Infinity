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

/**
 * Task scheduling related classes
 */

namespace pocketmine\level\generator\vanilla\scheduler;

use InvalidStateException;
use pocketmine\Server;
use pocketmine\level\generator\vanilla\utils\ReversePriorityQueue;

class TaskScheduler
{
	/** @var string|null */
	private $owner;

	/** @var bool */
	private $enabled = true;

	/** @var ReversePriorityQueue<Task> */
	protected $queue;

	/** @var TaskHandler[] */
	protected $tasks = [];

	/** @var int */
	private $ids = 1;

	/** @var int */
	protected $currentTick = 0;

	public function __construct(?string $owner = null)
	{
		$this->owner = $owner;
		$this->queue = new ReversePriorityQueue();
	}

	/**
	 * @return TaskHandler
	 */
	public function scheduleTask(Task $task)
	{
		return $this->addTask($task, -1, -1);
	}

	/**
	 * @return TaskHandler
	 */
	public function scheduleDelayedTask(Task $task, int $delay)
	{
		return $this->addTask($task, $delay, -1);
	}

	/**
	 * @return TaskHandler
	 */
	public function scheduleRepeatingTask(Task $task, int $period)
	{
		return $this->addTask($task, -1, $period);
	}

	/**
	 * @return TaskHandler
	 */
	public function scheduleDelayedRepeatingTask(Task $task, int $delay, int $period)
	{
		return $this->addTask($task, $delay, $period);
	}

	public function cancelTask(int $taskId)
	{
		if (isset($this->tasks[$taskId])) {
			try {
				$this->tasks[$taskId]->cancel();
			} finally {
				unset($this->tasks[$taskId]);
			}
		}
	}

	public function cancelAllTasks()
	{
		foreach ($this->tasks as $id => $task) {
			$this->cancelTask($id);
		}
		$this->tasks = [];
		while (!$this->queue->isEmpty()) {
			$this->queue->extract();
		}
		$this->ids = 1;
	}

	public function isQueued(int $taskId) : bool
	{
		return isset($this->tasks[$taskId]);
	}

	/**
	 * @return TaskHandler
	 *
	 * @throws InvalidStateException
	 */
	private function addTask(Task $task, int $delay, int $period)
	{
		if (!$this->enabled) {
			throw new InvalidStateException("Tried to schedule task to disabled scheduler");
		}

		if ($delay <= 0) {
			$delay = -1;
		}

		if ($period <= -1) {
			$period = -1;
		} elseif ($period < 1) {
			$period = 1;
		}

		return $this->handle(new TaskHandler($task, $this->nextId(), $delay, $period, $this->owner));
	}

	private function handle(TaskHandler $handler) : TaskHandler
	{
		if ($handler->isDelayed()) {
			$nextRun = $this->currentTick + $handler->getDelay();
		} else {
			$nextRun = $this->currentTick;
		}

		$handler->setNextRun($nextRun);
		$this->tasks[$handler->getTaskId()] = $handler;
		$this->queue->insert($handler, $nextRun);

		return $handler;
	}

	public function shutdown() : void
	{
		$this->enabled = false;
		$this->cancelAllTasks();
	}

	public function setEnabled(bool $enabled) : void
	{
		$this->enabled = $enabled;
	}

	public function mainThreadHeartbeat(int $currentTick)
	{
		$this->currentTick = $currentTick;
		while ($this->isReady($this->currentTick)) {
			/** @var TaskHandler $task */
			$task = $this->queue->extract();
			if ($task->isCancelled()) {
				unset($this->tasks[$task->getTaskId()]);
				continue;
			}
			try{
				$task->run($this->currentTick);
			}catch (\Throwable $e){
				Server::getInstance()->getLogger()->critical("Could not execute task " . $task->getTaskName() . ": " . $e->getMessage());
				Server::getInstance()->getLogger()->logException($e);
			}
			if ($task->isRepeating()) {
				$task->setNextRun($this->currentTick + $task->getPeriod());
				$this->queue->insert($task, $this->currentTick + $task->getPeriod());
			} else {
				$task->remove();
				unset($this->tasks[$task->getTaskId()]);
			}
		}
	}

	private function isReady(int $currentTick) : bool
	{
		return !$this->queue->isEmpty() && $this->queue->current()->getNextRun() <= $currentTick;
	}

	private function nextId() : int
	{
		return $this->ids++;
	}
}
