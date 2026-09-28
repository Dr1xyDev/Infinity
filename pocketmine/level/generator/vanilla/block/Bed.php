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
use pocketmine\level\generator\vanilla\lang\TranslationContainer;
use pocketmine\level\generator\vanilla\level\Explosion;
use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\math\AxisAlignedBB;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\DimensionIds;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\tile\Bed as TileBed;
use pocketmine\level\generator\vanilla\tile\Tile;
use pocketmine\level\generator\vanilla\utils\TextFormat;

class Bed extends Transparent
{
	public const BITFLAG_OCCUPIED = 0x04;
	public const BITFLAG_HEAD = 0x08;

	protected $id = self::BED_BLOCK;

	protected $itemId = Item::BED;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getHardness() : float
	{
		return 0.2;
	}

	public function getName() : string
	{
		return "Bed Block";
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB
	{
		return new AxisAlignedBB(
			$this->x,
			$this->y,
			$this->z,
			$this->x + 1,
			$this->y + 0.5625,
			$this->z + 1
		);
	}

	public function isHeadPart() : bool
	{
		return ($this->meta & self::BITFLAG_HEAD) !== 0;
	}

	public function isOccupied() : bool
	{
		return ($this->meta & self::BITFLAG_OCCUPIED) !== 0;
	}

	/**
	 * @return void
	 */
	public function setOccupied(bool $occupied = true)
	{
		if ($occupied) {
			$this->meta |= self::BITFLAG_OCCUPIED;
		} else {
			$this->meta &= ~self::BITFLAG_OCCUPIED;
		}

		$this->getLevel()->setBlock($this, $this, false, false);

		if (($other = $this->getOtherHalf()) !== null && $other->isOccupied() !== $occupied) {
			$other->setOccupied($occupied);
		}
	}

	public static function getOtherHalfSide(int $meta, bool $isHead = false) : int
	{
		$rotation = $meta & 0x03;
		$side = -1;

		switch ($rotation) {
			case 0x00: //South
				$side = Facing::SOUTH;
				break;
			case 0x01: //West
				$side = Facing::WEST;
				break;
			case 0x02: //North
				$side = Facing::NORTH;
				break;
			case 0x03: //East
				$side = Facing::EAST;
				break;
		}

		if ($isHead) {
			$side = Facing::opposite($side);
		}

		return $side;
	}

	public function getOtherHalf() : ?Bed
	{
		$other = $this->getSide(self::getOtherHalfSide($this->meta, $this->isHeadPart()));
		if ($other instanceof Bed && $other->getId() === $this->getId() && $other->isHeadPart() !== $this->isHeadPart() && (($other->getDamage() & 0x03) === ($this->getDamage() & 0x03))) {
			return $other;
		}

		return null;
	}

	public function onActivate(Item $item, ?Player $player = null) : bool
	{
		$dimensionId = $this->getLevel()->getDimension();
		if ($dimensionId === DimensionIds::NETHER || $dimensionId === DimensionIds::THE_END) {
			$explosion = new Explosion($this, 6, $this);
			$explosion->explodeA();
			$explosion->explodeB();
			return true;
		}

		if ($player !== null) {
			$other = $this->getOtherHalf();
			if ($other === null) {
				$player->sendMessage(TextFormat::GRAY . "This bed is incomplete");

				return true;
			} elseif ($player->distanceSquared($this) > 4 && $player->distanceSquared($other) > 4) {
				$player->sendMessage(new TranslationContainer(TextFormat::GRAY . "%tile.bed.tooFar"));
				return true;
			}

			$time = $this->getLevel()->getTimeOfDay();

			$isNight = ($time >= Level::TIME_NIGHT && $time < Level::TIME_SUNRISE);

			if (!$isNight) {
				$player->sendMessage(new TranslationContainer(TextFormat::GRAY . "%tile.bed.noSleep"));

				return true;
			}

			$b = ($this->isHeadPart() ? $this : $other);

			if ($b->isOccupied()) {
				$player->sendMessage(new TranslationContainer(TextFormat::GRAY . "%tile.bed.occupied"));

				return true;
			}

			$player->sleepOn($b);
		}

		return true;

	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		$down = $this->getSide(Facing::DOWN);
		if (!$down->isTransparent()) {
			$meta = (($player instanceof Player ? $player->getDirection() : 0) - 1) & 0x03;
			$next = $this->getSide(self::getOtherHalfSide($meta));
			if ($next->canBeReplaced() && !$next->getSide(Facing::DOWN)->isTransparent()) {
				$this->getLevel()->setBlock($blockReplace, BlockFactory::get($this->id, $meta), true, true);
				$this->getLevel()->setBlock($next, BlockFactory::get($this->id, $meta | self::BITFLAG_HEAD), true, true);

				Tile::createTile(Tile::BED, $this->getLevel(), TileBed::createNBT($this, $face, $item, $player));
				Tile::createTile(Tile::BED, $this->getLevel(), TileBed::createNBT($next, $face, $item, $player));

				return true;
			}
		}

		return false;
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		if ($this->isHeadPart()) {
			return [$this->getItem()];
		}

		return [];
	}

	public function getPickedItem(bool $addUserData = false) : Item
	{
		return $this->getItem();
	}

	private function getItem() : Item
	{
		$tile = $this->getLevel()->getTile($this);
		if ($tile instanceof TileBed) {
			return ItemFactory::get($this->getItemId(), $tile->getColor());
		}

		return ItemFactory::get($this->getItemId(), 14); //Red
	}

	public function isAffectedBySilkTouch() : bool
	{
		return false;
	}

	public function getAffectedBlocks() : array
	{
		if (($other = $this->getOtherHalf()) !== null) {
			return [$this, $other];
		}

		return parent::getAffectedBlocks();
	}

	public function getBounceMotionMultiplier() : float
	{
		return 0.66;
	}

	public function getBounceFallDistanceMultiplier() : float
	{
		return 0.5;
	}
}
