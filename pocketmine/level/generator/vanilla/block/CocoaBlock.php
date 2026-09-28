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

use pocketmine\level\generator\vanilla\block\utils\BlockEventHelper;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\item\ItemIds;
use pocketmine\level\generator\vanilla\math\AxisAlignedBB;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\utils\Random;

use function in_array;
use function min;
use function mt_rand;

class CocoaBlock extends Flowable implements Growable
{
	private const MAX_AGE = 2;

	private const FACE_TO_META = [
		Facing::NORTH => 0,
		Facing::EAST => 1,
		Facing::SOUTH => 2,
		Facing::WEST => 3
	];

	private const META_TO_FACE = [
		0 => Facing::SOUTH,
		1 => Facing::WEST,
		2 => Facing::NORTH,
		3 => Facing::EAST
	];

	protected $id = self::COCOA_BLOCK;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Cocoa Block";
	}

	public function getHardness() : float
	{
		return 0.2;
	}

	public function getBlastResistance() : float
	{
		return 15;
	}

	public function canBeFlowedInto() : bool
	{
		return false;
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_AXE;
	}

	public function getVariantBitmask() : int
	{
		return 0;
	}

	public function isAffectedBySilkTouch() : bool
	{
		return false;
	}

	public function getAge() : int
	{
		return min(self::MAX_AGE, $this->meta >> 2);
	}

	public function setAge(int $age) : self
	{
		$this->meta = ($this->meta & 0x03) | (min(self::MAX_AGE, $age) << 2);
		return $this;
	}

	private function getSupportFace() : int
	{
		return self::META_TO_FACE[$this->meta & 0x03] ?? Facing::SOUTH;
	}

	private function getPodFace() : int
	{
		return Facing::opposite($this->getSupportFace());
	}

	private function isJungleSupport(Block $block) : bool
	{
		return match($block->getId()){
			BlockIds::LOG => ($block->getDamage() & 0x03) === Log::JUNGLE,
			BlockIds::WOOD => in_array($block->getDamage(), Wood::getAxisFaces(Wood::JUNGLE), true) || in_array($block->getDamage(), Wood::getAxisFaces(Wood::STRIPPED_JUNGLE), true),
			BlockIds::STRIPPED_JUNGLE_LOG => true,
			default => false
		};
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB
	{
		$age = $this->getAge();
		$small = $age === 0;

		$minX = 0.3125;
		$minY = 0.3125;
		$minZ = 0.3125;
		$maxX = 0.6875;
		$maxY = 0.75;
		$maxZ = 0.6875;

		switch($this->getPodFace()){
			case Facing::EAST:
				$minX = $small ? 0.6875 : 0.5625;
				$maxX = 0.9375;
				$minY = $small ? 0.4375 : 0.3125;
				$minZ = $small ? 0.375 : 0.3125;
				$maxZ = $small ? 0.625 : 0.6875;
				break;
			case Facing::SOUTH:
				$minX = $small ? 0.375 : 0.3125;
				$maxX = $small ? 0.625 : 0.6875;
				$minY = $small ? 0.4375 : 0.3125;
				$minZ = $small ? 0.6875 : 0.5625;
				$maxZ = 0.9375;
				break;
			case Facing::WEST:
				$minX = 0.0625;
				$maxX = $small ? 0.3125 : 0.4375;
				$minY = $small ? 0.4375 : 0.3125;
				$minZ = $small ? 0.375 : 0.3125;
				$maxZ = $small ? 0.625 : 0.6875;
				break;
			case Facing::NORTH:
			default:
				$minX = $small ? 0.375 : 0.3125;
				$maxX = $small ? 0.625 : 0.6875;
				$minY = $small ? 0.4375 : 0.3125;
				$minZ = 0.0625;
				$maxZ = $small ? 0.3125 : 0.4375;
				break;
		}

		return new AxisAlignedBB(
			$this->x + $minX,
			$this->y + $minY,
			$this->z + $minZ,
			$this->x + $maxX,
			$this->y + $maxY,
			$this->z + $maxZ
		);
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		$meta = self::FACE_TO_META[$face] ?? null;
		if($meta === null || !$this->isJungleSupport($blockClicked)){
			return false;
		}

		$this->meta = $meta;
		$this->getLevel()->setBlock($blockReplace, $this, true, true);
		return true;
	}

	public function onNearbyBlockChange() : void
	{
		if(!$this->isJungleSupport($this->getSide($this->getSupportFace()))){
			$this->level->useBreakOn($this);
		}
	}

	public function ticksRandomly() : bool
	{
		return $this->getAge() < self::MAX_AGE;
	}

	public function onRandomTick() : void
	{
		if($this->getAge() < self::MAX_AGE && mt_rand(1, 6) === 1){
			$newState = clone $this;
			$newState->setAge($this->getAge() + 1);
			BlockEventHelper::grow($this, $newState, null);
		}
	}

	public function canGrow(Random $random, ?Player $player) : bool
	{
		return $this->getAge() < self::MAX_AGE;
	}

	public function canUseBonemeal(Random $random, ?Player $player) : bool
	{
		return true;
	}

	public function grow(Random $random, ?Player $player) : void
	{
		$newState = clone $this;
		$newState->setAge($this->getAge() + 1);
		BlockEventHelper::grow($this, $newState, $player);
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		return [
			ItemFactory::get(ItemIds::DYE, 3, $this->getAge() === self::MAX_AGE ? mt_rand(2, 3) : 1)
		];
	}

	public function getPickedItem(bool $addUserData = false) : Item
	{
		return ItemFactory::get(ItemIds::DYE, 3);
	}
}
