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

use function count;
use function mb_strtoupper;
use function spl_object_id;

/**
 * Offers a shim to make a native enum behave similarly to a legacy {@link EnumTrait} enum.
 * Used to provide backwards compatibility for enums that have been migrated to native PHP 8.1 enums.
 *
 * @deprecated
 */
trait LegacyEnumShimTrait
{
	/**
	 * @param mixed[] $arguments
	 */
	public static function __callStatic(string $name, array $arguments) : self
	{
		if (count($arguments) > 0) {
			throw new \ArgumentCountError("Expected exactly 0 arguments, " . count($arguments) . " passed");
		}
		return self::getAll()[mb_strtoupper($name)];
	}

	/**
	 * Returns a list of all cases, indexed by name.
	 *
	 * @return self[]
	 * @phpstan-return array<string, self>
	 */
	public static function getAll() : array
	{
		/** @var array<string, self>|null $result */
		static $result = null;
		if ($result === null) {
			$result = [];
			foreach (self::cases() as $case) {
				$result[mb_strtoupper($case->name)] = $case;
			}
		}
		return $result;
	}

	/**
	 * Shim for {@link \UnitEnum::name}.
	 *
	 * @deprecated Use the native enum's name property instead.
	 */
	public function name() : string
	{
		return $this->name;
	}

	/**
	 * Alias of spl_object_id($this).
	 *
	 * @deprecated
	 */
	public function id() : int
	{
		return spl_object_id($this);
	}

	/**
	 * Returns whether the two objects are equivalent.
	 *
	 * @deprecated Native enums can be safely compared with ===.
	 */
	public function equals(self $other) : bool
	{
		return $this === $other;
	}
}
