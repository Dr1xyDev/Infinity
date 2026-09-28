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

use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\Player;

/**
 * Represents an action involving a change that applies in some way to an inventory or other item-source.
 */
abstract class InventoryAction
{
	/** @var Item */
	protected $sourceItem;
	/** @var Item */
	protected $targetItem;

	public function __construct(Item $sourceItem, Item $targetItem)
	{
		$this->sourceItem = $sourceItem;
		$this->targetItem = $targetItem;
	}

	/**
	 * Returns the item that was present before the action took place.
	 */
	public function getSourceItem() : Item
	{
		return clone $this->sourceItem;
	}

	/**
	 * Returns the item that the action attempted to replace the source item with.
	 */
	public function getTargetItem() : Item
	{
		return clone $this->targetItem;
	}

	/**
	 * Returns whether this action is currently valid. This should perform any necessary sanity checks.
	 */
	abstract public function isValid(Player $source) : bool;

	/**
	 * Called by inventory transactions before any actions are processed. If this returns false, the transaction will
	 * be cancelled.
	 */
	public function onPreExecute(Player $source) : bool
	{
		return true;
	}

	/**
	 * Performs actions needed to complete the inventory-action server-side. Returns if it was successful. Will return
	 * false if plugins cancelled events. This will only be called if the transaction which it is part of is considered
	 * valid.
	 */
	abstract public function execute(Player $source) : void;

	/**
	 * Performs additional actions when this inventory-action did not complete successfully.
	 */
	abstract public function revert(Player $source) : void;

}
