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

use pmmp\thread\ThreadSafe;

use function get_debug_type;
use function igbinary_serialize;
use function igbinary_unserialize;

/**
 * This class automatically serializes values which can't be shared between threads.
 * This class does NOT enable sharing the variable between threads. Each call to deserialize() will return a new copy
 * of the variable.
 *
 * @phpstan-template TValue
 */
final class NonThreadSafeValue extends ThreadSafe
{
	private string $variable;

	/**
	 * @phpstan-param TValue $variable
	 */
	public function __construct(
		mixed $variable
	) {
		$this->variable = igbinary_serialize($variable) ?? throw new \InvalidArgumentException("Cannot serialize variable of type " . get_debug_type($variable));
	}

	/**
	 * Returns a deserialized copy of the original variable.
	 *
	 * @phpstan-return TValue
	 */
	public function deserialize() : mixed
	{
		return igbinary_unserialize($this->variable);
	}
}
