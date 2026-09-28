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
use pocketmine\level\generator\vanilla\math\AxisAlignedBB;

/**
 * Air block
 */
class Air extends Transparent
{
	protected $id = self::AIR;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Air";
	}

	public function canPassThrough() : bool
	{
		return true;
	}

	public function isBreakable(Item $item) : bool
	{
		return false;
	}

	public function canBeFlowedInto() : bool
	{
		return true;
	}

	public function canBeReplaced() : bool
	{
		return true;
	}

	public function canBePlaced() : bool
	{
		return false;
	}

	public function isSolid() : bool
	{
		return false;
	}

	public function getBoundingBox() : ?AxisAlignedBB
	{
		return null;
	}

	public function getCollisionBoxes() : array
	{
		return [];
	}

	public function getHardness() : float
	{
		return -1;
	}

	public function getBlastResistance() : float
	{
		return 0;
	}
}
