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

namespace pocketmine\level\generator\vanilla\loot;

use function array_is_list;
use function count;
use function floor;
use function is_array;
use function is_numeric;

/**
 * Numbers in loot tables are either constants or ranges: 3, {"min": 1, "max": 4} or [1, 4]
 */
final class LootRange
{
	private function __construct()
	{
		//NOOP
	}

	/**
	 * @return float[]|null [min, max]
	 */
	public static function parse(mixed $value) : ?array
	{
		if (is_numeric($value)) {
			return [(float) $value, (float) $value];
		}
		if (is_array($value)) {
			if (array_is_list($value) && count($value) === 2 && is_numeric($value[0]) && is_numeric($value[1])) {
				return [(float) $value[0], (float) $value[1]];
			}
			if (isset($value["min"]) || isset($value["max"])) {
				$min = is_numeric($value["min"] ?? null) ? (float) $value["min"] : 0.0;
				$max = is_numeric($value["max"] ?? null) ? (float) $value["max"] : $min;
				return [$min, $max];
			}
		}
		return null;
	}

	public static function toInt(mixed $value, LootContext $context, int $default) : int
	{
		$range = self::parse($value);
		if ($range === null) {
			return $default;
		}
		return $context->randomInt((int) floor($range[0]), (int) floor($range[1]));
	}

	public static function toFloat(mixed $value, LootContext $context, float $default) : float
	{
		$range = self::parse($value);
		if ($range === null) {
			return $default;
		}
		return $context->randomFloat($range[0], $range[1]);
	}
}
