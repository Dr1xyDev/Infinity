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

namespace pocketmine\level\generator\vanilla\block;

use pocketmine\level\generator\vanilla\block\utils\FortuneDropHelper;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;

use pocketmine\level\generator\vanilla\item\ItemIds;

class Potato extends Crops {
	protected $id = self::POTATO_BLOCK;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function getName() : string{
		return "Potato Block";
	}

	public function getSeed() : Item {
		return ItemFactory::get(ItemIds::POTATO);
	}

	public function getCrop() : Item {
		return ItemFactory::get(ItemIds::POTATO);
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		$result = [
			//min/max would be 2-5 in Java
			ItemFactory::get(ItemIds::POTATO, 0, $this->isMaxAge() ? FortuneDropHelper::binomial($item, 1) : 1)
		];

		if ($this->isMaxAge() && $this->level->random->nextBoundedInt(50) === 0) {
			$result[] = ItemFactory::get(ItemIds::POISONOUS_POTATO);
		}

		return $result;
	}
}
