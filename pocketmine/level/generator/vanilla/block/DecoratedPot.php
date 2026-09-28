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
use pocketmine\level\generator\vanilla\math\AxisAlignedBB;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\tile\DecoratedPot as TileDecoratedPot;
use pocketmine\level\generator\vanilla\tile\Tile;

class DecoratedPot extends Transparent
{
	protected $id = self::DECORATED_POT;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Decorated Pot";
	}

	public function getHardness() : float
	{
		return 0;
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB
	{

		return new AxisAlignedBB(
			$this->x + 0.05,
			$this->y,
			$this->z + 0.05,
			$this->x + 0.95,
			$this->y,
			$this->z + 0.95
		);
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		$faces = [
			0 => 1,
			1 => 2,
			2 => 3,
			3 => 0
		];

		$this->meta = $faces[$player instanceof Player ? $player->getDirection() : 0];

		$this->getLevel()->setBlock($blockReplace, $this, true, true);

		Tile::createTile(Tile::DECORATED_POT, $this->getLevel(), TileDecoratedPot::createNBT($this, $face, $item, $player));

		return true;
	}
}
