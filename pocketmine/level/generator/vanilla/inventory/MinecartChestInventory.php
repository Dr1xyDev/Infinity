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

use pocketmine\level\generator\vanilla\entity\vehicle\MinecartChest;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\WindowTypes;

class MinecartChestInventory extends ContainerInventory
{
	/** @var MinecartChest */
	protected $holder;

	public function __construct(MinecartChest $holder)
	{
		parent::__construct($holder);
	}

	public function getNetworkType() : int
	{
		return WindowTypes::MINECART_CHEST;
	}

	public function getName() : string
	{
		return "Minecart with Chest";
	}

	public function getDefaultSize() : int
	{
		return 27;
	}

	/**
	 * @return MinecartChest
	 */
	public function getHolder()
	{
		return $this->holder;
	}
}
