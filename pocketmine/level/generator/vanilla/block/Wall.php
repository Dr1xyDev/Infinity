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

use pocketmine\level\generator\vanilla\block\utils\WallConnectionType;
use pocketmine\level\generator\vanilla\math\AxisAlignedBB;
use pocketmine\level\generator\vanilla\math\Facing;

class Wall extends Transparent {
	public function onNearbyBlockChange() : void{
		if($this->recalculateConnections()){
			$this->level->setBlock($this, $this);
		}
	}

	protected function recalculateConnections() : bool{
		$east = $this->calculateConnection(Facing::EAST);
		$north = $this->calculateConnection(Facing::NORTH);
		$south = $this->calculateConnection(Facing::SOUTH);
		$west = $this->calculateConnection(Facing::WEST);
		$post = ($this->getSide(Facing::UP)->getId() !== BlockIds::AIR) ? 1 : 0;

		$meta = $east * 6 + $north * 2 + $south * 18 + $west * 54 + $post;
		if ($this->meta !== $meta){
			$this->meta = $meta;
			return true;
		}

		return false;
	}

	/**
	 * @see WallConnectionType
	 */
	public function calculateConnection(int $face) : int{
		$sidePos = $this->getSide($face);
		$side = $this->level->getBlock($sidePos);
		$up = $this->getSide(Facing::UP);
		if ($this->canConnect($side)) {
			$boxes = $up->getCollisionBoxes();

			foreach ($boxes as $bb) {
				$bb = $bb->offsetCopy(-$up->x, -$up->y, -$up->z);
				if ($bb->minY === 0.0) {
					$xOverlap = $bb->minX < 0.75 && $bb->maxX > 0.25;
					$zOverlap = $bb->minZ < 0.75 && $bb->maxZ > 0.25;
					$tall = false;
					switch ($face) {
						case Facing::NORTH:
							$tall = $xOverlap && $bb->maxZ > 0.75;
							break;
						case Facing::EAST:
							$tall = $bb->minX < 0.25 && $zOverlap;
							break;
						case Facing::SOUTH:
							$tall = $xOverlap && $bb->minZ < 0.25;
							break;
						case Facing::WEST:
							$tall = $bb->maxX > 0.75 && $zOverlap;
							break;
					}

					if ($tall) {
						return WallConnectionType::TALL;
					}
				}
			}

			return WallConnectionType::SHORT;
		}

		return WallConnectionType::NONE;
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB{
		//walls don't have any special collision boxes like fences do

		$east = $this->canConnect($this->getSide(Facing::EAST));
		$north = $this->canConnect($this->getSide(Facing::NORTH));
		$south = $this->canConnect($this->getSide(Facing::SOUTH));
		$west = $this->canConnect($this->getSide(Facing::WEST));

		$inset = 0.25;
		if (
			$this->getSide(Facing::UP)->getId() === Block::AIR && //if there is a block on top, it stays as a post
			(
				($north && $south && !$west && !$east) ||
				(!$north && !$south && $west && $east)
			)
		) {
			//If connected to two sides on the same axis but not any others, AND there is not a block on top, there is no post and the wall is thinner
			$inset = 0.3125;
		}

		return new AxisAlignedBB(
			$this->x + ($west ? 0 : $inset),
			$this->y,
			$this->z + ($north ? 0 : $inset),
			$this->x + 1 - ($east ? 0 : $inset),
			$this->y + 1.5,
			$this->z + 1 - ($south ? 0 : $inset)
		);
	}

	public function canConnect(Block $block) : bool{
		return $block instanceof Wall || $block instanceof FenceGate || $block instanceof Thin || ($block->isSolid() && !$block->isTransparent());
	}
}
