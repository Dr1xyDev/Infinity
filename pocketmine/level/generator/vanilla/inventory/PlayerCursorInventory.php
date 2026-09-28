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

use BadMethodCallException;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\UIInventorySlotOffset;
use pocketmine\Player;

/**
 * This is not really a inventory, just a thing to prevent api breaks
 */
class PlayerCursorInventory extends BaseInventory
{
	/** @var Player */
	protected $holder;

	public function __construct(Player $holder)
	{
		$this->holder = $holder;
		parent::__construct();
	}

	public function getName() : string
	{
		return "Cursor";
	}

	public function setItem(int $index, Item $item, bool $send = true) : bool
	{
		return $this->holder->getUIInventory()->setItem(UIInventorySlotOffset::CURSOR, $item, $send);
	}

	public function getItem(int $index) : Item
	{
		return $this->holder->getUIInventory()->getItem(UIInventorySlotOffset::CURSOR);
	}

	public function getContents(bool $includeEmpty = false) : array
	{
		return [$this->getItem(0)];
	}

	public function getDefaultSize() : int
	{
		return 1;
	}

	public function setSize(int $size)
	{
		throw new BadMethodCallException("Cursor can only carry one item at a time");
	}

	/**
	 * This override is here for documentation and code completion purposes only.
	 * @return Player
	 */
	public function getHolder()
	{
		return $this->holder;
	}

	public function sendContents($target) : void
	{
		$this->holder->getUIInventory()->sendContents($target);
	}
}
