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

namespace pocketmine\level\generator\vanilla\inventory\random;

use pocketmine\level\generator\vanilla\utils\Random;

class WeightedRandom {

	/**
	 * @param WeightedRandomItem[] $items
	 */
	public static function getTotalWeight(array $items) : int {
		$weights = 0;
		foreach ($items as $item) {
			$weights += $item->itemWeight;
		}

		return $weights;
	}

	/**
	 * @param WeightedRandomItem[] $items
	 */
	public static function getRandomItem(array $items, ?int $weight, ?Random $random = null) : ?WeightedRandomItem {
		if ($weight === null) {
			$weight = WeightedRandom::getTotalWeight($items);
		}

		if ($random !== null) {
			if ($weight <= 0) {
				throw new \InvalidArgumentException("Weight must be greater than 0");
			}

			$weight = $random->nextBoundedInt($weight);
		}

		foreach ($items as $item) {
			$weight -= $item->itemWeight;
			if ($weight < 0) {
				return $item;
			}
		}

		return null;
	}
}
