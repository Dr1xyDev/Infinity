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

use pocketmine\level\generator\vanilla\event\Cancellable;
use pocketmine\level\generator\vanilla\inventory\Inventory;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\Player;

class InventoryClickEvent extends InventoryEvent implements Cancellable
{
	private Player $who;
	private int $slot;
	private Item $item;

	public function __construct(Inventory $inventory, Player $who, int $slot, Item $item)
	{
		$this->who = $who;
		$this->slot = $slot;
		$this->item = $item;
		parent::__construct($inventory);
	}

	public function getWhoClicked() : Player
	{
		return $this->who;
	}

	public function getPlayer() : Player
	{
		return $this->who;
	}

	public function getSlot() : int
	{
		return $this->slot;
	}

	public function getItem() : Item
	{
		return $this->item;
	}
}
