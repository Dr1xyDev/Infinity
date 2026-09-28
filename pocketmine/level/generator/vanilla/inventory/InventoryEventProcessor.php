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

/**
 * This interface can be used to listen for events on a specific Inventory.
 *
 * If you want to listen to changes on an inventory, create a class implementing this interface and implement its
 * methods, then register it onto the inventory or inventories that you want to receive events for.
 */
interface InventoryEventProcessor
{
	/**
	 * Called prior to a slot in the given inventory changing. This is called by inventories that this listener is
	 * attached to.
	 *
	 * @return Item|null that should be used in place of $newItem, or null if the slot change should not proceed.
	 */
	public function onSlotChange(Inventory $inventory, int $slot, Item $oldItem, Item $newItem) : ?Item;
}
