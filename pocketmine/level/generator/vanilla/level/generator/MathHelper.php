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

namespace pocketmine\level\generator\vanilla\level\generator;

use pocketmine\level\generator\vanilla\utils\Random;
use function min;

final class MathHelper {
	private function __construct(){
		//NOOP
	}

	public static function smoothstep(float $x) : float{
		return $x * $x * $x * ($x * ($x * 6.0 - 15.0) + 10.0);
	}

	public static function lerp3(
		float $alpha1,
		float $alpha2,
		float $alpha3,
		float $x000,
		float $x100,
		float $x010,
		float $x110,
		float $x001,
		float $x101,
		float $x011,
		float $x111
	) : float{
		return self::lerp($alpha3, self::lerp2($alpha1, $alpha2, $x000, $x100, $x010, $x110), self::lerp2($alpha1, $alpha2, $x001, $x101, $x011, $x111));
	}

	public static function lerp2(
		float $alpha1,
		float $alpha2,
		float $x00,
		float $x10,
		float $x01,
		float $x11
	) : float{
		return self::lerp($alpha2, self::lerp($alpha1, $x00, $x10), self::lerp($alpha1, $x01, $x11));
	}

	public static function lerp(
		float $alpha1,
		float $p0,
		float $p1
	) : float{
		return $p0 + $alpha1 * ($p1 - $p0);
	}

	public static function clampedLerp(float $lowerBnd, float $upperBnd, float $slide) : float {
		if ($slide < 0.0) {
			return $lowerBnd;
		} else {
			return $slide > 1.0 ? $upperBnd : MathHelper::lerp($slide, $lowerBnd, $upperBnd);
		}
	}

	public static function clamp(float $num, float $min, float $max) : float{
		if ($num < $min) {
			return $min;
		} else {
			return min($num, $max);
		}
	}

	public static function floorMod(int $x, int $y) : int {
		$mod = $x % $y;
		// if the signs are different and modulo not zero, adjust result
		if (($mod ^ $y) < 0 && $mod != 0) {
			$mod += $y;
		}
		return $mod;
	}

	public static function nextInt(Random $random, int $minInclusive, int $maxInclusive) : int {
		return $minInclusive >= $maxInclusive ? $minInclusive : $random->nextBoundedInt($maxInclusive - $minInclusive + 1) + $minInclusive;
	}

	public static function randomBetweenInclusive(Random $random, int $min, int $maxInclusive) : int {
		return $random->nextBoundedInt($maxInclusive - $min + 1) + $min;
	}
}
