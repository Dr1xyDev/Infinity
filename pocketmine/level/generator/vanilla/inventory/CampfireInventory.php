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
use pocketmine\level\generator\vanilla\tile\Campfire;

class CampfireInventory extends ContainerInventory
{
	/** @var Campfire */
	protected $holder;

	public function __construct(Campfire $tile)
	{
		parent::__construct($tile);
	}

	public function getNetworkType() : int
	{
		return WindowTypes::NONE;
	}

	public function getName() : string
	{
		return "Campfire";
	}

	public function getDefaultSize() : int
	{
		return 4;
	}

	/**
	 * @return Campfire
	 */
	public function getHolder()
	{
		return $this->holder;
	}
}
