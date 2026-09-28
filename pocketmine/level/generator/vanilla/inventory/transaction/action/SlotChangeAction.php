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

namespace pocketmine\level\generator\vanilla\inventory\transaction\action;

use pocketmine\level\generator\vanilla\event\inventory\InventoryClickEvent;
use pocketmine\level\generator\vanilla\inventory\Inventory;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\Player;

use function spl_object_hash;

/**
 * Represents an action causing a change in an inventory slot.
 */
class SlotChangeAction extends InventoryAction
{
	protected Inventory $inventory;
	protected int $inventorySlot;

	public function __construct(Inventory $inventory, int $inventorySlot, Item $sourceItem, Item $targetItem)
	{
		parent::__construct($sourceItem, $targetItem);
		$this->inventory = $inventory;
		$this->inventorySlot = $inventorySlot;
	}

	/**
	 * Returns the inventory involved in this action.
	 */
	public function getInventory() : Inventory
	{
		return $this->inventory;
	}

	/**
	 * Returns the slot in the inventory which this action modified.
	 */
	public function getSlot() : int
	{
		return $this->inventorySlot;
	}

	/**
	 * Checks if the item in the inventory at the specified slot is the same as this action's source item.
	 */
	public function isValid(Player $source) : bool
	{
		return (
			$this->inventory->slotExists($this->inventorySlot) &&
			$this->inventory->getItem($this->inventorySlot)->equalsExact($this->sourceItem)
		);
	}

	public function onPreExecute(Player $source) : bool
	{
		$ev = new InventoryClickEvent($this->inventory, $source, $this->inventorySlot, $this->sourceItem);
		$ev->call();
		return !$ev->isCancelled();
	}

	/**
	 * Sets the item into the target inventory.
	 */
	public function execute(Player $source) : void
	{
		if ($this->inventory->setItem($this->inventorySlot, $this->targetItem, false)) {
			$viewers = $this->inventory->getViewers();
			unset($viewers[spl_object_hash($source)]);
			$this->inventory->sendSlot($this->inventorySlot, $viewers);
		} else {
			$this->inventory->sendSlot($this->inventorySlot, $source);
		}
	}

	public function revert(Player $source) : void
	{
		$this->inventory->sendContents($source);
	}
}
