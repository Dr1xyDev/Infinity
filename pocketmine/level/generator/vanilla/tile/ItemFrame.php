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

use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\nbt\tag\LongTag;
use pocketmine\level\generator\vanilla\nbt\tag\StringTag;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;

class ItemFrame extends Spawnable
{
	public const TAG_ITEM_ROTATION = "ItemRotation";
	public const TAG_ITEM_DROP_CHANCE = "ItemDropChance";
	public const TAG_ITEM = "Item";

	private Item $item;
	private int $itemRotation;
	private float $itemDropChance;

	protected function readSaveData(CompoundTag $nbt) : void
	{
		if (($itemTag = $nbt->getCompoundTag(self::TAG_ITEM)) !== null) {
			$this->item = Item::nbtDeserialize($itemTag);
		} else {
			$this->item = ItemFactory::get(Item::AIR, 0, 0);
		}
		$this->item->setOnItemFrame(true);

		$this->itemRotation = $nbt->getByte(self::TAG_ITEM_ROTATION, 0, true);
		$this->itemDropChance = $nbt->getFloat(self::TAG_ITEM_DROP_CHANCE, 1.0, true);
	}

	protected function writeSaveData(CompoundTag $nbt) : void
	{
		$nbt->setFloat(self::TAG_ITEM_DROP_CHANCE, $this->itemDropChance);
		$nbt->setByte(self::TAG_ITEM_ROTATION, $this->itemRotation);
		$nbt->setTag($this->item->nbtSerialize(-1, self::TAG_ITEM));
	}

	public function hasItem() : bool
	{
		return !$this->item->isNull();
	}

	public function getItem() : Item
	{
		return clone $this->item;
	}

	public function setItem(?Item $item = null) : void
	{
		if ($item !== null && !$item->isNull()) {
			$this->item = clone $item;
		} else {
			$this->item = ItemFactory::get(Item::AIR, 0, 0);
		}
		$this->item->setOnItemFrame(true);

		$this->onChanged();
	}

	public function getItemRotation() : int
	{
		return $this->itemRotation;
	}

	public function setItemRotation(int $rotation) : void
	{
		$this->itemRotation = $rotation;
		$this->onChanged();
	}

	public function getItemDropChance() : float
	{
		return $this->itemDropChance;
	}

	public function setItemDropChance(float $chance) : void
	{
		$this->itemDropChance = $chance;
		$this->onChanged();
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt, int $protocolVersion) : void
	{
		$nbt->setFloat(self::TAG_ITEM_DROP_CHANCE, $this->itemDropChance);
		$nbt->setByte(self::TAG_ITEM_ROTATION, $this->itemRotation);

		$item = $this->item;
		if ($item->getNamedTagEntry("map_uuid") instanceof LongTag && $protocolVersion < ProtocolInfo::PROTOCOL_407) {
			$item = clone $item;
			$mapId = $item->getNamedTagEntry("map_uuid")->getValue();
			$item->removeNamedTagEntry("map_uuid");
			$item->setNamedTagEntry(new StringTag("map_uuid", (string) $mapId));
		}

		$nbt->setTag($item->nbtSerialize(-1, self::TAG_ITEM, $protocolVersion));
	}
}
