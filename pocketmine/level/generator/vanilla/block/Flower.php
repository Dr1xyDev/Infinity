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

use pocketmine\level\generator\vanilla\block\utils\StaticSupportTrait;
use pocketmine\level\generator\vanilla\math\Facing;

class Flower extends Flowable
{
	use StaticSupportTrait;

	public const TYPE_POPPY = 0;
	public const TYPE_BLUE_ORCHID = 1;
	public const TYPE_ALLIUM = 2;
	public const TYPE_AZURE_BLUET = 3;
	public const TYPE_RED_TULIP = 4;
	public const TYPE_ORANGE_TULIP = 5;
	public const TYPE_WHITE_TULIP = 6;
	public const TYPE_PINK_TULIP = 7;
	public const TYPE_OXEYE_DAISY = 8;
	public const TYPE_CORNFLOWER = 9;
	public const TYPE_LILY_OF_THE_VALLEY = 10;

	protected $id = self::RED_FLOWER;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		static $names = [
			self::TYPE_POPPY => "Poppy",
			self::TYPE_BLUE_ORCHID => "Blue Orchid",
			self::TYPE_ALLIUM => "Allium",
			self::TYPE_AZURE_BLUET => "Azure Bluet",
			self::TYPE_RED_TULIP => "Red Tulip",
			self::TYPE_ORANGE_TULIP => "Orange Tulip",
			self::TYPE_WHITE_TULIP => "White Tulip",
			self::TYPE_PINK_TULIP => "Pink Tulip",
			self::TYPE_OXEYE_DAISY => "Oxeye Daisy"
		];
		return $names[$this->getVariant()] ?? "Unknown";
	}

	protected function canBeSupportedAt(Block $block) : bool{
		$supportBlock = $block->getSide(Facing::DOWN);
		return
			$supportBlock instanceof Grass ||
			$supportBlock instanceof Dirt ||
			$supportBlock instanceof Mycelium ||
			$supportBlock instanceof Podzol ||
			$supportBlock instanceof Farmland ||
			$supportBlock instanceof Mud;
	}

	public function getFlameEncouragement() : int
	{
		return 60;
	}

	public function getFlammability() : int
	{
		return 100;
	}
}
