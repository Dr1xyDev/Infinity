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

use Closure;
use pmmp\thread\ThreadSafe;

final class ThreadSafeClosure extends ThreadSafe{

	private Closure $closure;

	public function __construct(Closure $closure, ?Object $object = null){
		$closure = Closure::bind($closure, $object === null ? $this : $object);
		$this->closure = $closure;
	}

	public function getClosure() : Closure{
		return $this->closure;
	}

	/**
	 * @deprecated Use bindToAndExecute() instead
	 */
	public function execute(mixed ...$args) : mixed{
		return ($this->closure)(...$args);
	}

	public function bindToAndExecute(Object $newThis, mixed ...$args) : mixed{
		$closure = Closure::bind($this->closure, $newThis);
		return $closure(...$args);
	}
}
