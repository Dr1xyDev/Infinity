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
use pocketmine\level\generator\vanilla\math\AxisAlignedBB;
use pocketmine\level\generator\vanilla\math\Facing;

class GrassPath extends Transparent
{
	protected $id = self::GRASS_PATH;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Grass Path";
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_SHOVEL;
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB
	{
		return new AxisAlignedBB(
			$this->x,
			$this->y,
			$this->z,
			$this->x + 1,
			$this->y + 1, //TODO: this should be 0.9375, but MCPE currently treats them as a full block (https://bugs.mojang.com/browse/MCPE-12109)
			$this->z + 1
		);
	}

	public function getHardness() : float
	{
		return 0.6;
	}

	public function onNearbyBlockChange() : void
	{
		if ($this->getSide(Facing::UP)->isSolid()) {
			$this->level->setBlock($this, BlockFactory::get(Block::DIRT), true);
		}
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		return [
			ItemFactory::get(Item::DIRT)
		];
	}
}
