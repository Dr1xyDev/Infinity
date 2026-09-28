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

use pmmp\thread\Thread as NativeThread;
use pocketmine\level\generator\vanilla\scheduler\AsyncTask;

/**
 * Specialized Thread class aimed at PocketMine-MP-related usages. It handles setting up autoloading and error handling.
 *
 * Note: You probably don't need a thread unless you're doing something in it that's expected to last a long time (or
 * indefinitely).
 * For CPU-demanding tasks that take a short amount of time, consider using AsyncTasks instead to make better use of the
 * CPU.
 * @see AsyncTask
 */
abstract class Thread extends NativeThread
{
	use CommonThreadPartsTrait;
}
