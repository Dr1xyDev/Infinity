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
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\Player;

/**
 * Represents an action involving dropping an item into the world.
 */
class DropItemAction extends InventoryAction
{
	public function __construct(Item $targetItem)
	{
		parent::__construct(ItemFactory::get(Item::AIR, 0, 0), $targetItem);
	}

	public function isValid(Player $source) : bool
	{
		return !$this->targetItem->isNull();
	}

	public function onPreExecute(Player $source) : bool
	{
		return $source->dropItem($this->targetItem);
	}

	public function execute(Player $source) : void
	{

	}

	public function revert(Player $source) : void
	{

	}
}
