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

class StoneSlab4 extends StoneSlab
{
	public const TYPE_MOSSY_STONE_BRICK = 0;
	public const TYPE_SMOOTH_QUARTZ = 1;
	public const TYPE_STONE = 2;
	public const TYPE_CUT_SANDSTONE = 3;
	public const TYPE_CUT_RED_SANDSTONE = 4;

	protected $id = self::STONE_SLAB4;

	public function getDoubleSlabId() : int
	{
		return self::DOUBLE_STONE_SLAB4;
	}

	public function getName() : string
	{
		static $names = [
			self::TYPE_MOSSY_STONE_BRICK => "Mossy Stone",
			self::TYPE_SMOOTH_QUARTZ => "Smooth Quartz",
			self::TYPE_STONE => "Stone",
			self::TYPE_CUT_SANDSTONE => "Cut Sandstone",
			self::TYPE_CUT_RED_SANDSTONE => "Cut Red Sandstone",
		];

		return ($this->isTop() ? "Upper " : "") . ($names[$this->getVariant()] ?? "") . " Slab";
	}
}
