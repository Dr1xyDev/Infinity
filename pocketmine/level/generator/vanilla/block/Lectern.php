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
use pocketmine\level\generator\vanilla\item\WritableBook;
use pocketmine\level\generator\vanilla\math\AxisAlignedBB;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\tile\Lectern as TileLectern;
use pocketmine\level\generator\vanilla\tile\Tile;

/**
 * Meta: bits 0-1 direction (0 south, 1 west, 2 north, 3 east), bit 2 powered
 */
class Lectern extends Transparent
{
	protected $id = self::LECTERN;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Lectern";
	}

	public function getHardness() : float
	{
		return 2.5;
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_AXE;
	}

	public function getVariantBitmask() : int
	{
		return 0;
	}

	public function getFuelTime() : int
	{
		return 300;
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB
	{
		return new AxisAlignedBB($this->x, $this->y, $this->z, $this->x + 1, $this->y + 0.9, $this->z + 1);
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		//the reading side faces the player
		$this->meta = $player !== null ? ($player->getDirection() + 2) & 0x03 : 0;
		$this->getLevel()->setBlock($blockReplace, $this, true, true);
		Tile::createTile(Tile::LECTERN, $this->getLevel(), TileLectern::createNBT($this, $face, $item, $player));

		return true;
	}

	private function getTile() : ?TileLectern
	{
		$tile = $this->getLevel()->getTile($this);
		if ($tile instanceof TileLectern) {
			return $tile;
		}
		//lecterns placed by older versions of the core have no tile
		$tile = Tile::createTile(Tile::LECTERN, $this->getLevel(), TileLectern::createNBT($this));
		return $tile instanceof TileLectern ? $tile : null;
	}

	public function onActivate(Item $item, ?Player $player = null) : bool
	{
		$tile = $this->getTile();
		if ($tile !== null && !$tile->hasBook() && $item instanceof WritableBook) {
			$tile->setBook($item);
			if ($player === null || $player->hasFiniteResources()) {
				$item->pop();
			}
		}
		//with a book on it the client opens the book screen by itself
		return true;
	}

	/**
	 * Punching a lectern takes its book off
	 */
	public function onAttack(Item $item, int $face, ?Player $player = null) : bool
	{
		$tile = $this->getLevel()->getTile($this);
		if ($tile instanceof TileLectern && $tile->hasBook()) {
			$tile->dropBook();
			return true;
		}
		return false;
	}

	public function onBreak(Item $item, ?Player $player = null) : bool
	{
		$tile = $this->getLevel()->getTile($this);
		if ($tile instanceof TileLectern) {
			$tile->dropBook();
		}

		return parent::onBreak($item, $player);
	}
}
