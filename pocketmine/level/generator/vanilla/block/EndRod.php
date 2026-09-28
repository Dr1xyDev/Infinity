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

class EndRod extends Flowable
{
	protected $id = Block::END_ROD;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "End Rod";
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		if ($face === Facing::UP || $face === Facing::DOWN) {
			$this->meta = $face;
		} else {
			$this->meta = $face ^ 0x01;
		}
		if ($blockClicked instanceof EndRod && $blockClicked->getDamage() === $this->meta) {
			$this->meta ^= 0x01;
		}

		$this->level->setBlock($blockReplace, $this, true, true);
		return true;
	}

	public function isSolid() : bool
	{
		return true;
	}

	public function getLightLevel() : int
	{
		return 14;
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB
	{
		$m = $this->meta & ~0x01;
		$width = 0.375;

		return match ($m) {
			0x00 => new AxisAlignedBB(
				$this->x + $width,
				$this->y,
				$this->z + $width,
				$this->x + 1 - $width,
				$this->y + 1,
				$this->z + 1 - $width
			),
			0x02 => new AxisAlignedBB(
				$this->x,
				$this->y + $width,
				$this->z + $width,
				$this->x + 1,
				$this->y + 1 - $width,
				$this->z + 1 - $width
			),
			0x04 => new AxisAlignedBB(
				$this->x + $width,
				$this->y + $width,
				$this->z,
				$this->x + 1 - $width,
				$this->y + 1 - $width,
				$this->z + 1
			),
			default => null,
		};

	}

	public function getVariantBitmask() : int
	{
		return 0;
	}
}
