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

use pocketmine\level\generator\vanilla\inventory\Inventory;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\loot\LootContext;
use pocketmine\level\generator\vanilla\loot\LootTableManager;
use pocketmine\level\generator\vanilla\nbt\NBT;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\nbt\tag\IntTag;
use pocketmine\level\generator\vanilla\nbt\tag\ListTag;
use pocketmine\level\generator\vanilla\nbt\tag\LongTag;
use pocketmine\level\generator\vanilla\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\utils\Random;
use function mt_rand;

/**
 * This trait implements most methods in the {@link Container} interface. It should only be used by Tiles.
 */
trait ContainerTrait
{
	/** @var string|null */
	private $lock;
	/** Vanilla loot table which fills the inventory the first time it is used */
	private ?string $lootTable = null;
	private int $lootTableSeed = 0;

	/**
	 * @return Inventory
	 */
	abstract public function getRealInventory();

	public function getLootTable() : ?string
	{
		return $this->lootTable;
	}

	public function setLootTable(?string $lootTable, int $seed = 0) : void
	{
		$this->lootTable = $lootTable;
		$this->lootTableSeed = $seed;
	}

	public function unpackLootTable(?Player $player = null) : void
	{
		if ($this->lootTable === null) {
			return;
		}
		$table = $this->lootTable;
		$random = new Random($this->lootTableSeed !== 0 ? $this->lootTableSeed : mt_rand());
		//generate only once, even if something goes wrong
		$this->lootTable = null;
		$this->lootTableSeed = 0;

		LootTableManager::getInstance()->fillInventory($table, $this->getRealInventory(), new LootContext($random, 0.0, null, $player), $this->getLevel(), $this->add(0.5, 0.5, 0.5));
	}

	protected function loadItems(CompoundTag $tag) : void
	{
		if ($tag->hasTag(LootContainer::TAG_LOOT_TABLE, StringTag::class)) {
			$this->lootTable = $tag->getString(LootContainer::TAG_LOOT_TABLE);
			$seedTag = $tag->getTag(LootContainer::TAG_LOOT_TABLE_SEED);
			$this->lootTableSeed = $seedTag instanceof LongTag || $seedTag instanceof IntTag ? $seedTag->getValue() : 0;
		}

		if ($tag->hasTag(Container::TAG_ITEMS, ListTag::class)) {
			$inventoryTag = $tag->getListTag(Container::TAG_ITEMS);
			$inventory = $this->getRealInventory();

			$newContents = [];
			/** @var CompoundTag $itemNBT */
			foreach ($inventoryTag as $itemNBT) {
				$newContents[$itemNBT->getByte("Slot")] = Item::nbtDeserialize($itemNBT);
			}
			$inventory->setContents($newContents);
		}

		if ($tag->hasTag(Container::TAG_LOCK, StringTag::class)) {
			$this->lock = $tag->getString(Container::TAG_LOCK);
		}
	}

	protected function saveItems(CompoundTag $tag) : void
	{
		$items = [];
		foreach ($this->getRealInventory()->getContents() as $slot => $item) {
			$items[] = $item->nbtSerialize($slot);
		}

		$tag->setTag(new ListTag(Container::TAG_ITEMS, $items, NBT::TAG_Compound));

		if ($this->lock !== null) {
			$tag->setString(Container::TAG_LOCK, $this->lock);
		}

		if ($this->lootTable !== null) {
			$tag->setString(LootContainer::TAG_LOOT_TABLE, $this->lootTable);
			$tag->setLong(LootContainer::TAG_LOOT_TABLE_SEED, $this->lootTableSeed);
		} else {
			$tag->removeTag(LootContainer::TAG_LOOT_TABLE, LootContainer::TAG_LOOT_TABLE_SEED);
		}
	}

	/**
	 * @see Container::canOpenWith()
	 */
	public function canOpenWith(string $key) : bool
	{
		return $this->lock === null || $this->lock === $key;
	}
}
