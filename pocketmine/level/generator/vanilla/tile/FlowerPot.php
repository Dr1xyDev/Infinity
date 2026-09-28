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
use pocketmine\level\generator\vanilla\network\mcpe\convert\block\BlockProtocolConvertor;
use pocketmine\level\generator\vanilla\network\mcpe\convert\block\RuntimeBlockMapping;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;

class FlowerPot extends Spawnable
{
	public const TAG_ITEM = "item";
	public const TAG_ITEM_DATA = "mData";
	private const TAG_PLANT_BLOCK = "PlantBlock";

	/** @var Item */
	private $item;

	protected function readSaveData(CompoundTag $nbt) : void
	{
		$this->item = ItemFactory::get($nbt->getShort(self::TAG_ITEM, 0, true), $nbt->getInt(self::TAG_ITEM_DATA, 0, true), 1);
	}

	protected function writeSaveData(CompoundTag $nbt) : void
	{
		$nbt->setShort(self::TAG_ITEM, $this->item->getId());
		$nbt->setInt(self::TAG_ITEM_DATA, $this->item->getDamage());
	}

	public function getItem() : Item
	{
		return clone $this->item;
	}

	/**
	 * @return void
	 */
	public function setItem(Item $item)
	{
		$this->item = clone $item;

		$this->onChanged();
	}

	/**
	 * @return void
	 */
	public function removeItem()
	{
		$this->setItem(ItemFactory::get(Item::AIR, 0, 0));
	}

	public function isEmpty() : bool
	{
		return $this->getItem()->isNull();
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt, int $protocolVersion) : void
	{
		$item = $this->item;
		if ($protocolVersion >= ProtocolInfo::PROTOCOL_419) {
			if(!$item->isNull()){
				$runtimeBlockMapping = RuntimeBlockMapping::getInstance($protocolVersion);
				$block = $item->getBlock();
				$blockProtocol = BlockProtocolConvertor::getInstance()->get($block, $protocolVersion) ?? $block;
				$plantNbt = $runtimeBlockMapping->toNbtBlock($runtimeBlockMapping->toRuntimeId($blockProtocol->getFullId()), true);
				$plantNbt->setName(self::TAG_PLANT_BLOCK);
				$nbt->setTag($plantNbt);
			}
		} else {
			[$id, $meta] = [$item->getId(), $item->getDamage()];

			$itemProtocol = $item->getItemProtocol($protocolVersion);
			if ($itemProtocol !== null) {
				[$id, $meta] = [$itemProtocol->getId(), $itemProtocol->getMeta()];
			}

			$nbt->setShort(self::TAG_ITEM, $id);
			$nbt->setInt(self::TAG_ITEM_DATA, $meta);
		}
	}
}
