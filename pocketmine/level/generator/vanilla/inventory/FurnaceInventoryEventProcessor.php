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

namespace pocketmine\level\generator\vanilla\inventory;

use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\tile\Furnace;

class FurnaceInventoryEventProcessor implements InventoryEventProcessor
{
	private Furnace $furnace;
	private Level $level;

	public function __construct(Furnace $furnace, Level $level){
		$this->furnace = $furnace;
		$this->level = $level;
	}

	public function onSlotChange(Inventory $inventory, int $slot, Item $oldItem, Item $newItem) : ?Item{
		$this->level->scheduleDelayedBlockUpdate($this->furnace, 1);
		return $newItem;
	}
}
