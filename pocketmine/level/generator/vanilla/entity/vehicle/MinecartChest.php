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

namespace pocketmine\level\generator\vanilla\entity\vehicle;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\inventory\InventoryHolder;
use pocketmine\level\generator\vanilla\inventory\MinecartChestInventory;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\loot\LootContext;
use pocketmine\level\generator\vanilla\loot\LootTableManager;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\nbt\NBT;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\nbt\tag\IntTag;
use pocketmine\level\generator\vanilla\nbt\tag\ListTag;
use pocketmine\level\generator\vanilla\nbt\tag\LongTag;
use pocketmine\level\generator\vanilla\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\tile\LootContainer;
use pocketmine\level\generator\vanilla\utils\Random;
use function mt_rand;

class MinecartChest extends Minecart implements InventoryHolder
{
	public const NETWORK_ID = self::CHEST_MINECART;

	public const TAG_ITEMS = "Items";
	public const TAG_SLOT = "Slot";

	protected MinecartChestInventory $inventory;
	/** Vanilla loot table filled in when the chest is used for the first time (mineshafts) */
	protected ?string $lootTable = null;
	protected int $lootTableSeed = 0;

	protected function initEntity() : void
	{
		$this->inventory = new MinecartChestInventory($this);

		if ($this->namedtag->hasTag(LootContainer::TAG_LOOT_TABLE, StringTag::class)) {
			$this->lootTable = $this->namedtag->getString(LootContainer::TAG_LOOT_TABLE);
			$seedTag = $this->namedtag->getTag(LootContainer::TAG_LOOT_TABLE_SEED);
			$this->lootTableSeed = $seedTag instanceof LongTag || $seedTag instanceof IntTag ? $seedTag->getValue() : 0;
		}

		$items = $this->namedtag->getListTag(self::TAG_ITEMS);
		if ($items !== null) {
			/** @var CompoundTag $itemNBT */
			foreach ($items as $itemNBT) {
				$slot = $itemNBT->getByte(self::TAG_SLOT, -1);
				if ($slot >= 0 && $slot < $this->inventory->getSize()) {
					$this->inventory->setItem($slot, Item::nbtDeserialize($itemNBT), false);
				}
			}
		}

		parent::initEntity();
	}

	public function saveNBT() : void
	{
		parent::saveNBT();

		$items = [];
		foreach ($this->inventory->getContents() as $slot => $item) {
			$items[] = $item->nbtSerialize($slot);
		}
		$this->namedtag->setTag(new ListTag(self::TAG_ITEMS, $items, NBT::TAG_Compound));

		if ($this->lootTable !== null) {
			$this->namedtag->setString(LootContainer::TAG_LOOT_TABLE, $this->lootTable);
			$this->namedtag->setLong(LootContainer::TAG_LOOT_TABLE_SEED, $this->lootTableSeed);
		} else {
			$this->namedtag->removeTag(LootContainer::TAG_LOOT_TABLE, LootContainer::TAG_LOOT_TABLE_SEED);
		}
	}

	public function getLootTable() : ?string
	{
		return $this->lootTable;
	}

	public function setLootTable(?string $lootTable, int $seed = 0) : void
	{
		$this->lootTable = $lootTable;
		$this->lootTableSeed = $seed;
	}

	/**
	 * Generates the loot into the chest if it wasn't done yet
	 */
	public function unpackLootTable(?Player $player = null) : void
	{
		if ($this->lootTable === null) {
			return;
		}
		$table = $this->lootTable;
		$random = new Random($this->lootTableSeed !== 0 ? $this->lootTableSeed : mt_rand());
		$this->lootTable = null;
		$this->lootTableSeed = 0;
		LootTableManager::getInstance()->fillInventory($table, $this->inventory, new LootContext($random, 0.0, null, $player), $this->level, $this->asVector3());
	}

	/**
	 * @return MinecartChestInventory
	 */
	public function getInventory()
	{
		return $this->inventory;
	}

	/**
	 * Interacting opens the chest, minecarts with a chest can't be ridden
	 */
	public function onFirstInteract(Player $player, Vector3 $clickPos) : bool
	{
		if ($player->isSpectator() || $this->isKilled) {
			return false;
		}
		$this->unpackLootTable($player);
		$player->addWindow($this->inventory);
		return true;
	}

	public function getDrops() : array
	{
		$this->unpackLootTable();
		return [
			ItemFactory::get(Item::MINECART),
			ItemFactory::get(Block::CHEST),
			...$this->inventory->getContents()
		];
	}

	public function kill() : void
	{
		parent::kill();
		$this->inventory->removeAllViewers(true);
	}

	public function close() : void
	{
		if (!$this->closed) {
			$this->inventory->removeAllViewers(true);
		}
		parent::close();
	}
}
