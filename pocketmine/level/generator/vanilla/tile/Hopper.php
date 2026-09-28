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

namespace pocketmine\level\generator\vanilla\tile;

use pocketmine\level\generator\vanilla\entity\object\ItemEntity;
use pocketmine\level\generator\vanilla\event\inventory\HopperItemTakeEvent;
use pocketmine\level\generator\vanilla\inventory\FurnaceInventory;
use pocketmine\level\generator\vanilla\inventory\HopperInventory;
use pocketmine\level\generator\vanilla\inventory\InventoryHolder;
use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\math\AxisAlignedBB;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;

use function count;

class Hopper extends Spawnable implements InventoryHolder, LootContainer, Nameable
{
	use NameableTrait {
		addAdditionalSpawnData as addNameSpawnData;
	}
	use ContainerTrait;

	private const TAG_TRANSFER_COOLDOWN = "TransferCooldown";

	protected HopperInventory $inventory;
	protected int $transferCooldown = 8;
	protected AxisAlignedBB $pullBox;

	protected function readSaveData(CompoundTag $nbt) : void{
		$this->inventory = new HopperInventory($this);
		$this->pullBox = new AxisAlignedBB($this->x, $this->y, $this->z, $this->x + 1, $this->y + 1.5, $this->z + 1);

		$this->transferCooldown = $nbt->getInt(self::TAG_TRANSFER_COOLDOWN, 8);

		$this->loadName($nbt);
		$this->loadItems($nbt);

		if ($this->level instanceof Level) {
			$this->level->scheduleDelayedBlockUpdate($this, 1);
		}
	}

	protected function writeSaveData(CompoundTag $nbt) : void
	{
		$nbt->setInt(self::TAG_TRANSFER_COOLDOWN, $this->transferCooldown);

		$this->saveItems($nbt);
		$this->saveName($nbt);
	}

	public function close() : void
	{
		if (!$this->closed) {
			$this->inventory->removeAllViewers(true);
			parent::close();
		}
	}

	public function getInventory() : HopperInventory
	{
		return $this->inventory;
	}

	public function getRealInventory() : HopperInventory
	{
		return $this->inventory;
	}

	public function getDefaultName() : string
	{
		return "Hopper";
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt, int $protocolVersion) : void
	{
		$this->addNameSpawnData($nbt, $protocolVersion);
	}

	public function onUpdate() : bool{
		if ($this->closed) {
			return false;
		}

		$this->timings->startTiming();

		if ($this->isOnTransferCooldown()) {
			$this->transferCooldown--;
		} else {
			$transfer = false;
			$empty = $this->isEmpty();
			if (!$empty) {
				$transfer = $this->pushItems();
			}

			$full = false;
			if (!$transfer) {
				$full = $this->isFull();
				if (!$full) {
					$transfer = $this->pullItems();
				}
			}

			$this->setTransferCooldown(8);
		}

		$this->timings->stopTiming();
		return true;
	}

	public function isEmpty() : bool
	{
		$slots = $this->inventory->getSlots();
		$size = $this->inventory->getSize();
		for ($i = 0; $i < $size; ++$i) {
			$slot = $slots[$i] ?? null;
			if ($slot !== null && !$slot->isNull()) {
				return false;
			}
		}
		return true;
	}

	public function isFull() : bool
	{
		$inv = $this->inventory;
		$size = $inv->getSize();
		if ($size < $inv->getDefaultSize()) {
			return false;
		}
		$slots = $inv->getSlots();
		for ($i = 0; $i < $size; ++$i) {
			$item = $slots[$i] ?? null;
			if ($item === null || $item->isNull() || $item->getCount() !== $item->getMaxStackSize()) {
				return false;
			}
		}
		return true;
	}

	public function pushItems() : bool
	{
		$tile = $this->level->getTile($this->getSide($direction = $this->getBlock()->getDamage()));

		if ($tile instanceof Furnace) {
			$inv = $tile->getInventory();

			for ($i = 0, $size = $this->inventory->getSize(); $i < $size; $i++) {
				$item = $this->inventory->getItem($i);
				if ($item->isNull()) {
					continue;
				}

				$itemToAdd = (clone $item)->setCount(1);
				if ($direction === Facing::DOWN) {
					$smelting = $inv->getSmelting();

					if ($smelting->isNull()) {
						$inv->setSmelting($itemToAdd);
						$item->pop();
						$this->inventory->setItem($i, $item);
						return true;
					} elseif ($smelting->equals($itemToAdd)) {
						$inv->setSmelting($smelting->setCount($smelting->getCount() + 1));
						$item->pop();
						$this->inventory->setItem($i, $item);
						return true;
					}
				} elseif ($item->getFuelTime() > 0) {
					$fuel = $inv->getFuel();

					if ($fuel->isNull()) {
						$inv->setFuel($itemToAdd);
						$item->pop();
						$this->inventory->setItem($i, $item);
						return true;
					} elseif ($fuel->equals($itemToAdd)) {
						$inv->setFuel($fuel->setCount($fuel->getCount() + 1));
						$item->pop();
						$this->inventory->setItem($i, $item);
						return true;
					}
				}
			}
		} elseif ($tile instanceof Chest || $tile instanceof Hopper) {
			$tile->unpackLootTable();
			$inv = $tile->getInventory();

			for ($i = 0, $size = $this->inventory->getSize(); $i < $size; $i++) {
				$item = $this->inventory->getItem($i);
				if ($item->isNull()) {
					continue;
				}

				$itemToAdd = (clone $item)->setCount(1);
				if (count($inv->addItem($itemToAdd)) === 0) {
					$item->pop();
					$this->inventory->setItem($i, $item);
					return true;
				}
			}
		}

		return false;
	}

	public function pullItems() : bool
	{
		$tile = $this->level->getTile($this->up());

		if ($tile instanceof Container) {
			if ($tile instanceof Hopper) {
				return false;
			}
			if ($tile instanceof LootContainer) {
				$tile->unpackLootTable();
			}
			$inv = $tile->getInventory();

			for ($i = 0, $size = $inv->getSize(); $i < $size; $i++) {
				if ($inv instanceof FurnaceInventory && $i !== 2) { //So only results of Furnaces go trough
					continue;
				}
				$item = $inv->getItem($i);
				if ($item->isNull()) {
					continue;
				}

				$itemToAdd = (clone $item)->setCount(1);
				$ev = new HopperItemTakeEvent($itemToAdd, $this->inventory, HopperItemTakeEvent::EVENT_TAKE_ITEM_FROM_INVENTORY, $inv);
				$ev->call();
				if ($ev->isCancelled()) {
					continue;
				}

				if (count($this->inventory->addItem($itemToAdd)) === 0) {
					$item->pop();
					$inv->setItem($i, $item);

					return true;
				}
			}
		} else {
			foreach ($this->level->getNearbyEntities($this->pullBox) as $entity) {
				if (!$entity instanceof ItemEntity || $entity->isFlaggedForDespawn()) {
					continue;
				}
				$item = $entity->getItem();
				$ev = new HopperItemTakeEvent($entity, $this->inventory, HopperItemTakeEvent::EVENT_TAKE_ITEM);
				$ev->call();
				if (!$ev->isCancelled()) {
					if ($this->inventory->canAddItem($item)) {
						$this->inventory->addItem($item);
						$entity->flagForDespawn();

						return true;
					}
				}
			}
		}

		return false;
	}

	public function isOnTransferCooldown() : bool
	{
		return $this->transferCooldown > 0;
	}

	public function setTransferCooldown(int $cooldown) : void
	{
		$this->transferCooldown = $cooldown;
	}
}
