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

namespace pocketmine\level\generator\vanilla\event\inventory;

use pocketmine\level\generator\vanilla\entity\projectile\Arrow;
use pocketmine\level\generator\vanilla\event\Cancellable;
use pocketmine\level\generator\vanilla\inventory\Inventory;

class InventoryPickupArrowEvent extends InventoryEvent implements Cancellable
{
	/** @var Arrow */
	private $arrow;

	public function __construct(Inventory $inventory, Arrow $arrow)
	{
		$this->arrow = $arrow;
		parent::__construct($inventory);
	}

	public function getArrow() : Arrow
	{
		return $this->arrow;
	}
}
