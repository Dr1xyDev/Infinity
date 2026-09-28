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

use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\event\entity\EntityInventoryChangeEvent;
use pocketmine\level\generator\vanilla\item\Item;

class EntityInventoryEventProcessor implements InventoryEventProcessor
{
	/** @var Entity */
	private $entity;

	public function __construct(Entity $entity)
	{
		$this->entity = $entity;
	}

	public function onSlotChange(Inventory $inventory, int $slot, Item $oldItem, Item $newItem) : ?Item
	{
		$ev = new EntityInventoryChangeEvent($this->entity, $oldItem, $newItem, $slot);
		$ev->call();
		if ($ev->isCancelled()) {
			return null;
		}

		return $ev->getNewItem();
	}
}
