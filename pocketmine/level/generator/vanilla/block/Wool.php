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

use pocketmine\level\generator\vanilla\block\utils\ColorBlockMetaHelper;
use pocketmine\level\generator\vanilla\item\Item;

class Wool extends Solid
{
	protected $id = self::WOOL;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getHardness() : float
	{
		return 0.8;
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_SHEARS;
	}

	public function getName() : string
	{
		return ColorBlockMetaHelper::getColorFromMeta($this->getVariant()) . " Wool";
	}

	public function getBreakTime(Item $item) : float
	{
		$time = parent::getBreakTime($item);
		if ($item->getBlockToolType() === BlockToolType::TYPE_SHEARS) {
			$time *= 3; //shears break compatible blocks 15x faster, but wool 5x
		}

		return $time;
	}

	public function getFlameEncouragement() : int
	{
		return 30;
	}

	public function getFlammability() : int
	{
		return 60;
	}
}
