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

use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\entity\Living;
use pocketmine\level\generator\vanilla\entity\object\LeashKnot;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\math\AxisAlignedBB;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;

use function count;

abstract class Fence extends Transparent
{
	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getThickness() : float
	{
		return 0.25;
	}

	public function isPassable() : bool
	{
		return false;
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB
	{
		$width = 0.5 - $this->getThickness() / 2;

		return new AxisAlignedBB(
			$this->x + ($this->canConnect($this->getSide(Facing::WEST)) ? 0 : $width),
			$this->y,
			$this->z + ($this->canConnect($this->getSide(Facing::NORTH)) ? 0 : $width),
			$this->x + 1 - ($this->canConnect($this->getSide(Facing::EAST)) ? 0 : $width),
			$this->y + 1.5,
			$this->z + 1 - ($this->canConnect($this->getSide(Facing::SOUTH)) ? 0 : $width)
		);
	}

	protected function recalculateCollisionBoxes() : array
	{
		$inset = 0.5 - $this->getThickness() / 2;

		/** @var AxisAlignedBB[] $bbs */
		$bbs = [];

		$connectWest = $this->canConnect($this->getSide(Facing::WEST));
		$connectEast = $this->canConnect($this->getSide(Facing::EAST));

		if ($connectWest || $connectEast) {
			//X axis (west/east)
			$bbs[] = new AxisAlignedBB(
				$this->x + ($connectWest ? 0 : $inset),
				$this->y,
				$this->z + $inset,
				$this->x + 1 - ($connectEast ? 0 : $inset),
				$this->y + 1.5,
				$this->z + 1 - $inset
			);
		}

		$connectNorth = $this->canConnect($this->getSide(Facing::NORTH));
		$connectSouth = $this->canConnect($this->getSide(Facing::SOUTH));

		if ($connectNorth || $connectSouth) {
			//Z axis (north/south)
			$bbs[] = new AxisAlignedBB(
				$this->x + $inset,
				$this->y,
				$this->z + ($connectNorth ? 0 : $inset),
				$this->x + 1 - $inset,
				$this->y + 1.5,
				$this->z + 1 - ($connectSouth ? 0 : $inset)
			);
		}

		if (count($bbs) === 0) {
			//centre post AABB (only needed if not connected on any axis - other BBs overlapping will do this if any connections are made)
			return [
				new AxisAlignedBB(
					$this->x + $inset,
					$this->y,
					$this->z + $inset,
					$this->x + 1 - $inset,
					$this->y + 1.5,
					$this->z + 1 - $inset
				)
			];
		}

		return $bbs;
	}

	/**
	 * @return bool
	 */
	public function canConnect(Block $block)
	{
		return $block instanceof static || $block instanceof FenceGate || ($block->isSolid() && !$block->isTransparent());
	}

	public function onActivate(Item $item, ?Player $player = null) : bool
	{
		if ($player !== null) {
			$knot = LeashKnot::getKnotFromPosition($player->level, $this);
			$f = 7.0;
			$flag = false;

			foreach ($player->level->getCollidingEntities(new AxisAlignedBB($this->x - $f, $this->y - $f, $this->z - $f, $this->x + $f, $this->y + $f, $this->z + $f)) as $entity) {
				if ($entity instanceof Living) {
					if ($entity->isLeashed() && $entity->getLeashedToEntity() === $player) {
						if ($knot === null) {
							$knot = new LeashKnot($player->level, Entity::createBaseNBT($this));
							$knot->spawnToAll();
						}

						$entity->setLeashedToEntity($knot, true);
						$flag = true;
					}
				}
			}

			if ($flag) {
				$player->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_LEASHKNOT_PLACE);
			}

			return $flag;
		}
		return false;
	}
}
