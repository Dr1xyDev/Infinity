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
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\tile\Beacon as TileBeacon;
use pocketmine\level\generator\vanilla\tile\Tile;

class Beacon extends Transparent
{
	protected $id = self::BEACON;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Beacon";
	}

	public function getLightLevel() : int
	{
		return 15;
	}

	public function getHardness() : float
	{
		return 3;
	}

	public function getBreakTime(Item $item) : float
	{
		return 4.5;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		$this->getLevel()->setBlock($blockReplace, $this, true, true);

		Tile::createTile(Tile::BEACON, $this->getLevel(), TileBeacon::createNBT($this, $face, $item, $player));

		return true;
	}

	public function onActivate(Item $item, ?Player $player = null) : bool
	{
		if ($player instanceof Player) {
			$tile = $this->level->getTile($this);
			if ($tile instanceof TileBeacon) {
				$top = $this->getSide(Facing::UP);
				if ($top->isTransparent() !== true) {
					return true;
				}

				$player->addWindow($tile->getInventory());
			}
		}

		return true;
	}
}
