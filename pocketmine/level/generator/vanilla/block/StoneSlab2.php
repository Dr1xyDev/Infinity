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

class StoneSlab2 extends StoneSlab
{
	public const TYPE_RED_SANDSTONE = 0;
	public const TYPE_PURPUR = 1;
	public const TYPE_PRISMARINE = 2;
	public const TYPE_DARK_PRISMARINE = 3;
	public const TYPE_PRISMARINE_BRICKS = 4;
	public const TYPE_MOSSY_COBBLESTONE = 5;
	public const TYPE_SMOOTH_SANDSTONE = 6;
	public const TYPE_RED_NETHER_BRICK = 7;

	protected $id = self::STONE_SLAB2;

	public function getDoubleSlabId() : int
	{
		return self::DOUBLE_STONE_SLAB2;
	}

	public function getName() : string
	{
		static $names = [
			self::TYPE_RED_SANDSTONE => "Red Sandstone",
			self::TYPE_PURPUR => "Purpur",
			self::TYPE_PRISMARINE => "Prismarine",
			self::TYPE_DARK_PRISMARINE => "Dark Prismarine",
			self::TYPE_PRISMARINE_BRICKS => "Prismarine Bricks",
			self::TYPE_MOSSY_COBBLESTONE => "Mossy Cobblestone",
			self::TYPE_SMOOTH_SANDSTONE => "Smooth Sandstone",
			self::TYPE_RED_NETHER_BRICK => "Red Nether Brick"
		];

		return ($this->isTop() ? "Upper " : "") . ($names[$this->getVariant()] ?? "") . " Slab";
	}
}
