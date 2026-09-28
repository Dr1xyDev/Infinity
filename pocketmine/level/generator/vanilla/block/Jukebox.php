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
use pocketmine\level\generator\vanilla\item\Record;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\tile\Jukebox as TileJukebox;
use pocketmine\level\generator\vanilla\tile\Tile;

class Jukebox extends Solid
{
	protected $id = self::JUKEBOX;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Jukebox";
	}

	public function getHardness() : float
	{
		return 2.0;
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_AXE;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		$this->getLevel()->setBlock($blockReplace, $this, true, true);

		Tile::createTile(Tile::JUKEBOX, $this->getLevel(), TileJukebox::createNBT($this, $face, $item, $player));

		return true;
	}

	public function onActivate(Item $item, ?Player $player = null) : bool
	{
		if ($player instanceof Player) {
			$jb = $this->getLevel()->getTile($this);
			if ($jb instanceof TileJukebox) {
				if ($jb->getRecordItem() == null) {
					if ($item instanceof Record) {
						$this->level->setBlock($this, $this);

						$jb->setRecordItem($item);
						$jb->playDisc($player);
						$player->getInventory()->removeItem($item);
					}
				} else {
					$jb->dropDisc();
				}
			}
		}

		$this->level->setBlock($this, $this);
		return true;
	}

	public function onBreak(Item $item, ?Player $player = null) : bool
	{
		$tile = $this->getLevel()->getTile($this);
		if ($tile instanceof TileJukebox) {
			$tile->dropDisc();
		}

		return parent::onBreak($item, $player);
	}
}
