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

class WeightedRandomItem
{
	/** @var mixed */
	public $item = null;

	/** @var int */
	public $itemWeight = 0;

	public function __construct(int $itemWeight, $item = null)
	{
		$this->itemWeight = $itemWeight;
		$this->item = $item;
	}

	/**
	 * @param WeightedRandomItem[] $weightedItems
	 */
	public static function getTotalWeight(array $weightedItems) : int
	{
		$total = 0;
		foreach ($weightedItems as $weightedItem) {
			$total += $weightedItem->itemWeight;
		}

		return $total;
	}

	/**
	 * @param WeightedRandomItem[] $items
	 */
	public static function getRandomItem(Random $random, array $items, int $totalWeight) : ?WeightedRandomItem
	{
		return self::getRandomItemFromCollection($items, $random->nextBoundedInt($totalWeight));
	}

	/**
	 * @param WeightedRandomItem[] $collection
	 */
	public static function getRandomItemFromCollection(array $collection, int $weight) : ?WeightedRandomItem
	{
		foreach ($collection as $item) {
			$weight -= $item->itemWeight;

			if ($weight < 0) {
				return $item;
			}
		}

		return null;
	}
}
