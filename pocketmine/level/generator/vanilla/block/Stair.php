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
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;

abstract class Stair extends Transparent
{
	protected function recalculateCollisionBoxes() : array
	{
		//TODO: handle corners

		$minYSlab = ($this->meta & 0x04) === 0 ? 0 : 0.5;
		$maxYSlab = $minYSlab + 0.5;

		$bbs = [
			new AxisAlignedBB(
				$this->x,
				$this->y + $minYSlab,
				$this->z,
				$this->x + 1,
				$this->y + $maxYSlab,
				$this->z + 1
			)
		];

		$minY = ($this->meta & 0x04) === 0 ? 0.5 : 0;
		$maxY = $minY + 0.5;

		$rotationMeta = $this->meta & 0x03;

		$minX = $minZ = 0;
		$maxX = $maxZ = 1;

		switch ($rotationMeta) {
			case 0:
				$minX = 0.5;
				break;
			case 1:
				$maxX = 0.5;
				break;
			case 2:
				$minZ = 0.5;
				break;
			case 3:
				$maxZ = 0.5;
				break;
		}

		$bbs[] = new AxisAlignedBB(
			$this->x + $minX,
			$this->y + $minY,
			$this->z + $minZ,
			$this->x + $maxX,
			$this->y + $maxY,
			$this->z + $maxZ
		);

		return $bbs;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		$faces = [
			0 => 0,
			1 => 2,
			2 => 1,
			3 => 3
		];
		$this->meta = $player !== null ? $faces[$player->getDirection()] & 0x03 : 0;
		if (($clickVector->y > 0.5 && $face !== Facing::UP) || $face === Facing::DOWN) {
			$this->meta |= 0x04; //Upside-down stairs
		}
		$this->getLevel()->setBlock($blockReplace, $this, true, true);

		return true;
	}

	public function getVariantBitmask() : int
	{
		return 0;
	}
}
