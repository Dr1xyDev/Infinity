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

use pocketmine\level\generator\vanilla\network\mcpe\protocol\BlockEventPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\tile\ShulkerBox;

use function count;

class ShulkerBoxInventory extends ContainerInventory
{
	/** @var ShulkerBox */
	protected $holder;

	public function __construct(ShulkerBox $tile)
	{
		parent::__construct($tile);
	}

	public function getName() : string
	{
		return "Shulker Box";
	}

	public function getDefaultSize() : int
	{
		return 27;
	}

	public function getNetworkType() : int
	{
		return WindowTypes::CONTAINER;
	}

	/**
	 * This override is here for documentation and code completion purposes only.
	 * @return ShulkerBox
	 */
	public function getHolder()
	{
		return $this->holder;
	}

	protected function getOpenSound() : int
	{
		return LevelSoundEventPacket::SOUND_SHULKERBOX_OPEN;
	}

	protected function getCloseSound() : int
	{
		return LevelSoundEventPacket::SOUND_SHULKERBOX_CLOSED;
	}

	public function onOpen(Player $who) : void
	{
		parent::onOpen($who);

		if (count($this->getViewers()) === 1 && $this->getHolder()->isValid()) {
			//TODO: this crap really shouldn't be managed by the inventory
			$this->broadcastBlockEventPacket(true);
			$this->getHolder()->getLevel()->broadcastLevelSoundEvent($this->getHolder()->add(0.5, 0.5, 0.5), $this->getOpenSound());
		}
	}

	public function onClose(Player $who) : void
	{
		if (count($this->getViewers()) === 1 && $this->getHolder()->isValid()) {
			//TODO: this crap really shouldn't be managed by the inventory
			$this->broadcastBlockEventPacket(false);
			$this->getHolder()->getLevel()->broadcastLevelSoundEvent($this->getHolder()->add(0.5, 0.5, 0.5), $this->getCloseSound());
		}
		parent::onClose($who);
	}

	protected function broadcastBlockEventPacket(bool $isOpen) : void
	{
		$holder = $this->getHolder();
		$holder->getLevel()->broadcastPacketToViewers($holder, BlockEventPacket::create(
			(int) $holder->x,
			(int) $holder->y,
			(int) $holder->z,
			BlockEventPacket::TYPE_CHEST,
			$isOpen ? BlockEventPacket::DATA_CHEST_OPEN : BlockEventPacket::DATA_CHEST_CLOSED
		));
	}
}
