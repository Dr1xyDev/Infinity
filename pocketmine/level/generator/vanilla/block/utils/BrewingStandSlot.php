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

namespace pocketmine\level\generator\vanilla\block\utils;

use pocketmine\level\generator\vanilla\inventory\BrewingStandInventory;
use pocketmine\level\generator\vanilla\utils\EnumTrait;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
 *
 * @method static BrewingStandSlot EAST()
 * @method static BrewingStandSlot NORTHWEST()
 * @method static BrewingStandSlot SOUTHWEST()
 */
final class BrewingStandSlot
{
	use EnumTrait {
		__construct as Enum___construct;
	}

	protected static function setup() : void
	{
		self::registerAll(
			new self("east", BrewingStandInventory::SLOT_BOTTLE_LEFT),
			new self("northwest", BrewingStandInventory::SLOT_BOTTLE_MIDDLE),
			new self("southwest", BrewingStandInventory::SLOT_BOTTLE_RIGHT)
		);
	}

	private function __construct(string $enumName, private int $slotNumber)
	{
		$this->Enum___construct($enumName);
	}

	/**
	 * Returns the brewing stand inventory slot number associated with this visual slot.
	 */
	public function getSlotNumber() : int
	{
		return $this->slotNumber;
	}
}
