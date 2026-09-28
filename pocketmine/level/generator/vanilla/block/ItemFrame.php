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

use pocketmine\level\generator\vanilla\event\block\ItemFrameDropItemEvent;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\level\sound\ItemFrameAddItemSound;
use pocketmine\level\generator\vanilla\level\sound\ItemFrameRemoveItemSound;
use pocketmine\level\generator\vanilla\level\sound\ItemFrameRotateItemSound;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\tile\ItemFrame as TileItemFrame;
use pocketmine\level\generator\vanilla\tile\Tile;
use pocketmine\level\generator\vanilla\utils\Utils;

class ItemFrame extends Flowable
{
	protected $id = Block::ITEM_FRAME_BLOCK;

	protected $itemId = Item::ITEM_FRAME;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Item Frame";
	}

	public function onActivate(Item $item, ?Player $player = null) : bool
	{
		$tile = $this->level->getTile($this);
		if (!($tile instanceof TileItemFrame)) {
			$tile = Tile::createTile(Tile::ITEM_FRAME, $this->getLevel(), TileItemFrame::createNBT($this));
			if (!($tile instanceof TileItemFrame)) {
				return true;
			}
		}

		if ($tile->hasItem()) {
			$tile->setItemRotation(($tile->getItemRotation() + 1) % 8);

			$this->level->addSound(new ItemFrameRotateItemSound($this));
		} elseif (!$item->isNull()) {
			$tile->setItem($item->pop());

			$this->level->addSound(new ItemFrameAddItemSound($this));
		}

		return true;
	}

	public function onNearbyBlockChange() : void
	{
		$sides = [
			0 => Facing::WEST,
			1 => Facing::EAST,
			2 => Facing::NORTH,
			3 => Facing::SOUTH
		];
		if (isset($sides[$this->meta]) && !$this->getSide($sides[$this->meta])->isSolid()) {
			$this->level->useBreakOn($this);
		}
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		if ($face === Facing::DOWN || $face === Facing::UP || !$blockClicked->isSolid()) {
			return false;
		}

		$faces = [
			Facing::NORTH => 3,
			Facing::SOUTH => 2,
			Facing::WEST => 1,
			Facing::EAST => 0
		];

		$this->meta = $faces[$face];
		$this->level->setBlock($blockReplace, $this);

		Tile::createTile(Tile::ITEM_FRAME, $this->getLevel(), TileItemFrame::createNBT($this, $face, $item, $player));

		return true;
	}

	public function onAttack(Item $item, int $face, ?Player $player = null) : bool
	{
		$tile = $this->level->getTile($this);
		if (!($tile instanceof TileItemFrame)) {
			$tile = Tile::createTile(Tile::ITEM_FRAME, $this->getLevel(), TileItemFrame::createNBT($this));
			if (!($tile instanceof TileItemFrame)) {
				return false;
			}
		}

		$ev = new ItemFrameDropItemEvent($player, $this, $tile, $tile->getItem());
		$ev->call();
		if ($player->isSpectator() || $ev->isCancelled()) {
			$tile->spawnTo($player);
			return false;
		}

		$level = $this->getLevel();
		if (Utils::getRandomFloat() <= $tile->getItemDropChance()) {
			$level->dropItem($this->add(0.5, 0.5, 0.5), clone $tile->getItem());
			$this->level->addSound(new ItemFrameRemoveItemSound($this));
		}
		$tile->setItem(null);
		$tile->setItemRotation(0);
		return true;
	}

	public function getVariantBitmask() : int
	{
		return 0;
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		$drops = parent::getDropsForCompatibleTool($item);

		$tile = $this->level->getTile($this);
		if ($tile instanceof TileItemFrame) {
			$tileItem = $tile->getItem();
			if (Utils::getRandomFloat() <= $tile->getItemDropChance() && !$tileItem->isNull()) {
				$drops[] = $tileItem;
			}
		}

		return $drops;
	}

	public function isAffectedBySilkTouch() : bool
	{
		return false;
	}

	public function getHardness() : float
	{
		return 0.25;
	}

	public function getPickedItem(bool $addUserData = false) : Item
	{
		$itemOnFrame = null;

		$tile = $this->getLevel()->getTile($this);
		if ($tile instanceof TileItemFrame) {
			$tileItem = $tile->getItem();
			if (Utils::getRandomFloat() <= $tile->getItemDropChance() && !$tileItem->isNull()) {
				$itemOnFrame = $tileItem;
			}
		}

		return $itemOnFrame !== null ? $itemOnFrame : parent::getPickedItem($addUserData);
	}
}
