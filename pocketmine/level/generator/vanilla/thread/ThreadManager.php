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

namespace pocketmine\level\generator\vanilla\thread;

use pmmp\thread\ThreadSafe;
use pmmp\thread\ThreadSafeArray;

use function spl_object_id;

class ThreadManager extends ThreadSafe
{
	private static ?self $instance = null;

	public static function init() : void
	{
		self::$instance = new ThreadManager();
	}

	public static function getInstance() : ThreadManager
	{
		if (self::$instance === null) {
			self::$instance = new ThreadManager();
		}
		return self::$instance;
	}

	/** @phpstan-var ThreadSafeArray<int, Thread|Worker> */
	private ThreadSafeArray $threads;

	private function __construct()
	{
		$this->threads = new ThreadSafeArray();
	}

	public function add(Worker|Thread $thread) : void
	{
		$this->threads[spl_object_id($thread)] = $thread;
	}

	public function remove(Worker|Thread $thread) : void
	{
		unset($this->threads[spl_object_id($thread)]);
	}

	/**
	 * @return Worker[]|Thread[]
	 */
	public function getAll() : array
	{
		$array = [];
		/**
		 * @var Worker|Thread $thread
		 */
		foreach ($this->threads as $key => $thread) {
			$array[$key] = $thread;
		}

		return $array;
	}

	public function stopAll() : int
	{
		$logger = \GlobalLogger::get();

		$erroredThreads = 0;

		foreach ($this->getAll() as $thread) {
			$logger->debug("Stopping " . $thread->getThreadName() . " thread");
			try {
				$thread->quit();
				$logger->debug($thread->getThreadName() . " thread stopped successfully.");
			} catch (ThreadException $e) {
				++$erroredThreads;
				$logger->debug("Could not stop " . $thread->getThreadName() . " thread: " . $e->getMessage());
			}
		}

		return $erroredThreads;
	}
}
