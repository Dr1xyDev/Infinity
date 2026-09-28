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

namespace pocketmine\level\generator\vanilla\event\entity;

use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\event\Cancellable;
use pocketmine\level\generator\vanilla\item\Item;

/**
 * Called before a slot in an entity's inventory changes.
 * @phpstan-extends EntityEvent<Entity>
 */
class EntityInventoryChangeEvent extends EntityEvent implements Cancellable
{
	/** @var Item */
	private $oldItem;
	/** @var Item */
	private $newItem;
	/** @var int */
	private $slot;

	public function __construct(Entity $entity, Item $oldItem, Item $newItem, int $slot)
	{
		$this->entity = $entity;
		$this->oldItem = $oldItem;
		$this->newItem = $newItem;
		$this->slot = $slot;
	}

	/**
	 * Returns the inventory slot number affected by the event.
	 */
	public function getSlot() : int
	{
		return $this->slot;
	}

	/**
	 * Returns the item which will be in the slot after the event.
	 */
	public function getNewItem() : Item
	{
		return $this->newItem;
	}

	public function setNewItem(Item $item) : void
	{
		$this->newItem = $item;
	}

	/**
	 * Returns the item currently in the slot.
	 */
	public function getOldItem() : Item
	{
		return $this->oldItem;
	}
}
