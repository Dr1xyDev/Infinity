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

use pocketmine\level\generator\vanilla\math\Axis;
use pocketmine\level\generator\vanilla\math\AxisAlignedBB;
use pocketmine\level\generator\vanilla\math\Facing;

use function count;

abstract class Thin extends Transparent
{
	protected function recalculateBoundingBox() : ?AxisAlignedBB{
		$width = 0.5 - 0.125 / 2;

		return new AxisAlignedBB(
			$this->x + ($this->canConnect($this->getSide(Facing::WEST)) ? 0 : $width),
			$this->y,
			$this->z + ($this->canConnect($this->getSide(Facing::NORTH)) ? 0 : $width),
			$this->x + 1 - ($this->canConnect($this->getSide(Facing::EAST)) ? 0 : $width),
			$this->y + 1,
			$this->z + 1 - ($this->canConnect($this->getSide(Facing::SOUTH)) ? 0 : $width)
		);
	}

	protected function recalculateCollisionBoxes() : array{
		$inset = 0.5 - 0.125 / 2;

		/** @var AxisAlignedBB[] $bbs */
		$bbs = [];

		$connectWest = $this->canConnect($this->getSide(Facing::WEST));
		$connectEast = $this->canConnect($this->getSide(Facing::EAST));

		if($connectWest || $connectEast){
			//X axis (west/east)
			$bbs[] = new AxisAlignedBB(
				$this->x + ($connectWest ? 0 : $inset),
				$this->y,
				$this->z + $inset,
				$this->x + 1 - ($connectEast ? 0 : $inset),
				$this->y + 1,
				$this->z + 1 - $inset
			);
		}

		$connectNorth = $this->canConnect($this->getSide(Facing::NORTH));
		$connectSouth = $this->canConnect($this->getSide(Facing::SOUTH));

		if($connectNorth || $connectSouth){
			//Z axis (north/south)
			$bbs[] = new AxisAlignedBB(
				$this->x + $inset,
				$this->y,
				$this->z + ($connectNorth ? 0 : $inset),
				$this->x + 1 - $inset,
				$this->y + 1,
				$this->z + 1 - ($connectSouth ? 0 : $inset)
			);
		}

		if(count($bbs) === 0){
			//centre post AABB (only needed if not connected on any axis - other BBs overlapping will do this if any connections are made)
			return [
				new AxisAlignedBB(
					$this->x + $inset,
					$this->y,
					$this->z + $inset,
					$this->x + 1 - $inset,
					$this->y + 1,
					$this->z + 1 - $inset
				)
			];
		}

		return $bbs;
	}

	public function canConnect(Block $block) : bool
	{
		if ($block instanceof Thin) {
			return true;
		}

		//FIXME: currently there's no proper way to tell if a block is a full-block, so we check the bounding box size
		$bb = $block->getBoundingBox();
		return $bb !== null && $bb->getAverageEdgeLength() >= 1;
	}
}
