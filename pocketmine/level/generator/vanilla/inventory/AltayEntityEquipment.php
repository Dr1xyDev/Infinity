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
use pocketmine\level\generator\vanilla\inventory\utils\EquipmentSlot;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\network\mcpe\convert\TypeConverter;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\ContainerIds;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\Player;

class AltayEntityEquipment extends BaseInventory
{
	/** @var Living */
	protected $holder;

	public function __construct(Living $entity)
	{
		$this->holder = $entity;
		parent::__construct();
	}

	public function getName() : string
	{
		return "Entity Equipment";
	}

	public function getDefaultSize() : int
	{
		return 2; // equipment slots (1 mainhand, 1 offhand)
	}

	public function getHolder() : Living
	{
		return $this->holder;
	}

	public function sendSlot(int $index, $target) : void
	{
		if ($target instanceof Player) {
			$target = [$target];
		}

		/** @var Player[][] $protocolPlayers */
		$protocolPlayers = [];
		foreach ($target as $player) {
			$protocolPlayers[$player->getProtocolVersion()][] = $player;
		}

		foreach ($protocolPlayers as $protocolVersion => $players) {
			$pk = MobEquipmentPacket::create(
				$this->holder->getId(),
				ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet($this->getItem($index), $protocolVersion)),
				$index,
				$index,
				ContainerIds::INVENTORY
			);

			foreach ($players as $player) {
				$player->sendDataPacket($pk);
			}
		}
	}

	public function getViewers() : array
	{
		return $this->holder->getViewers();
	}

	public function getItemInHand() : Item
	{
		return $this->getItem(EquipmentSlot::MAINHAND);
	}

	public function getOffhandItem() : Item
	{
		return $this->getItem(EquipmentSlot::OFFHAND);
	}

	public function setItemInHand(Item $item, bool $send = true) : bool
	{
		return $this->setItem(EquipmentSlot::MAINHAND, $item, $send);
	}

	public function setOffhandItem(Item $item, bool $send = true) : bool
	{
		return $this->setItem(EquipmentSlot::OFFHAND, $item, $send);
	}

	public function sendContents($target) : void
	{
		$this->sendSlot(EquipmentSlot::MAINHAND, $target);
		$this->sendSlot(EquipmentSlot::OFFHAND, $target);
	}
}
