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

use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\level\generator\vanilla\tile\Hopper;

class HopperInventory extends ContainerInventory
{
	/** @var Hopper */
	protected $holder;

	public function __construct(Hopper $tile)
	{
		parent::__construct($tile);
	}

	public function getNetworkType() : int
	{
		return WindowTypes::HOPPER;
	}

	public function getName() : string
	{
		return "Hopper";
	}

	public function getDefaultSize() : int
	{
		return 5;
	}

	/**
	 * This override is here for documentation and code completion purposes only.
	 * @return Hopper
	 */
	public function getHolder()
	{
		return $this->holder;
	}
}
