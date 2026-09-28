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

namespace pocketmine\level\generator\vanilla\network\mcpe\compression;

use function array_push;

class CompressBatchPromise
{
	/**
	 * @var \Closure[]
	 * @phpstan-var (\Closure(self) : void)[]
	 */
	private array $callbacks = [];

	private ?string $result = null;

	private bool $cancelled = false;

	/**
	 * @phpstan-param \Closure(self) : void ...$callbacks
	 */
	public function onResolve(\Closure ...$callbacks) : void
	{
		$this->checkCancelled();
		if ($this->result !== null) {
			foreach ($callbacks as $callback) {
				$callback($this);
			}
		} else {
			array_push($this->callbacks, ...$callbacks);
		}
	}

	public function resolve(string $result) : void
	{
		if (!$this->cancelled) {
			if ($this->result !== null) {
				throw new \LogicException("Cannot resolve promise more than once");
			}
			$this->result = $result;
			foreach ($this->callbacks as $callback) {
				$callback($this);
			}
			$this->callbacks = [];
		}
	}

	/**
	 * @return \Closure[]
	 * @phpstan-return (\Closure(self) : void)[]
	 */
	public function getResolveCallbacks() : array
	{
		return $this->callbacks;
	}

	public function getResult() : string
	{
		$this->checkCancelled();
		if ($this->result === null) {
			throw new \LogicException("Promise has not yet been resolved");
		}
		return $this->result;
	}

	public function hasResult() : bool
	{
		return $this->result !== null;
	}

	public function isCancelled() : bool
	{
		return $this->cancelled;
	}

	public function cancel() : void
	{
		if ($this->hasResult()) {
			throw new \LogicException("Cannot cancel a resolved promise");
		}
		$this->cancelled = true;
	}

	private function checkCancelled() : void
	{
		if ($this->cancelled) {
			throw new \InvalidArgumentException("Promise has been cancelled");
		}
	}
}
