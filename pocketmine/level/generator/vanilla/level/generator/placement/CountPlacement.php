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

namespace pocketmine\level\generator\vanilla\level\generator\placement;

use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\Random;
use pocketmine\level\generator\vanilla\utils\valueproviders\ConstantInt;
use pocketmine\level\generator\vanilla\utils\valueproviders\IntProvider;
use pocketmine\level\generator\vanilla\utils\valueproviders\WeightedListInt;
use function abs;

class CountPlacement extends RepeatingPlacement {

	public static function countExtra(int $count, float $chance, int $extra) : CountPlacement {
		$weight = 1.0 / $chance;
		if (abs($weight - (int) $weight) > 1.0E-5) {
			throw new \InvalidArgumentException("Chance data cannot be represented as list weight");
		}

		$array = [];
		for ($i = 0; $i <= (int) ($weight - 1); $i++) {
			$array[] = ConstantInt::of($count);
		}

		$array[] = ConstantInt::of($count + $extra);
		return new CountPlacement(new WeightedListInt($array));
	}

	public static function of(IntProvider $count) : CountPlacement {
		return new self($count);
	}

	public function __construct(
		private IntProvider $count
	){}

	protected function count(Random $random, Vector3 $origin) : int{
		return $this->count->sample($random);
	}
}
