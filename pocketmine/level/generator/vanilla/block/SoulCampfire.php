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

use pocketmine\level\generator\vanilla\inventory\FurnaceType;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\item\ItemIds;

class SoulCampfire extends Campfire {
	protected $id = self::SOUL_CAMPFIRE;
	protected $itemId = ItemIds::SOUL_CAMPFIRE;

	public function getName() : string{
		return "Soul Campfire";
	}

	public function getLightLevel() : int{
		return $this->isExtinguished() ? 0 : 10;
	}

	public function getFurnaceType() : FurnaceType {
		return FurnaceType::SOUL_CAMPFIRE;
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [
			ItemFactory::get(ItemIds::SOUL_SOIL)
		];
	}

	protected function getEntityCollisionDamage() : int{
		return 2;
	}
}
