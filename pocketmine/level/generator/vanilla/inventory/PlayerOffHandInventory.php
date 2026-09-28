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

use BadMethodCallException;
use pocketmine\level\generator\vanilla\entity\Human;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\network\mcpe\convert\TypeConverter;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\ContainerIds;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\Player;

use function array_merge;

class PlayerOffHandInventory extends BaseInventory
{
	/** @var Human */
	protected $holder;

	public function __construct(Human $holder)
	{
		$this->holder = $holder;
		parent::__construct();
	}

	public function getName() : string
	{
		return "OffHand";
	}

	public function getDefaultSize() : int
	{
		return 1;
	}

	public function getHolder() : Human
	{
		return $this->holder;
	}

	/**
	 * @deprecated
	 */
	public function setItemInHand(Item $item) : void
	{
		$this->setItemInOffHand($item);
	}

	public function setItemInOffHand(Item $item) : void
	{
		$this->setItem(0, $item);
	}

	public function getItemInOffHand() : Item
	{
		return $this->getItem(0);
	}

	public function onSlotChange(int $index, Item $before, bool $send) : void
	{
		if ($send === true) {
			foreach ($this->viewers as $viewer) {
				$this->sendContents($viewer); // Sync contents of this inventory instead of slot... #blamemojang?
			}
		}

		foreach ($this->holder->getViewers() as $viewer) {
			$this->sendOffhand($viewer);
		}
	}

	public function setSize(int $size)
	{
		throw new BadMethodCallException("OffHand can only carry one item at a time");
	}

	public function sendOffhand(Player $target) : void
	{
		$target->sendDataPacket(MobEquipmentPacket::create(
			$this->holder->getId(),
			ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet($this->getItemInHand(), $target->getProtocolVersion())),
			$this->getHeldItemIndex(),
			$this->getHeldItemIndex(),
			ContainerIds::OFFHAND
		));
	}

	public function getItemInHand() : Item
	{
		return $this->getItem(0);
	}

	public function getHeldItemIndex() : int
	{
		return 0;
	}

	/**
	 * @return Player[]
	 */
	public function getViewers() : array
	{
		return array_merge(parent::getViewers(), $this->holder->getViewers());
	}
}
