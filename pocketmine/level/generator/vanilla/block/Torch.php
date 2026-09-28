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

class Torch extends Flowable
{
	protected $id = self::TORCH;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getLightLevel() : int
	{
		return 14;
	}

	public function getName() : string
	{
		return "Torch";
	}

	public function onNearbyBlockChange() : void
	{
		$below = $this->getSide(Facing::DOWN);
		$meta = $this->getDamage();
		static $faces = [
			0 => Facing::DOWN,
			1 => Facing::WEST,
			2 => Facing::EAST,
			3 => Facing::NORTH,
			4 => Facing::SOUTH,
			5 => Facing::DOWN
		];
		$face = $faces[$meta] ?? Facing::DOWN;

		if ($this->getSide($face)->isTransparent() && !($face === Facing::DOWN && ($below->getId() === self::FENCE || $below->getId() === self::COBBLESTONE_WALL))) {
			$this->getLevel()->useBreakOn($this);
		}
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		$below = $this->getSide(Facing::DOWN);

		if (!$blockClicked->isTransparent() && $face !== Facing::DOWN) {
			$faces = [
				Facing::UP => 5,
				Facing::NORTH => 4,
				Facing::SOUTH => 3,
				Facing::WEST => 2,
				Facing::EAST => 1
			];
			$this->meta = $faces[$face];
			$this->getLevel()->setBlock($blockReplace, $this, true, true);

			return true;
		} elseif (!$below->isTransparent() || $below->getId() === self::FENCE || $below->getId() === self::COBBLESTONE_WALL) {
			$this->meta = 0;
			$this->getLevel()->setBlock($blockReplace, $this, true, true);

			return true;
		}

		return false;
	}

	public function getVariantBitmask() : int
	{
		return 0;
	}
}
