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
use pocketmine\level\generator\vanilla\item\TieredTool;
use pocketmine\level\generator\vanilla\math\AxisAlignedBB;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;

class Lantern extends Transparent
{
	protected $id = self::LANTERN;

	public const int HANGING_BIT = 0x01;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getLightLevel() : int
	{
		return 15;
	}

	public function getHardness() : float
	{
		return 3.5;
	}

	public function getBlastResistance() : float
	{
		return 3.5;
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_PICKAXE;
	}

	public function getToolHarvestLevel() : int
	{
		return TieredTool::TIER_WOODEN;
	}

	public function getName() : string
	{
		return "Lantern";
	}

	public function isBlockAboveValid() : bool
	{
		$up = $this->getSide(Facing::UP);

		return
			$up->getId() === BlockIds::IRON_BARS ||
			$up->getId() === BlockIds::HOPPER_BLOCK ||
			$up->getId() === BlockIds::CHAIN ||
			($up instanceof Fence) ||
			($up instanceof Slab && !$up->isTop()) ||
			($up instanceof Stair && ($up->getDamage() & 0x04) == 0x00) ||
			(!$up->isTransparent() && $up->isSolid());
	}

	public function isBlockUnderValid() : bool
	{
		$down = $this->getSide(Facing::DOWN);
		return
			!$down->isTransparent() ||
			($down instanceof Glass) ||
			($down instanceof Fence) ||
			($down instanceof Wall) ||
			$down->getId() === BlockIds::CHAIN ||
			$down->getId() === BlockIds::IRON_BARS ||
			$down->isSolid();
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		if ($this->level->getBlock($this) instanceof Liquid) {
			return false;
		}

		$isUnderValid = $this->isBlockUnderValid();
		$hanging = $face != Facing::UP && $this->isBlockAboveValid() && (!$isUnderValid || $face == Facing::DOWN);
		if (!$isUnderValid && !$hanging) {
			return false;
		}

		$this->meta = $hanging ? self::HANGING_BIT : 0;

		$this->getLevel()->setBlock($blockReplace, $this, true);
		return true;
	}

	public function isHanging() : bool
	{
		return $this->meta == self::HANGING_BIT;
	}

	public function setHanging(bool $hanging) : void
	{
		$this->meta = $hanging ? self::HANGING_BIT : 0;
	}

	public function onNearbyBlockChange() : void
	{
		if ($this->meta !== self::HANGING_BIT) {
			if (!$this->isBlockUnderValid()) {
				$this->level->useBreakOn($this);
			}
		} elseif (!$this->isBlockAboveValid()) {
			$this->level->useBreakOn($this);
		}
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB
	{
		return new AxisAlignedBB(
			$this->x + 0.3125,
			$this->y + ($this->meta == 0 ? 0 : 0.0625),
			$this->z + 0.3125,
			$this->x + 0.6875,
			$this->y + ($this->meta == 0 ? 0.375 : 0.5),
			$this->z + 0.6875
		);
	}
}
