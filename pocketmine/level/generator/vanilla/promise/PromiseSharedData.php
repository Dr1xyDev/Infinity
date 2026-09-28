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
 * @internal
 * @see PromiseResolver
 * @phpstan-template TValue
 */
final class PromiseSharedData
{
	/**
	 * @var \Closure[]
	 * @phpstan-var array<int, \Closure(TValue) : void>
	 */
	public array $onSuccess = [];

	/**
	 * @var \Closure[]
	 * @phpstan-var array<int, \Closure() : void>
	 */
	public array $onFailure = [];

	public ?bool $state = null;

	/** @phpstan-var TValue */
	public mixed $result;
}
