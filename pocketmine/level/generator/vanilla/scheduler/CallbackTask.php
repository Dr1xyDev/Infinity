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

use function call_user_func_array;

/**
 * Allows the creation of simple callbacks with extra data
 * The last parameter in the callback will be this object
 *
 * If you want to do a task in a Plugin, consider extending PluginTask to your needs
 *
 * @deprecated
 * Do NOT use this anymore, it was deprecated a long time ago at PocketMine
 * and will be removed at some stage in the future.
 */
class CallbackTask extends Task
{
	/** @var callable */
	protected $callable;

	/** @var array */
	protected $args;

	public function __construct(callable $callable, array $args = [])
	{
		$this->callable = $callable;
		$this->args = $args;
		$this->args[] = $this;
	}

	public function getCallable() : callable
	{
		return $this->callable;
	}

	public function onRun(int $currentTick)
	{
		call_user_func_array($this->callable, $this->args);
	}
}
