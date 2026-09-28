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

use function count;
use function spl_object_id;

/**
 * @phpstan-template TValue
 */
final class Promise
{
	/**
	 * @internal Do NOT call this directly; create a new Resolver and call Resolver->promise()
	 * @see PromiseResolver
	 * @phpstan-param PromiseSharedData<TValue> $shared
	 */
	public function __construct(private PromiseSharedData $shared)
	{
	}

	/**
	 * @phpstan-param \Closure(TValue) : void $onSuccess
	 * @phpstan-param \Closure() : void $onFailure
	 */
	public function onCompletion(\Closure $onSuccess, \Closure $onFailure) : void
	{
		$state = $this->shared->state;
		if ($state === true) {
			$onSuccess($this->shared->result);
		} elseif ($state === false) {
			$onFailure();
		} else {
			$this->shared->onSuccess[spl_object_id($onSuccess)] = $onSuccess;
			$this->shared->onFailure[spl_object_id($onFailure)] = $onFailure;
		}
	}

	public function isResolved() : bool
	{
		//TODO: perhaps this should return true when rejected? currently there's no way to tell if a promise was
		//rejected or just hasn't been resolved yet
		return $this->shared->state === true;
	}

	/**
	 * Returns a promise that will resolve only once all the Promises in
	 * `$promises` have resolved. The resolution value of the returned promise
	 * will be an array containing the resolution values of each Promises in
	 * `$promises` indexed by the respective Promises' array keys.
	 *
	 * @param Promise[] $promises
	 *
	 * @phpstan-template TPromiseValue
	 * @phpstan-template TKey of array-key
	 * @phpstan-param array<TKey, Promise<TPromiseValue>> $promises
	 *
	 * @phpstan-return Promise<array<TKey, TPromiseValue>>
	 */
	public static function all(array $promises) : Promise
	{
		/** @phpstan-var PromiseResolver<array<TKey, TPromiseValue>> $resolver */
		$resolver = new PromiseResolver();
		if (count($promises) === 0) {
			$resolver->resolve([]);
			return $resolver->getPromise();
		}
		$values = [];
		$toResolve = count($promises);
		$continue = true;

		foreach ($promises as $key => $promise) {
			$promise->onCompletion(
				function (mixed $value) use ($resolver, $key, $toResolve, &$values) : void {
					$values[$key] = $value;

					if (count($values) === $toResolve) {
						$resolver->resolve($values);
					}
				},
				function () use ($resolver, &$continue) : void {
					if ($continue) {
						$continue = false;
						$resolver->reject();
					}
				}
			);

			if (!$continue) {
				break;
			}
		}

		return $resolver->getPromise();
	}
}
