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
use pocketmine\level\generator\vanilla\utils\Utils;

/**
 * Task implementation which allows closures to be called by a scheduler.
 *
 * Example usage:
 *
 * ```
 * TaskScheduler->scheduleTask(new ClosureTask(function(int $currentTick) : void{
 *     echo "HI on $currentTick\n";
 * });
 * ```
 */
class ClosureTask extends Task
{
	/** @var Closure */
	private $closure;

	/**
	 * @param Closure $closure Must accept only ONE parameter, $currentTick
	 */
	public function __construct(Closure $closure)
	{
		Utils::validateCallableSignature(function (int $currentTick) : void {}, $closure);
		$this->closure = $closure;
	}

	public function getName() : string
	{
		return Utils::getNiceClosureName($this->closure);
	}

	public function onRun(int $currentTick)
	{
		($this->closure)($currentTick);
	}
}
