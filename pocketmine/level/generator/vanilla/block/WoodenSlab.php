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

class WoodenSlab extends Slab
{
	public const TYPE_OAK = 0;
	public const TYPE_SPRUCE = 1;
	public const TYPE_BIRCH = 2;
	public const TYPE_JUNGLE = 3;
	public const TYPE_ACACIA = 4;
	public const TYPE_DARK_OAK = 5;

	protected $id = self::WOODEN_SLAB;

	public function getDoubleSlabId() : int
	{
		return self::DOUBLE_WOODEN_SLAB;
	}

	public function getHardness() : float
	{
		return 2;
	}

	public function getName() : string
	{
		static $names = [
			self::TYPE_OAK => "Oak",
			self::TYPE_SPRUCE => "Spruce",
			self::TYPE_BIRCH => "Birch",
			self::TYPE_JUNGLE => "Jungle",
			self::TYPE_ACACIA => "Acacia",
			self::TYPE_DARK_OAK => "Dark Oak"
		];
		return ($this->isTop() ? "Upper " : "") . ($names[$this->getVariant()] ?? "") . " Wooden Slab";
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_AXE;
	}

	public function getFuelTime() : int
	{
		return 300;
	}

	public function getFlameEncouragement() : int
	{
		return 5;
	}

	public function getFlammability() : int
	{
		return 20;
	}
}
