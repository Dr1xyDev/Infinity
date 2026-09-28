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

namespace pocketmine\level\generator\vanilla\snooze;

use function assert;
use function microtime;

/**
 * Manages a Threaded sleeper which can be waited on for notifications. Calls callbacks for attached notifiers when
 * notifications are received from the notifiers.
 */
class SleeperHandler
{
	/** @var ThreadedSleeper */
	private $threadedSleeper;

	/** @var SleeperNotifier[] */
	private $notifiers = [];
	/**
	 * @var callable[]
	 * this is stored separately from notifiers otherwise pthreads would break closures referencing variables
	 */
	private $handlers = [];

	/** @var int */
	private $nextSleeperId = 0;

	public function __construct()
	{
		$this->threadedSleeper = new ThreadedSleeper();
	}

	public function getThreadedSleeper() : ThreadedSleeper
	{
		return $this->threadedSleeper;
	}

	/**
	 * @param callable $handler Called when the notifier wakes the server up, of the signature `function() : void`
	 */
	public function addNotifier(SleeperNotifier $notifier, callable $handler) : void
	{
		$id = $this->nextSleeperId++;
		$notifier->attachSleeper($this->threadedSleeper, $id);
		$this->notifiers[$id] = $notifier;
		$this->handlers[$id] = $handler;
	}

	/**
	 * Removes a notifier from the sleeper. Note that this does not prevent the notifier waking the sleeper up - it just
	 * stops the notifier getting actions processed from the main thread.
	 */
	public function removeNotifier(SleeperNotifier $notifier) : void
	{
		unset($this->notifiers[$notifier->getSleeperId()], $this->handlers[$notifier->getSleeperId()]);
	}

	/**
	 * Sleeps until the given timestamp. Sleep may be interrupted by notifications, which will be processed before going
	 * back to sleep.
	 */
	public function sleepUntil(float $unixTime) : void
	{
		while (true) {
			$this->processNotifications();

			$sleepTime = (int) (($unixTime - microtime(true)) * 1000000);
			if ($sleepTime > 0) {
				$this->threadedSleeper->sleep($sleepTime);
			} else {
				break;
			}
		}
	}

	/**
	 * Blocks until notifications are received, then processes notifications. Will not sleep if notifications are
	 * already waiting.
	 */
	public function sleepUntilNotification() : void
	{
		$this->threadedSleeper->sleep(0);
		$this->processNotifications();
	}

	/**
	 * Processes any notifications from notifiers and calls handlers for received notifications.
	 */
	public function processNotifications() : void
	{
		while ($this->threadedSleeper->hasNotifications()) {
			$processed = 0;
			foreach ($this->notifiers as $id => $notifier) {
				if ($notifier->hasNotification()) {
					++$processed;

					$notifier->clearNotification();
					if (isset($this->notifiers[$id])) {
						/*
						 * Notifiers can end up getting removed due to a previous notifier's callback. Since a foreach
						 * iterates on a copy of the notifiers array, the removal isn't reflected by the foreach. This
						 * ensures that we do not attempt to fire callbacks for notifiers which have been removed.
						 */
						assert(isset($this->handlers[$id]));
						$this->handlers[$id]();
					}
				}
			}

			assert($processed > 0);

			$this->threadedSleeper->clearNotifications($processed);
		}
	}
}
