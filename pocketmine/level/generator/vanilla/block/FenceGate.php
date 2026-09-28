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
use pocketmine\level\generator\vanilla\level\sound\DoorSound;
use pocketmine\level\generator\vanilla\math\AxisAlignedBB;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;

class FenceGate extends Transparent
{
	public function getHardness() : float
	{
		return 2;
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_AXE;
	}

	public function isPassable() : bool
	{
		return ($this->getDamage() & 0x04) > 0;
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB
	{

		if (($this->getDamage() & 0x04) > 0) {
			return null;
		}

		$i = ($this->getDamage() & 0x03);
		if ($i === 2 || $i === 0) {
			return new AxisAlignedBB(
				$this->x,
				$this->y,
				$this->z + 0.375,
				$this->x + 1,
				$this->y + 1.5,
				$this->z + 0.625
			);
		} else {
			return new AxisAlignedBB(
				$this->x + 0.375,
				$this->y,
				$this->z,
				$this->x + 0.625,
				$this->y + 1.5,
				$this->z + 1
			);
		}
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		$this->meta = ($player instanceof Player ? ($player->getDirection() - 1) & 0x03 : 0);
		$this->getLevel()->setBlock($blockReplace, $this, true, true);

		return true;
	}

	public function getVariantBitmask() : int
	{
		return 0;
	}

	public function onActivate(Item $item, ?Player $player = null) : bool
	{
		$this->meta = (($this->meta ^ 0x04) & ~0x02);

		if ($player !== null) {
			$this->meta |= (($player->getDirection() - 1) & 0x02);
		}

		$this->getLevel()->setBlock($this, $this, true);
		$this->level->addSound(new DoorSound($this));
		return true;
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
