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
use pocketmine\level\generator\vanilla\level\Position;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\tile\Chest;

use function array_merge;
use function array_slice;
use function count;

class DoubleChestInventory extends ChestInventory implements InventoryHolder
{
	private ?ChestInventory $left;
	private ?ChestInventory $right;

	public function __construct(Chest $left, Chest $right)
	{
		$this->left = $left->getRealInventory();
		$this->right = $right->getRealInventory();
		$items = array_merge($this->left->getContents(true), $this->right->getContents(true));
		BaseInventory::__construct($items);
	}

	public function getName() : string
	{
		return "Double Chest";
	}

	public function getDefaultSize() : int
	{
		return $this->left->getDefaultSize() + $this->right->getDefaultSize();
	}

	public function getInventory()
	{
		return $this;
	}

	/**
	 * @return Chest|Position
	 */
	public function getHolder()
	{
		return $this->left->getHolder();
	}

	public function getItem(int $index) : Item
	{
		return $index < $this->left->getSize() ? $this->left->getItem($index) : $this->right->getItem($index - $this->left->getSize());
	}

	public function setItem(int $index, Item $item, bool $send = true) : bool
	{
		$old = $this->getItem($index);
		if ($index < $this->left->getSize() ? $this->left->setItem($index, $item, $send) : $this->right->setItem($index - $this->left->getSize(), $item, $send)) {
			$this->onSlotChange($index, $old, $send);
			return true;
		}
		return false;
	}

	public function getContents(bool $includeEmpty = false) : array
	{
		$result = $this->left->getContents($includeEmpty);
		$leftSize = $this->left->getSize();

		foreach ($this->right->getContents($includeEmpty) as $i => $item) {
			$result[$i + $leftSize] = $item;
		}

		return $result;
	}

	/**
	 * @param Item[] $items
	 */
	public function setContents(array $items, bool $send = true) : void
	{
		$size = $this->getSize();
		if (count($items) > $size) {
			$items = array_slice($items, 0, $size, true);
		}

		$leftSize = $this->left->getSize();

		for ($i = 0; $i < $size; ++$i) {
			if (!isset($items[$i])) {
				if (($i < $leftSize && isset($this->left->slots[$i])) || isset($this->right->slots[$i - $leftSize])) {
					$this->clear($i, false);
				}
			} elseif (!$this->setItem($i, $items[$i], false)) {
				$this->clear($i, false);
			}
		}

		if ($send) {
			$this->sendContents($this->getViewers());
		}
	}

	public function onOpen(Player $who) : void
	{
		parent::onOpen($who);

		if (count($this->getViewers()) === 1 && $this->right->getHolder()->isValid()) {
			$this->right->broadcastBlockEventPacket(true);
		}
	}

	public function onClose(Player $who) : void
	{
		if (count($this->getViewers()) === 1 && $this->right->getHolder()->isValid()) {
			$this->right->broadcastBlockEventPacket(false);
		}
		parent::onClose($who);
	}

	public function getLeftSide() : ChestInventory
	{
		return $this->left;
	}

	public function getRightSide() : ChestInventory
	{
		return $this->right;
	}

	/**
	 * @return void
	 */
	public function invalidate()
	{
		$this->left = null;
		$this->right = null;
	}
}
