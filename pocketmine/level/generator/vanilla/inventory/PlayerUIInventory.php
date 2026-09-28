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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\UIInventorySlotOffset;
use pocketmine\Player;

class PlayerUIInventory extends BaseInventory
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
		return "UI";
	}

	public function setItem(int $index, Item $item, bool $send = true) : bool
	{
		if (parent::setItem($index, $item, $send)) {
			if ($index !== UIInventorySlotOffset::CURSOR && $index !== UIInventorySlotOffset::CREATED_ITEM_OUTPUT) {
				$window = $this->holder->findWindow(FakeInventory::class) ?? $this->holder->getCraftingGrid();

				if ($window instanceof FakeInventory) {
					$slot = $window->getUIOffsets($this->holder)[$index] ?? -1;

					if ($window->slotExists($slot)) {
						$window->setItem($slot, $item, $send);
					}
				}
			}

			return true;
		}

		return false;
	}

	public function getDefaultSize() : int
	{
		return 51;
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
		//TODO: HACK!
		//Since 1.13, this is now part of a larger "UI inventory", and sending contents for this larger inventory does
		//not work the way it's intended to. Even if it did, it would be necessary to send all 51 slots just to update
		//this one, which is just not worth it.
		//This workaround isn't great, but it's at least simple.
		$this->sendSlot(UIInventorySlotOffset::CURSOR, $target);
	}
}
