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

namespace pocketmine\level\generator\vanilla\entity\utils;

use InvalidArgumentException;
use pocketmine\level\generator\vanilla\math\Math;
use pocketmine\level\generator\vanilla\utils\AssumptionFailedError;

use function count;
use function max;

abstract class ExperienceUtils
{
	/**
	 * Calculates and returns the amount of XP needed to get from level 0 to level $level
	 */
	public static function getXpToReachLevel(int $level) : int
	{
		if ($level <= 16) {
			return $level ** 2 + $level * 6;
		} elseif ($level <= 31) {
			return (int) ($level ** 2 * 2.5 - 40.5 * $level + 360);
		}

		return (int) ($level ** 2 * 4.5 - 162.5 * $level + 2220);
	}

	/**
	 * Returns the amount of XP needed to reach $level + 1.
	 */
	public static function getXpToCompleteLevel(int $level) : int
	{
		if ($level <= 15) {
			return 2 * $level + 7;
		} elseif ($level <= 30) {
			return 5 * $level - 38;
		} else {
			return 9 * $level - 158;
		}
	}

	/**
	 * Calculates and returns the number of XP levels the specified amount of XP points are worth.
	 * This returns a floating-point number, the decimal part being the progress through the resulting level.
	 */
	public static function getLevelFromXp(int $xp) : float
	{
		if ($xp < 0) {
			throw new InvalidArgumentException("XP must be at least 0");
		}
		if ($xp <= self::getXpToReachLevel(16)) {
			$a = 1;
			$b = 6;
			$c = 0;
		} elseif ($xp <= self::getXpToReachLevel(31)) {
			$a = 2.5;
			$b = -40.5;
			$c = 360;
		} else {
			$a = 4.5;
			$b = -162.5;
			$c = 2220;
		}

		$x = Math::solveQuadratic($a, $b, $c - $xp);
		if (count($x) === 0) {
			throw new AssumptionFailedError("Expected at least 1 solution");
		}

		return max($x); //we're only interested in the positive solution
	}
}
