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

use pmmp\thread\Worker as NativeWorker;
use pocketmine\level\generator\vanilla\scheduler\AsyncTask;

/**
 * Specialized Worker class for PocketMine-MP-related use cases. It handles setting up autoloading and error handling.
 *
 * Workers are a special type of thread which execute tasks passed to them during their lifetime. Since creating a new
 * thread has a high resource cost, workers can be kept around and reused for lots of short-lived tasks.
 *
 * As a plugin developer, you'll rarely (if ever) actually need to use this class directly.
 * If you want to run tasks on other CPU cores, check out AsyncTask first.
 * @see AsyncTask
 */
abstract class Worker extends NativeWorker
{
	use CommonThreadPartsTrait;
}
