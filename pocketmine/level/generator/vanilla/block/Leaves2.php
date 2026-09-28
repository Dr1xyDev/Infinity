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

namespace pocketmine\level\generator\vanilla\block;

use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;

class Leaves2 extends Leaves
{
	protected $id = self::LEAVES2;
	protected int $woodType = self::WOOD2;

	public function getName() : string
	{
		static $names = [
			self::ACACIA => "Acacia Leaves",
			self::DARK_OAK => "Dark Oak Leaves"
		];
		return $names[$this->getVariant()] ?? "Unknown";
	}

	public function getSaplingItem() : Item
	{
		return ItemFactory::get(Item::SAPLING, $this->getVariant() + 4);
	}

	public function canDropApples() : bool
	{
		return $this->getVariant() === self::DARK_OAK;
	}
}
