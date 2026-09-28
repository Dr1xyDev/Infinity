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

use function function_exists;
use function pcntl_async_signals;
use function pcntl_signal;
use function sapi_windows_set_ctrl_handler;

use const PHP_WINDOWS_EVENT_CTRL_BREAK;
use const PHP_WINDOWS_EVENT_CTRL_C;
use const SIG_DFL;
use const SIGHUP;
use const SIGINT;
use const SIGTERM;

final class SignalHandler
{
	/** @phpstan-var (\Closure(int) : void)|null */
	private ?\Closure $interruptCallback;

	/**
	 * @phpstan-param \Closure() : void $interruptCallback
	 */
	public function __construct(\Closure $interruptCallback)
	{
		$this->interruptCallback = $interruptCallback;

		if (function_exists('sapi_windows_set_ctrl_handler')) {
			sapi_windows_set_ctrl_handler($this->interruptCallback = function (int $signo) use ($interruptCallback) : void {
				if ($signo === PHP_WINDOWS_EVENT_CTRL_C || $signo === PHP_WINDOWS_EVENT_CTRL_BREAK) {
					$interruptCallback();
				}
			});
		} elseif (function_exists('pcntl_signal')) {
			foreach ([
				SIGTERM,
				SIGINT,
				SIGHUP
			] as $signal) {
				pcntl_signal($signal, $this->interruptCallback = fn (int $signo) => $interruptCallback());
			}
			pcntl_async_signals(true);
		} else {
			//no supported signal handlers :(
		}
	}

	public function unregister() : void
	{
		if (function_exists('sapi_windows_set_ctrl_handler')) {
			sapi_windows_set_ctrl_handler($this->interruptCallback, false);
		} elseif (function_exists('pcntl_signal')) {
			foreach ([
				SIGTERM,
				SIGINT,
				SIGHUP
			] as $signal) {
				pcntl_signal($signal, SIG_DFL);
			}
		}
	}
}
