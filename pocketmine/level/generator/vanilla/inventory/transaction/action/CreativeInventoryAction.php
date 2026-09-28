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

class CreativeInventoryAction extends InventoryAction
{
	/** Player put an item into the creative window to destroy it. */
	public const TYPE_DELETE_ITEM = 0;
	/** Player took an item from the creative window. */
	public const TYPE_CREATE_ITEM = 1;

	/** @var int */
	protected $actionType;

	public function __construct(Item $sourceItem, Item $targetItem, int $actionType)
	{
		parent::__construct($sourceItem, $targetItem);
		$this->actionType = $actionType;
	}

	/**
	 * Checks that the player is in creative, and (if creating an item) that the item exists in the creative inventory.
	 */
	public function isValid(Player $source) : bool
	{
		return $source->isCreative(true) &&
			($this->actionType === self::TYPE_DELETE_ITEM || Item::getCreativeItemIndex($this->sourceItem, $source->getProtocolVersion()) !== -1);
	}

	/**
	 * Returns the type of the action.
	 */
	public function getActionType() : int
	{
		return $this->actionType;
	}

	/**
	 * No need to do anything extra here: this type just provides a place for items to disappear or appear from.
	 */
	public function execute(Player $source) : void
	{

	}

	public function revert(Player $source) : void
	{

	}
}
