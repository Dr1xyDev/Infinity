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
use pocketmine\level\generator\vanilla\level\sound\FireExtinguishSound;
use pocketmine\level\generator\vanilla\level\sound\FlintSteelSound;
use pocketmine\level\generator\vanilla\math\AxisAlignedBB;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;

class CandleCake extends Transparent
{
	public const int LIT_BIT = 0x01;

	protected int $candleId = BlockIds::CANDLE;

	protected $itemId = Item::CAKE;

	public function __construct(int $id, int $meta = 0, ?string $name = null, ?int $itemId = null, int $candleId = BlockIds::CANDLE)
	{
		parent::__construct($id, $meta, $name, $itemId);
		$this->candleId = $candleId;
	}

	public function getHardness() : float
	{
		return 0.5;
	}

	public function getCandle() : Block
	{
		return BlockFactory::get($this->candleId);
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB
	{

		$f = $this->getDamage() * 0.125; //1 slice width

		return new AxisAlignedBB(
			$this->x + 0.0625 + $f,
			$this->y,
			$this->z + 0.0625,
			$this->x + 1 - 0.0625,
			$this->y + 0.5,
			$this->z + 1 - 0.0625
		);
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		$down = $this->getSide(Facing::DOWN);
		if ($down->getId() !== self::AIR) {
			$this->getLevel()->setBlock($blockReplace, $this, true, true);

			return true;
		}

		return false;
	}

	public function onActivate(Item $item, ?Player $player = null) : bool
	{
		if ($this->isLit() && $item->getId() != ItemIds::FLINT_AND_STEEL) {
			$this->meta = 0;
			$this->level->addSound(new FireExtinguishSound($this));
			$this->level->setBlock($this, $this, true, true);
			return true;
		} elseif (!$this->isLit() && $item->getId() == ItemIds::FLINT_AND_STEEL) {
			$this->meta = self::LIT_BIT;
			$this->level->addSound(new FlintSteelSound($this));
			$this->level->setBlock($this, $this, true, true);
			return true;
		} elseif ($player != null && ($player->getFood() < $player->getMaxFood() || $player->isCreative() || $player->getServer()->getDifficulty() == 0)) {
			$this->level->setBlock($this, BlockFactory::get(BlockIds::CAKE_BLOCK), true, true);
			$this->level->dropItem($this->add(0.5, 0.5, 0.5), $this->getCandle()->asItem());
			return $this->level->getBlock($this)->onActivate(ItemFactory::air(), $player);
		}

		return false;
	}

	public function onNearbyBlockChange() : void
	{
		if ($this->getSide(Facing::DOWN)->getId() === self::AIR) { //Replace with common break method
			$this->getLevel()->setBlock($this, BlockFactory::get(Block::AIR), true);
		}
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		return [$this->getCandle()->asItem()];
	}

	public function isAffectedBySilkTouch() : bool
	{
		return false;
	}

	public function getVariantBitmask() : int
	{
		return 0;
	}

	public function isLit() : bool
	{
		return ($this->meta & self::LIT_BIT) !== 0;
	}
}
