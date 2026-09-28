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
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;

class Vine extends Flowable
{
	public const FLAG_SOUTH = 0x01;
	public const FLAG_WEST = 0x02;
	public const FLAG_NORTH = 0x04;
	public const FLAG_EAST = 0x08;

	protected $id = self::VINE;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Vines";
	}

	public function getHardness() : float
	{
		return 0.2;
	}

	public function canPassThrough() : bool
	{
		return true;
	}

	public function hasEntityCollision() : bool
	{
		return true;
	}

	public function canClimb() : bool
	{
		return true;
	}

	public function canBeReplaced() : bool
	{
		return true;
	}

	public function onEntityCollide(Entity $entity) : void
	{
		$entity->resetFallDistance();
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		if (!$blockClicked->isSolid() || $face === Facing::UP || $face === Facing::DOWN) {
			return false;
		}

		$faces = [
			Facing::NORTH => self::FLAG_SOUTH,
			Facing::SOUTH => self::FLAG_NORTH,
			Facing::WEST => self::FLAG_EAST,
			Facing::EAST => self::FLAG_WEST
		];

		$this->meta = $faces[$face] ?? 0;
		if ($blockReplace->getId() === $this->getId()) {
			$this->meta |= $blockReplace->meta;
		}

		$this->getLevel()->setBlock($blockReplace, $this, true, true);
		return true;
	}

	public function onNearbyBlockChange() : void
	{
		$sides = [
			self::FLAG_SOUTH => Facing::SOUTH,
			self::FLAG_WEST => Facing::WEST,
			self::FLAG_NORTH => Facing::NORTH,
			self::FLAG_EAST => Facing::EAST
		];

		$meta = $this->meta;
		$up = $this->getSide(Facing::UP);
		foreach ($sides as $flag => $side) {
			if (!$this->getSide($side)->isSolid() && (!($up instanceof Vine) || ($up->getDamage() & $flag) != $flag)) {
				$meta &= ~$flag;
			}
		}

		if ($meta == 0 && !$up->isSolid()) {
			$this->level->useBreakOn($this);
		}

		if ($meta != $this->getDamage()) {
			$this->meta = $meta;
			$this->level->setBlock($this, $this);
		}
	}

	public function getVariantBitmask() : int
	{
		return 0;
	}

	public function getDrops(Item $item) : array
	{
		if (($item->getBlockToolType() & BlockToolType::TYPE_SHEARS) !== 0) {
			return $this->getDropsForCompatibleTool($item);
		}

		return [];
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_AXE;
	}

	public function getFlameEncouragement() : int
	{
		return 15;
	}

	public function getFlammability() : int
	{
		return 100;
	}
}
