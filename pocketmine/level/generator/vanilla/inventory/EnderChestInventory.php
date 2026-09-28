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

use pocketmine\level\generator\vanilla\level\Position;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\level\generator\vanilla\tile\EnderChest;

class EnderChestInventory extends ChestInventory
{
	public function __construct()
	{
		ContainerInventory::__construct(new Position(0, 0, 0));
	}

	public function getNetworkType() : int
	{
		return WindowTypes::CONTAINER;
	}

	public function getName() : string
	{
		return "EnderChest";
	}

	public function getDefaultSize() : int
	{
		return 27;
	}

	/**
	 * Set the holder's position to that of a tile
	 */
	public function setHolderPosition(EnderChest $enderChest) : void
	{
		$this->holder->x = $enderChest->getFloorX();
		$this->holder->y = $enderChest->getFloorY();
		$this->holder->z = $enderChest->getFloorZ();
		$this->holder->setLevel($enderChest->getLevel());
	}

	protected function getOpenSound() : int
	{
		return LevelSoundEventPacket::SOUND_ENDERCHEST_OPEN;
	}

	protected function getCloseSound() : int
	{
		return LevelSoundEventPacket::SOUND_ENDERCHEST_CLOSED;
	}

	/**
	 * This override is here for documentation and code completion purposes only.
	 * @return Position
	 */
	public function getHolder()
	{
		return $this->holder;
	}
}
