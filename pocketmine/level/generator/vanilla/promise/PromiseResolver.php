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

namespace pocketmine\level\generator\vanilla\promise;

/**
 * @phpstan-template TValue
 */
final class PromiseResolver
{
	/** @phpstan-var PromiseSharedData<TValue> */
	private PromiseSharedData $shared;
	/** @phpstan-var Promise<TValue> */
	private Promise $promise;

	public function __construct()
	{
		$this->shared = new PromiseSharedData();
		$this->promise = new Promise($this->shared);
	}

	/**
	 * @phpstan-param TValue $value
	 */
	public function resolve(mixed $value) : void
	{
		if ($this->shared->state !== null) {
			throw new \LogicException("Promise has already been resolved/rejected");
		}
		$this->shared->state = true;
		$this->shared->result = $value;
		foreach ($this->shared->onSuccess as $c) {
			$c($value);
		}
		$this->shared->onSuccess = [];
		$this->shared->onFailure = [];
	}

	public function reject() : void
	{
		if ($this->shared->state !== null) {
			throw new \LogicException("Promise has already been resolved/rejected");
		}
		$this->shared->state = false;
		foreach ($this->shared->onFailure as $c) {
			$c();
		}
		$this->shared->onSuccess = [];
		$this->shared->onFailure = [];
	}

	/**
	 * @phpstan-return Promise<TValue>
	 */
	public function getPromise() : Promise
	{
		return $this->promise;
	}
}
