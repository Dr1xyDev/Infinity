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

use pocketmine\level\generator\vanilla\inventory\Inventory;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\utils\Random;

class WeightedRandomChestContent extends WeightedRandomItem {

	/**
	 * @param WeightedRandomChestContent[] $list
	 */
	public static function generateChestContents(Random $random, array $list, Inventory $inventory, int $max) : void{
		for ($i = 0; $i < $max; ++$i) {
			/** @var WeightedRandomChestContent $weightedRandomChestContent */
			$weightedRandomChestContent = WeightedRandom::getRandomItem($list, null, $random);
			$count = $weightedRandomChestContent->minStackSize + $random->nextBoundedInt($weightedRandomChestContent->maxStackSize - $weightedRandomChestContent->minStackSize + 1);

			if ($weightedRandomChestContent->item->getMaxStackSize() >= $count) {
				$item = clone $weightedRandomChestContent->item;
				$item->setCount($count);
				$inventory->setItem($random->nextBoundedInt($inventory->getSize()), $item);
			} else {
				for ($k = 0; $k < $count; ++$k) {
					$item = clone $weightedRandomChestContent->item;
					$item->setCount(1);
					$inventory->setItem($random->nextBoundedInt($inventory->getSize()), $item);
				}
			}
		}
	}

	public function __construct(
		public Item $item,
		public int $minStackSize,
		public int $maxStackSize,
		int $itemWeight
	){
		parent::__construct($itemWeight);
	}
}
