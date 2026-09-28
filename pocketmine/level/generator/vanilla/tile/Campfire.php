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

use pocketmine\level\generator\vanilla\block\Campfire as BlockCampfire;
use pocketmine\level\generator\vanilla\inventory\CampfireInventory;
use pocketmine\level\generator\vanilla\inventory\InventoryHolder;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;

class Campfire extends Spawnable implements InventoryHolder, Container
{
	use ContainerTrait;

	protected CampfireInventory $inventory;

	/** @var int[] */
	public array $cookTimes = [0, 0, 0, 0];

	protected function readSaveData(CompoundTag $nbt) : void
	{
		$this->inventory = new CampfireInventory($this);
		$this->loadItems($nbt);

		for ($i = 0; $i < 4; ++$i) {
			$this->cookTimes[$i] = $nbt->getInt("ItemTime" . ($i + 1), 0);
		}

		$block = $this->getBlock();
		if ($block instanceof BlockCampfire && !$block->isExtinguished()) {
			$this->level->scheduleDelayedBlockUpdate($this, 1);
		}
	}

	protected function writeSaveData(CompoundTag $nbt) : void
	{
		$this->saveItems($nbt);

		for ($i = 0; $i < 4; ++$i) {
			$nbt->setInt("ItemTime" . ($i + 1), $this->cookTimes[$i]);
		}
	}

	public function getInventory() : CampfireInventory
	{
		return $this->inventory;
	}

	public function getRealInventory() : CampfireInventory
	{
		return $this->inventory;
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt, int $protocolVersion) : void
	{
		for ($i = 0; $i < 4; ++$i) {
			$item = $this->inventory->getItem($i);
			if (!$item->isNull()) {
				$nbt->setTag($item->nbtSerialize(-1, "Item" . ($i + 1), $protocolVersion));
			}
			$nbt->setInt("ItemTime" . ($i + 1), $this->cookTimes[$i]);
		}
	}

	public function onUpdate() : bool
	{
		if ($this->closed) {
			return false;
		}

		$block = $this->getBlock();
		if (!($block instanceof BlockCampfire) || $block->isExtinguished()) {
			return false;
		}

		$this->timings->startTiming();

		$hasItems = false;
		$changed = false;
		$craftingManager = $this->level->getServer()->getCraftingManager();

		$furnaceType = $block->getFurnaceType();
		for ($i = 0; $i < 4; ++$i) {
			$item = $this->inventory->getItem($i);
			if ($item->isNull()) {
				if ($this->cookTimes[$i] > 0) {
					$this->cookTimes[$i] = 0;
					$changed = true;
				}
				continue;
			}

			$hasItems = true;

			$recipe = $craftingManager->matchFurnaceRecipe($item, $furnaceType);
			if ($recipe !== null) {
				$this->cookTimes[$i] += 10;
				$cookDuration = $furnaceType->getCookDurationTicks();

				if ($this->cookTimes[$i] >= $cookDuration) {
					$result = clone $recipe->getResult();

					$this->level->dropItem($this->add(0.5, 0.5, 0.5), $result);

					$this->inventory->clear($i);
					$this->cookTimes[$i] = 0;
					$changed = true;
				}
			} else {
				if ($this->cookTimes[$i] > 0) {
					$this->cookTimes[$i] = 0;
					$changed = true;
				}
			}
		}

		if ($changed) {
			$this->onChanged();
		}

		$this->timings->stopTiming();

		return $hasItems;
	}

	public function close() : void
	{
		if (!$this->closed) {
			$this->inventory->removeAllViewers(true);
			parent::close();
		}
	}

	public function addItem(int $slot, Item $item) : void
	{
		$this->inventory->setItem($slot, $item);
		$this->cookTimes[$slot] = 0;
		$this->onChanged();
	}

	public function removeItem(int $slot) : Item
	{
		$item = $this->inventory->getItem($slot);
		$this->inventory->clear($slot);
		$this->cookTimes[$slot] = 0;
		$this->onChanged();
		return $item;
	}
}
