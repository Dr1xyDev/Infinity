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
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\math\AxisAlignedBB;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;

class Ladder extends Transparent
{
	protected $id = self::LADDER;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Ladder";
	}

	public function hasEntityCollision() : bool
	{
		return true;
	}

	public function isSolid() : bool
	{
		return false;
	}

	public function getHardness() : float
	{
		return 0.4;
	}

	public function canClimb() : bool
	{
		return true;
	}

	public function onEntityCollide(Entity $entity) : void
	{
		if ($entity instanceof Living && $entity->asVector3()->floor()->distanceSquared($this) < 1) { //entity coordinates must be inside block
			$entity->resetFallDistance();
			$entity->onGround = true;
		}
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB
	{
		$f = 0.1875;

		$minX = $minZ = 0;
		$maxX = $maxZ = 1;

		if ($this->meta === 2) {
			$minZ = 1 - $f;
		} elseif ($this->meta === 3) {
			$maxZ = $f;
		} elseif ($this->meta === 4) {
			$minX = 1 - $f;
		} elseif ($this->meta === 5) {
			$maxX = $f;
		}

		return new AxisAlignedBB(
			$this->x + $minX,
			$this->y,
			$this->z + $minZ,
			$this->x + $maxX,
			$this->y + 1,
			$this->z + $maxZ
		);
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		if (!$blockClicked->isTransparent()) {
			$faces = [
				2 => 2,
				3 => 3,
				4 => 4,
				5 => 5
			];
			if (isset($faces[$face])) {
				$this->meta = $faces[$face];
				$this->getLevel()->setBlock($blockReplace, $this, true, true);

				return true;
			}
		}

		return false;
	}

	public function onNearbyBlockChange() : void
	{
		if (!$this->getSide($this->meta ^ 0x01)->isSolid()) { //Replace with common break method
			$this->level->useBreakOn($this);
		}
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_AXE;
	}

	public function getVariantBitmask() : int
	{
		return 0;
	}
}
