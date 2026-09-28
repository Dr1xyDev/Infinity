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

use pocketmine\level\generator\vanilla\entity\EntityIds;
use pocketmine\level\generator\vanilla\entity\passive\Horse;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\nbt\NetworkLittleEndianNBTStream;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\nbt\tag\IntTag;
use pocketmine\level\generator\vanilla\nbt\tag\ListTag;
use pocketmine\level\generator\vanilla\nbt\tag\ShortTag;
use pocketmine\level\generator\vanilla\nbt\tag\StringTag;
use pocketmine\level\generator\vanilla\network\mcpe\convert\TypeConverter;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ContainerClosePacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\MobArmorEquipmentPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\UpdateEquipPacket;
use pocketmine\Player;

class HorseInventory extends AbstractHorseInventory
{
	/** @var Horse */
	protected $holder;

	public function getName() : string
	{
		return "Horse";
	}

	public function getDefaultSize() : int
	{
		return 2;
	}

	public function getArmor() : Item
	{
		return $this->getItem(1);
	}

	public function setArmor(Item $armor) : void
	{
		$this->setItem(1, $armor);

		foreach ($this->viewers as $player) {
			$this->sendArmor($player);
		}
	}

	public function getNetworkType() : int
	{
		return WindowTypes::HORSE;
	}

	public function onSlotChange(int $index, Item $before, bool $send) : void
	{
		parent::onSlotChange($index, $before, $send);

		if ($index === 1) {
			foreach ($this->viewers as $player) {
				$this->sendArmor($player);
			}

			$this->holder->level->broadcastLevelSoundEvent($this->holder, LevelSoundEventPacket::SOUND_ARMOR, -1, EntityIds::HORSE);
		}
	}

	/**
	 * @return Horse
	 */
	public function getHolder()
	{
		return $this->holder;
	}

	public function sendArmor(Player $player) : void
	{
		$protocolVersion = $player->getProtocolVersion();
		$typeConverter = TypeConverter::getInstance();

		$airItemStack = $typeConverter->coreItemStackToNet(ItemFactory::air(), $protocolVersion);

		$player->sendDataPacket(MobArmorEquipmentPacket::create(
			$this->getHolder()->getId(),
			ItemStackWrapper::legacy(clone $airItemStack),
			ItemStackWrapper::legacy($typeConverter->coreItemStackToNet($this->getItem(1), $protocolVersion)),
			ItemStackWrapper::legacy(clone $airItemStack),
			ItemStackWrapper::legacy(clone $airItemStack),
			ItemStackWrapper::legacy(clone $airItemStack)
		));
	}

	public function onOpen(Player $who) : void
	{
		parent::onOpen($who);

		$pk = new UpdateEquipPacket();
		$pk->entityUniqueId = $this->holder->getId();
		$pk->windowSlotCount = 0;
		$pk->windowType = $this->getNetworkType();
		$pk->windowId = $who->getWindowId($this);
		$pk->namedtag = (new NetworkLittleEndianNBTStream())->write($this->getNamedtag($who->getProtocolVersion()));

		$who->sendDataPacket($pk);

		$this->sendArmor($who);
		$this->sendContents($who);
	}

	public function onClose(Player $who) : void
	{
		$pk = new ContainerClosePacket();
		$pk->windowId = $who->getWindowId($this);
		$pk->windowType = $who->getCurrentWindowType();
		$pk->server = $who->getClosingWindowId() !== $pk->windowId;
		$who->dataPacket($pk);
		parent::onClose($who);
	}

	public function getNamedtag(int $playerProtocol) : CompoundTag
	{
		if ($playerProtocol >= ProtocolInfo::PROTOCOL_407) {
			$saddle = [
				new ListTag("acceptedItems", [
					new CompoundTag("", [
						new CompoundTag("slotItem", [
							new ShortTag("Aux", 32767),
							new StringTag("Name", "minecraft:saddle")
						])
					])
				]),
				new IntTag("slotNumber", 0)
			];
			if (!$this->getSaddle()->isNull()) {
				$saddle[] = new CompoundTag("item", [
					new StringTag("Name", "minecraft:saddle"),
					new ShortTag("Aux", 32767)
				]);
			}
			$armor = [
				new ListTag("acceptedItems", [
					new CompoundTag("", [
						new CompoundTag("slotItem", [
							new ShortTag("Aux", 32767),
							new StringTag("Name", "minecraft:leather_horse_armor")
						])
					]),
					new CompoundTag("", [
						new CompoundTag("slotItem", [
							new ShortTag("Aux", 32767),
							new StringTag("Name", "minecraft:iron_horse_armor")
						])
					]),
					new CompoundTag("", [
						new CompoundTag("slotItem", [
							new ShortTag("Aux", 32767),
							new StringTag("Name", "minecraft:golden_horse_armor")
						])
					]),
					new CompoundTag("", [
						new CompoundTag("slotItem", [
							new ShortTag("Aux", 32767),
							new StringTag("Name", "minecraft:diamond_horse_armor")
						])
					])
				]),
				new IntTag("slotNumber", 1)
			];
			if (!$this->getArmor()->isNull()) {
				$armor[] = new CompoundTag("item", [
					new StringTag("Name", $this->translateHorseArmorIdToStringName($this->getArmor()->getId())),
					new ShortTag("Aux", 32767)
				]);
			}
			return new CompoundTag("", [
				new ListTag("slots", [
					new CompoundTag("", $saddle),
					new CompoundTag("", $armor)
				])
			]);
		} else {
			return new CompoundTag("", [
				new ListTag("slots", [
					new CompoundTag("", [
						new ListTag("acceptedItems", [
							new CompoundTag("", [
								(ItemFactory::get(Item::SADDLE))->nbtSerialize(-1, "slotItem")
							])
						]),
						$this->getSaddle()->nbtSerialize(-1, "item"),
						new IntTag("slotNumber", 0)
					]),
					new CompoundTag("", [
						new ListTag("acceptedItems", [
							new CompoundTag("", [
								(ItemFactory::get(Item::HORSE_ARMOR_DIAMOND))->nbtSerialize(-1, "slotItem")
							]),
							new CompoundTag("", [
								(ItemFactory::get(Item::HORSE_ARMOR_GOLD))->nbtSerialize(-1, "slotItem")
							]),
							new CompoundTag("", [
								(ItemFactory::get(Item::HORSE_ARMOR_IRON))->nbtSerialize(-1, "slotItem")
							]),
							new CompoundTag("", [
								(ItemFactory::get(Item::HORSE_ARMOR_LEATHER))->nbtSerialize(-1, "slotItem")
							])
						]),
						$this->getArmor()->nbtSerialize(-1, "item"),
						new IntTag("slotNumber", 1)
					])
				])
			]);
		}
	}

	public function translateHorseArmorIdToStringName(int $armorId) : string
	{
		return match($armorId) {
			Item::HORSE_ARMOR_DIAMOND => "minecraft:diamond_horse_armor",
			Item::HORSE_ARMOR_GOLD => "minecraft:golden_horse_armor",
			Item::HORSE_ARMOR_IRON => "minecraft:iron_horse_armor",
			Item::HORSE_ARMOR_LEATHER => "minecraft:leather_horse_armor",
			default => "minecraft:leather_horse_armor",
		};
	}
}
