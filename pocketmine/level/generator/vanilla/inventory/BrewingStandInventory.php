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
use pocketmine\level\generator\vanilla\tile\BrewingStand;

class BrewingStandInventory extends ContainerInventory
{
	public const SLOT_INGREDIENT = 0;
	public const SLOT_BOTTLE_LEFT = 1;
	public const SLOT_BOTTLE_MIDDLE = 2;
	public const SLOT_BOTTLE_RIGHT = 3;
	public const SLOT_FUEL = 4;

	/** @var BrewingStand */
	protected $holder;

	public function __construct(BrewingStand $holder, array $items = [], ?int $size = null, ?string $title = null)
	{
		parent::__construct($holder, $items, $size, $title);
	}

	public function getDefaultSize() : int
	{
		return 5;
	}

	public function getName() : string
	{
		return "Brewing";
	}

	public function getNetworkType() : int
	{
		return WindowTypes::BREWING_STAND;
	}
}
