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

namespace pocketmine\level\generator\vanilla\event\inventory;

use pocketmine\level\generator\vanilla\entity\object\ItemEntity;
use pocketmine\level\generator\vanilla\event\Cancellable;
use pocketmine\level\generator\vanilla\inventory\Inventory;
use pocketmine\level\generator\vanilla\item\Item;

class HopperItemTakeEvent extends InventoryEvent implements Cancellable {
	public const int EVENT_TAKE_ITEM_FROM_INVENTORY = 0;
	public const int EVENT_TAKE_ITEM = 1;

	public function __construct(private ItemEntity|Item $item, Inventory $inventory, private int $type = self::EVENT_TAKE_ITEM, private ?Inventory $inventoryFrom = null)
	{
		$this->inventory = $inventory;
	}

	public function getInventoryFrom() : ?Inventory
	{
		return $this->inventoryFrom;
	}

	public function getType() : int
	{
		return $this->type;
	}

	public function getItem() : ItemEntity|Item
	{
		return $this->item;
	}
}
