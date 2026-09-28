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

namespace pocketmine\level\generator\vanilla\plugin;

use pocketmine\level\generator\vanilla\event\Cancellable;
use pocketmine\level\generator\vanilla\event\Event;
use pocketmine\level\generator\vanilla\event\Listener;
use pocketmine\level\generator\vanilla\timings\TimingsHandler;

class RegisteredListener
{
	public function __construct(
		private Listener $listener,
		private EventExecutor $executor,
		private int $priority,
		private Plugin $plugin,
		private bool $ignoreCancelled,
		private TimingsHandler $timings
	) {
	}

	public function getListener() : Listener
	{
		return $this->listener;
	}

	public function getPlugin() : Plugin
	{
		return $this->plugin;
	}

	public function getPriority() : int
	{
		return $this->priority;
	}

	public function callEvent(Event $event) : void
	{
		if ($event instanceof Cancellable && $event->isCancelled() && $this->isIgnoringCancelled()) {
			return;
		}
		$this->timings->startTiming();
		try {
			$this->executor->execute($this->listener, $event);
		} finally {
			$this->timings->stopTiming();
		}
	}

	public function isIgnoringCancelled() : bool
	{
		return $this->ignoreCancelled;
	}
}
