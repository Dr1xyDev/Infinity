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

abstract class Slab extends Transparent
{
	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	abstract public function getDoubleSlabId() : int;

	public function canBePlacedAt(Block $blockReplace, Vector3 $clickVector, int $face, bool $isClickedBlock) : bool{
		if(parent::canBePlacedAt($blockReplace, $clickVector, $face, $isClickedBlock)){
			return true;
		}

		if($blockReplace instanceof Slab && $blockReplace->isSameType($this)){
			if($blockReplace->isTop()){ //Trying to combine with top slab
				return $clickVector->y <= 0.5 || (!$isClickedBlock && $face === Facing::UP);
			}else{
				return $clickVector->y >= 0.5 || (!$isClickedBlock && $face === Facing::DOWN);
			}
		}

		return false;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($blockReplace instanceof Slab && $blockReplace->isSameType($this) && (
				($blockReplace->isTop() && ($clickVector->y <= 0.5 || $face === Facing::UP)) ||
				(!$blockReplace->isTop() && ($clickVector->y >= 0.5 || $face === Facing::DOWN))
			)){
			//Clicked in empty half of existing slab
			$this->getLevel()->setBlock($blockReplace, BlockFactory::get($this->getDoubleSlabId(), $this->getVariant()), true);
		}else{
			$this->setTop(($face !== Facing::UP && $clickVector->y > 0.5) || $face === Facing::DOWN);
			$this->getLevel()->setBlock($this, $this, true);
		}

		return true;
	}

	public function getVariantBitmask() : int
	{
		return 0x07;
	}

	public function getTopBitmask() : int {
		return 0x08;
	}

	public function isTop() : bool {
		return ($this->meta & $this->getTopBitmask()) !== 0;
	}

	public function setTop(bool $value) : void {
		$this->meta = ($this->meta & ~$this->getTopBitmask()) | ($value ? $this->getTopBitmask() : 0);
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB
	{
		if ($this->isTop()) {
			return new AxisAlignedBB(
				$this->x,
				$this->y + 0.5,
				$this->z,
				$this->x + 1,
				$this->y + 1,
				$this->z + 1
			);
		} else {
			return new AxisAlignedBB(
				$this->x,
				$this->y,
				$this->z,
				$this->x + 1,
				$this->y + 0.5,
				$this->z + 1
			);
		}
	}

	public function isPassable() : bool
	{
		return $this->isTop();
	}
}
