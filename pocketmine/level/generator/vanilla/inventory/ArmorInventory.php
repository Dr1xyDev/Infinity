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

use pocketmine\level\generator\vanilla\entity\Living;
use pocketmine\level\generator\vanilla\item\ArmorSlot;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\item\ItemIds;
use pocketmine\level\generator\vanilla\network\mcpe\convert\TypeConverter;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\MobArmorEquipmentPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\ContainerIds;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\Player;

use function array_map;
use function array_merge;

class ArmorInventory extends BaseInventory
{
	public const SLOT_HEAD = 0;
	public const SLOT_CHEST = 1;
	public const SLOT_LEGS = 2;
	public const SLOT_FEET = 3;

	/** @var Living */
	protected $holder;

	public function __construct(Living $holder)
	{
		$this->holder = $holder;
		parent::__construct();
	}

	public function getHolder() : Living
	{
		return $this->holder;
	}

	public function getName() : string
	{
		return "Armor";
	}

	public function getDefaultSize() : int
	{
		return 4;
	}

	public function getHelmet() : Item
	{
		return $this->getItem(self::SLOT_HEAD);
	}

	public function getChestplate() : Item
	{
		return $this->getItem(self::SLOT_CHEST);
	}

	public function getLeggings() : Item
	{
		return $this->getItem(self::SLOT_LEGS);
	}

	public function getBoots() : Item
	{
		return $this->getItem(self::SLOT_FEET);
	}

	public function setHelmet(Item $helmet) : bool
	{
		return $this->setItem(self::SLOT_HEAD, $helmet);
	}

	public function setChestplate(Item $chestplate) : bool
	{
		return $this->setItem(self::SLOT_CHEST, $chestplate);
	}

	public function setLeggings(Item $leggings) : bool
	{
		return $this->setItem(self::SLOT_LEGS, $leggings);
	}

	public function setBoots(Item $boots) : bool
	{
		return $this->setItem(self::SLOT_FEET, $boots);
	}

	public function setItem(int $index, Item $item, bool $send = true) : bool
	{
		if (($item instanceof ArmorSlot && $item->getArmorSlot() === $index) || $item->isNull()) {
			return parent::setItem($index, $item, $send);
		}

		return false;
	}

	public function sendSlot(int $index, $target) : void
	{
		if ($target instanceof Player) {
			$target = [$target];
		}

		$item = $this->getItem($index);
		$typeConverter = TypeConverter::getInstance();

		/** @var Player[][] $protocolPlayers */
		$protocolPlayers = [];
		foreach ($target as $player) {
			$protocolPlayers[$player->getProtocolVersion()][] = $player;
		}

		foreach ($protocolPlayers as $protocolVersion => $players) {
			$itemStack = $typeConverter->coreItemStackToNet($item, $protocolVersion);

			foreach ($players as $player) {
				if ($player === $this->getHolder()) {
					$player->sendInventorySlotPackets(ContainerIds::ARMOR, $index, ItemStackWrapper::legacy($itemStack));
				} else {
					$player->sendDataPacket(MobArmorEquipmentPacket::create(
						$this->getHolder()->getId(),
						ItemStackWrapper::legacy($typeConverter->coreItemStackToNet($this->getHelmet(), $protocolVersion)),
						ItemStackWrapper::legacy($typeConverter->coreItemStackToNet($this->getChestplate(), $protocolVersion)),
						ItemStackWrapper::legacy($typeConverter->coreItemStackToNet($this->getLeggings(), $protocolVersion)),
						ItemStackWrapper::legacy($typeConverter->coreItemStackToNet($this->getBoots(), $protocolVersion)),
						ItemStackWrapper::legacy($typeConverter->coreItemStackToNet(ItemFactory::get(ItemIds::AIR), $protocolVersion))
					));
				}
			}
		}
	}

	public function sendContents($target) : void
	{
		if ($target instanceof Player) {
			$target = [$target];
		}

		$contents = $this->getContents(true);
		$typeConverter = TypeConverter::getInstance();

		/** @var Player[][] $protocolPlayers */
		$protocolPlayers = [];
		foreach ($target as $player) {
			$protocolPlayers[$player->getProtocolVersion()][] = $player;
		}

		foreach ($protocolPlayers as $protocolVersion => $players) {
			$itemStacks = array_map(fn (Item $item) => $typeConverter->coreItemStackToNet($item, $protocolVersion), $contents);

			foreach ($players as $player) {
				if ($player === $this->getHolder()) {
					$player->sendInventoryContentPackets(ContainerIds::ARMOR, array_map(fn (ItemStack $itemStack) => ItemStackWrapper::legacy($itemStack), $itemStacks));
				} else {
					$player->sendDataPacket(MobArmorEquipmentPacket::create(
						$this->getHolder()->getId(),
						ItemStackWrapper::legacy($typeConverter->coreItemStackToNet($this->getHelmet(), $protocolVersion)),
						ItemStackWrapper::legacy($typeConverter->coreItemStackToNet($this->getChestplate(), $protocolVersion)),
						ItemStackWrapper::legacy($typeConverter->coreItemStackToNet($this->getLeggings(), $protocolVersion)),
						ItemStackWrapper::legacy($typeConverter->coreItemStackToNet($this->getBoots(), $protocolVersion)),
						ItemStackWrapper::legacy($typeConverter->coreItemStackToNet(ItemFactory::get(ItemIds::AIR), $protocolVersion))
					));
				}
			}
		}
	}

	public function onSlotChange(int $index, Item $before, bool $send) : void
	{
		$this->sendSlot($index, $this->getViewers());
	}

	/**
	 * @return Player[]
	 */
	public function getViewers() : array
	{
		return array_merge(parent::getViewers(), $this->holder->getViewers());
	}
}
