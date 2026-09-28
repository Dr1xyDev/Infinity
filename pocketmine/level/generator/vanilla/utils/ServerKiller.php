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

namespace pocketmine\level\generator\vanilla\utils;

use pocketmine\level\generator\vanilla\thread\Thread;

use function hrtime;
use function intdiv;

class ServerKiller extends Thread
{
	private bool $stopped = false;

	public function __construct(
		public int $time = 15
	) {
	}

	protected function onRun() : void
	{
		$start = hrtime(true);
		$remaining = $this->time * 1_000_000;
		$this->synchronized(function () use (&$remaining, $start) : void {
			while (!$this->stopped && $remaining > 0) {
				$this->wait($remaining);
				$remaining -= intdiv(hrtime(true) - $start, 1000);
			}
		});
		if ($remaining <= 0) {
			echo "\nTook too long to stop, server was killed forcefully!\n";
			@Process::kill(Process::pid());
		}
	}

	public function quit() : void
	{
		$this->synchronized(function () : void {
			$this->stopped = true;
			$this->notify();
		});
		parent::quit();
	}

	public function getThreadName() : string
	{
		return "Server Killer";
	}
}
