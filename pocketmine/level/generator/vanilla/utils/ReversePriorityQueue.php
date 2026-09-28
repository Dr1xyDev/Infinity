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

use SplPriorityQueue;

/**
 * @phpstan-template TPriority
 * @phpstan-template TValue
 * @phpstan-extends SplPriorityQueue<TPriority, TValue>
 */
class ReversePriorityQueue extends SplPriorityQueue
{
	/**
	 * @param mixed $priority1
	 * @param mixed $priority2
	 *
	 * @phpstan-param TPriority $priority1
	 * @phpstan-param TPriority $priority2
	 */
	public function compare($priority1, $priority2) : int
	{
		//TODO: this will crash if non-numeric priorities are used
		return (int) -($priority1 - $priority2);
	}
}
