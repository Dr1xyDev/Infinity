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

use Closure;
use InvalidArgumentException;
use pmmp\thread\Thread as NativeThread;
use pmmp\thread\ThreadSafeArray;
use pocketmine\Server;
use pocketmine\level\generator\vanilla\thread\log\ThreadSafeLogger;
use pocketmine\level\generator\vanilla\thread\ThreadSafeClassLoader;
use pocketmine\level\generator\vanilla\timings\Timings;
use pocketmine\level\generator\vanilla\utils\Utils;
use ReflectionClass;
use ReflectionException;

use function array_keys;
use function assert;
use function count;
use function get_class;
use function spl_object_hash;
use function time;

use const PHP_INT_MAX;

/**
 * Manages general-purpose worker threads used for processing asynchronous tasks, and the tasks submitted to those
 * workers.
 */
class AsyncPool
{
	private const WORKER_START_OPTIONS = NativeThread::INHERIT_INI | NativeThread::INHERIT_CONSTANTS;

	/** @var AsyncTask[] */
	private array $tasks = [];
	/** @var int[] */
	private array $taskWorkers = [];
	private int $nextTaskId = 1;

	/** @var AsyncWorker[] */
	private array $workers = [];
	/** @var int[] */
	private array $workerUsage = [];
	/** @var int[] */
	private array $workerLastUsed = [];

	/** @var Closure[] */
	private array $workerStartHooks = [];

	public function __construct(
		private Server                $server,
		private string                $name,
		protected int                 $workerMemoryLimit,
		private ThreadSafeClassLoader $classLoader,
		private ThreadSafeLogger      $logger
	) {
	}

	/**
	 * Returns the maximum size of the pool. Note that there may be less active workers than this number.
	 */
	public function getSize() : int
	{
		return $this->workerMemoryLimit;
	}

	/**
	 * Increases the maximum size of the pool to the specified amount. This does not immediately start new workers.
	 */
	public function increaseSize(int $newSize) : void
	{
		if ($newSize > $this->workerMemoryLimit) {
			$this->workerMemoryLimit = $newSize;
		}
	}

	/**
	 * Registers a Closure callback to be fired whenever a new worker is started by the pool.
	 * The signature should be `function(int $worker) : void`
	 *
	 * This function will call the hook for every already-running worker.
	 */
	public function addWorkerStartHook(Closure $hook) : void
	{
		Utils::validateCallableSignature(function (int $worker) : void {}, $hook);
		$this->workerStartHooks[spl_object_hash($hook)] = $hook;
		foreach ($this->workers as $i => $worker) {
			$hook($i);
		}
	}

	/**
	 * Removes a previously-registered callback listening for workers being started.
	 */
	public function removeWorkerStartHook(Closure $hook) : void
	{
		unset($this->workerStartHooks[spl_object_hash($hook)]);
	}

	/**
	 * Returns an array of IDs of currently running workers.
	 *
	 * @return int[]
	 */
	public function getRunningWorkers() : array
	{
		return array_keys($this->workers);
	}

	/**
	 * Fetches the worker with the specified ID, starting it if it does not exist, and firing any registered worker
	 * start hooks.
	 */
	private function getWorker(int $worker) : AsyncWorker
	{
		if (!isset($this->workers[$worker])) {
			$this->workerUsage[$worker] = 0;
			$this->workers[$worker] = new AsyncWorker($this->logger, $this->name, $worker);
			$this->workers[$worker]->setClassLoaders([$this->classLoader]);
			$this->workers[$worker]->start(self::WORKER_START_OPTIONS);

			foreach ($this->workerStartHooks as $hook) {
				$hook($worker);
			}
		}

		return $this->workers[$worker];
	}

	/**
	 * Submits an AsyncTask to an arbitrary worker.
	 */
	public function submitTaskToWorker(AsyncTask $task, int $workerId) : void
	{
		if ($workerId < 0 || $workerId >= $this->workerMemoryLimit) {
			throw new InvalidArgumentException("Invalid worker $workerId");
		}

		if ($task->getTaskId() === null) {
			$task->setTaskId($this->nextTaskId++);
		}

		$worker = $this->getWorker($workerId);

		$task->setWorker($worker);

		$worker->stack($task);

		$task->progressUpdates = new ThreadSafeArray();

		$this->tasks[$task->getTaskId()] = $task;
		$this->workerUsage[$workerId]++;
		$this->taskWorkers[$task->getTaskId()] = $workerId;
		$this->workerLastUsed[$workerId] = time();
	}

	/**
	 * Selects a worker ID to run a task.
	 *
	 * - if an idle worker is found, it will be selected
	 * - else, if the worker pool is not full, a new worker will be selected
	 * - else, the worker with the smallest backlog is chosen.
	 */
	public function selectWorker() : int
	{
		$worker = null;
		$minUsage = PHP_INT_MAX;
		foreach ($this->workerUsage as $i => $usage) {
			if ($usage < $minUsage) {
				$worker = $i;
				$minUsage = $usage;
				if ($usage === 0) {
					break;
				}
			}
		}
		if ($worker === null || ($minUsage > 0 && count($this->workers) < $this->workerMemoryLimit)) {
			//select a worker to start on the fly
			for ($i = 0; $i < $this->workerMemoryLimit; ++$i) {
				if (!isset($this->workers[$i])) {
					$worker = $i;
					break;
				}
			}
		}

		assert($worker !== null);

		return $worker;
	}

	public function submitClosure(Closure $fn, array $argv, ?int $workerId = null) : AsyncTaskPromise
	{
		$task = new PromiseResolveTask($promise = new AsyncTaskPromise($fn, $argv));

		if ($workerId === null) {
			$this->submitTask($task);
		} else {
			$this->submitTaskToWorker($task, $workerId);
		}

		return $promise;
	}

	public function submitTask(AsyncTask|Closure $task, array $argv = []) : mixed
	{
		if ($task instanceof Closure) {
			return $this->submitClosure($task, $argv);
		}

		if ($task->getTaskId() === null) {
			$task->setTaskId($this->nextTaskId++);
		}

		if (isset($this->tasks[$task->getTaskId()]) || $task->isGarbage()) {
			return null;
		}

		$worker = $this->selectWorker();
		$this->submitTaskToWorker($task, $worker);
		return $worker;
	}

	/**
	 * Removes a completed or crashed task from the pool.
	 */
	private function removeTask(AsyncTask $task, bool $force = false) : void
	{
		if (isset($this->taskWorkers[$task->getTaskId()])) {
			if (!$force && ($task->isRunning() || !$task->isGarbage())) {
				return;
			}
			$this->workerUsage[$this->taskWorkers[$task->getTaskId()]]--;
		}

		$task->removeDanglingStoredObjects();
		unset($this->tasks[$task->getTaskId()]);
		unset($this->taskWorkers[$task->getTaskId()]);
	}

	/**
	 * Removes all tasks from the pool, cancelling where possible. This will block until all tasks have been
	 * successfully deleted.
	 */
	public function removeTasks() : void
	{
		foreach ($this->workers as $worker) {
			/** @var AsyncTask $task */
			while (($task = $worker->unstack()) !== null) {
				//cancelRun() is not strictly necessary here, but it might be used to inform plugins of the task state
				//(i.e. it never executed).
				assert($task instanceof AsyncTask);
				$task->cancelRun();
				$this->removeTask($task, true);
			}
		}
		do {
			foreach ($this->tasks as $task) {
				$task->cancelRun();
				$this->removeTask($task);
			}

			if (count($this->tasks) > 0) {
				Server::microSleep(25000);
			}
		} while (count($this->tasks) > 0);

		for ($i = 0; $i < $this->workerMemoryLimit; ++$i) {
			$this->workerUsage[$i] = 0;
		}

		$this->taskWorkers = [];
		$this->tasks = [];

		$this->collectWorkers();
	}

	/**
	 * Collects garbage from running workers.
	 */
	private function collectWorkers() : void
	{
		foreach ($this->workers as $worker) {
			$worker->collect();
		}
	}

	/**
	 * Collects finished and/or crashed tasks from the workers, firing their on-completion hooks where appropriate.
	 *
	 * @throws ReflectionException
	 */
	public function collectTasks() : void
	{
		foreach ($this->tasks as $task) {
			$task->checkProgressUpdates($this->server);
			if ($task->isGarbage() && !$task->isRunning() && !$task->isCrashed()) {
				if (!$task->hasCancelledRun()) {
					/*
					 * It's possible for a task to submit a progress update and then finish before the progress
					 * update is detected by the parent thread, so here we consume any missed updates.
					 *
					 * When this happens, it's possible for a progress update to arrive between the previous
					 * checkProgressUpdates() call and the next isGarbage() call, causing progress updates to be
					 * lost. Thus, it's necessary to do one last check here to make sure all progress updates have
					 * been consumed before completing.
					 */
					$this->checkTaskProgressUpdates($task);
					Timings::getAsyncTaskCompletionTimings($task)->time(function () use ($task) : void {
						$task->onCompletion($this->server);
					});
					if ($task->removeDanglingStoredObjects()) {
						$this->logger->notice("AsyncTask " . get_class($task) . " stored local complex data but did not remove them after completion");
					}
				}

				$this->removeTask($task);
			} elseif ($task->isCrashed()) {
				$this->logger->critical("Could not execute asynchronous task " . (new ReflectionClass($task))->getShortName() . ": Task crashed");
				$this->removeTask($task, true);
			}
		}

		$this->collectWorkers();
	}

	public function shutdownUnusedWorkers() : int
	{
		$ret = 0;
		$time = time();
		foreach ($this->workerUsage as $i => $usage) {
			if ($usage === 0 && (!isset($this->workerLastUsed[$i]) || $this->workerLastUsed[$i] + 300 < $time)) {
				$this->workers[$i]->quit();
				unset($this->workers[$i], $this->workerUsage[$i], $this->workerLastUsed[$i]);
				$ret++;
			}
		}

		return $ret;
	}

	/**
	 * Cancels all pending tasks and shuts down all the workers in the pool.
	 */
	public function shutdown() : void
	{
		$this->collectTasks();
		$this->removeTasks();
		foreach ($this->workers as $worker) {
			$worker->quit();
		}
		$this->workers = [];
		$this->workerLastUsed = [];
	}

	private function checkTaskProgressUpdates(AsyncTask $task) : void
	{
		Timings::getAsyncTaskProgressUpdateTimings($task)->time(function () use ($task) : void {
			$task->checkProgressUpdates($this->server);
		});
	}
}
