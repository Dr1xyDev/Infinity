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
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\item\ItemIds;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;

use function mt_rand;

class DoublePlant extends Flowable
{
	public const BITFLAG_TOP = 0x08;

	public const TYPE_SUNFLOWER = 0;
	public const TYPE_LILAC = 1;
	public const TYPE_DOUBLE_TALLGRASS = 2;
	public const TYPE_LARGE_FERN = 3;
	public const TYPE_ROSE_BUSH = 4;
	public const TYPE_PEONY = 5;

	protected $id = self::DOUBLE_PLANT;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function canBeReplaced() : bool
	{
		return $this->getVariant() === 2 || $this->getVariant() === 3; //grass or fern
	}

	public function getName() : string
	{
		static $names = [
			self::TYPE_SUNFLOWER => "Sunflower",
			self::TYPE_LILAC => "Lilac",
			self::TYPE_DOUBLE_TALLGRASS => "Double Tallgrass",
			self::TYPE_LARGE_FERN => "Large Fern",
			self::TYPE_ROSE_BUSH => "Rose Bush",
			self::TYPE_PEONY => "Peony"
		];
		return $names[$this->getVariant()] ?? "";
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if ($this->canBeSupportedAt($blockReplace)) {
			$this->getLevel()->setBlock($blockReplace, $this, false, false);
			$this->getLevel()->setBlock($blockReplace->getSide(Facing::UP), BlockFactory::get($this->id, $this->meta | self::BITFLAG_TOP), false, false);

			return true;
		}

		return false;
	}

	protected function canBeSupportedAt(Block $block) : bool{
		$supportBlock = $block->getSide(Facing::DOWN);
		return
			(
				$supportBlock instanceof Grass ||
				$supportBlock instanceof Dirt ||
				$supportBlock instanceof Mycelium ||
				$supportBlock instanceof Podzol ||
				$supportBlock instanceof Farmland ||
				$supportBlock instanceof Mud
			) && $block->getSide(Facing::UP)->canBeReplaced();
	}

	/**
	 * Returns whether this double-plant has a corresponding other half.
	 */
	public function isValidHalfPlant() : bool
	{
		if (($this->meta & self::BITFLAG_TOP) !== 0) {
			$other = $this->getSide(Facing::DOWN);
		} else {
			$other = $this->getSide(Facing::UP);
		}

		return (
			$other->getId() === $this->getId() &&
			$other->isSameType($this) &&
			($other->getDamage() & self::BITFLAG_TOP) !== ($this->getDamage() & self::BITFLAG_TOP)
		);
	}

	public function onNearbyBlockChange() : void
	{
		if (!$this->isValidHalfPlant() || (($this->meta & self::BITFLAG_TOP) === 0 && !$this->canBeSupportedAt($this))) {
			$this->getLevel()->useBreakOn($this);
		}
	}

	public function getVariantBitmask() : int
	{
		return 0x07;
	}

	public function getToolType() : int
	{
		return ($this->getVariant() === self::TYPE_DOUBLE_TALLGRASS || $this->getVariant() === self::TYPE_LARGE_FERN) ? BlockToolType::TYPE_SHEARS : BlockToolType::TYPE_NONE;
	}

	public function getToolHarvestLevel() : int
	{
		return ($this->getVariant() === self::TYPE_DOUBLE_TALLGRASS || $this->getVariant() === self::TYPE_LARGE_FERN) ? 1 : 0; //only grass or fern require shears
	}

	public function getDrops(Item $item) : array
	{
		if (($this->meta & self::BITFLAG_TOP) !== 0) {
			if ($this->isCompatibleWithTool($item)) {
				return parent::getDrops($item);
			}

			if (mt_rand(0, 24) === 0) {
				return [
					ItemFactory::get(ItemIds::SEEDS)
				];
			}
		}

		return [];
	}

	public function getAffectedBlocks() : array
	{
		if ($this->isValidHalfPlant()) {
			return [$this, $this->getSide(($this->meta & self::BITFLAG_TOP) !== 0 ? Facing::DOWN : Facing::UP)];
		}

		return parent::getAffectedBlocks();
	}

	public function getFlameEncouragement() : int
	{
		return 60;
	}

	public function getFlammability() : int
	{
		return 100;
	}
}
