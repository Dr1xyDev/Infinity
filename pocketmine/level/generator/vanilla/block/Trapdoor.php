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
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;

abstract class Trapdoor extends Transparent
{
	public const MASK_UPPER = 0x04;
	public const MASK_OPENED = 0x08;
	public const MASK_SIDE = 0x03;
	public const MASK_SIDE_SOUTH = 2;
	public const MASK_SIDE_NORTH = 3;
	public const MASK_SIDE_EAST = 0;
	public const MASK_SIDE_WEST = 1;

	public function isPassable() : bool
	{
		return ($this->getDamage() & self::MASK_OPENED) > 0;
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB
	{
		$damage = $this->getDamage();

		$f = 0.1875;

		if (($damage & self::MASK_UPPER) > 0) {
			$bb = new AxisAlignedBB(
				$this->x,
				$this->y + 1 - $f,
				$this->z,
				$this->x + 1,
				$this->y + 1,
				$this->z + 1
			);
		} else {
			$bb = new AxisAlignedBB(
				$this->x,
				$this->y,
				$this->z,
				$this->x + 1,
				$this->y + $f,
				$this->z + 1
			);
		}

		if (($damage & self::MASK_OPENED) > 0) {
			if ($this->getFace() === self::MASK_SIDE_NORTH) {
				$bb = new AxisAlignedBB(
					$this->x,
					$this->y,
					$this->z + 1 - $f,
					$this->x + 1,
					$this->y + 1,
					$this->z + 1
				);
			} elseif ($this->getFace() === self::MASK_SIDE_SOUTH) {
				$bb = new AxisAlignedBB(
					$this->x,
					$this->y,
					$this->z,
					$this->x + 1,
					$this->y + 1,
					$this->z + $f
				);
			}
			if ($this->getFace() === self::MASK_SIDE_WEST) {
				$bb = new AxisAlignedBB(
					$this->x + 1 - $f,
					$this->y,
					$this->z,
					$this->x + 1,
					$this->y + 1,
					$this->z + 1
				);
			}
			if ($this->getFace() === self::MASK_SIDE_EAST) {
				$bb = new AxisAlignedBB(
					$this->x,
					$this->y,
					$this->z,
					$this->x + $f,
					$this->y + 1,
					$this->z + 1
				);
			}
		}

		return $bb;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		$directions = [
			0 => self::MASK_SIDE_WEST,
			1 => self::MASK_SIDE_NORTH,
			2 => self::MASK_SIDE_EAST,
			3 => self::MASK_SIDE_SOUTH
		];
		if ($player !== null) {
			$this->meta = $directions[$player->getDirection() & 0x03];
		}

		if (($clickVector->y > 0.5 && $face !== Facing::UP) || $face === Facing::DOWN) {
			$this->setTop(true); //top half of block
		}

		$this->getLevel()->setBlock($blockReplace, $this, true, true);
		return true;
	}

	public function getVariantBitmask() : int
	{
		return 0;
	}

	public function onActivate(Item $item, ?Player $player = null) : bool
	{
		$this->setOpen(!$this->isOpen());
		$this->getLevel()->setBlock($this, $this, true);
		$this->level->addSound(new DoorSound($this));
		return true;
	}

	public function isOpen() : bool{
		return ($this->meta & self::MASK_OPENED) !== 0;
	}

	public function setOpen(bool $value) : void {
		$this->meta = ($this->meta & ~self::MASK_OPENED) | ($value ? self::MASK_OPENED : 0);
	}

	public function isTop() : bool{
		return ($this->meta & self::MASK_UPPER) !== 0;
	}

	public function setTop(bool $value) : void {
		$this->meta = ($this->meta & ~self::MASK_UPPER) | ($value ? self::MASK_UPPER : 0);
	}

	public function getFace() : int {
		return $this->meta & self::MASK_SIDE;
	}
}
