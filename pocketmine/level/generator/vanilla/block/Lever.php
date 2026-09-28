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
use pocketmine\level\generator\vanilla\level\sound\RedstonePowerOffSound;
use pocketmine\level\generator\vanilla\level\sound\RedstonePowerOnSound;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;

class Lever extends Flowable
{
	protected $id = self::LEVER;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Lever";
	}

	public function getHardness() : float
	{
		return 0.5;
	}

	public function getVariantBitmask() : int
	{
		return 0;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		if (!$blockClicked->isSolid()) {
			return false;
		}

		if ($face === Facing::DOWN) {
			$this->meta = 0;
		} else {
			$this->meta = 6 - $face;
		}

		if ($player !== null) {
			if (($player->getDirection() & 0x01) === 0) {
				if ($face === Facing::UP) {
					$this->meta = 6;
				}
			} else {
				if ($face === Facing::DOWN) {
					$this->meta = 7;
				}
			}
		}

		$this->level->setBlock($blockReplace, $this, true, true);
		return true;
	}

	public function onNearbyBlockChange() : void
	{
		$faces = [
			0 => Facing::UP,
			1 => Facing::WEST,
			2 => Facing::EAST,
			3 => Facing::NORTH,
			4 => Facing::SOUTH,
			5 => Facing::DOWN,
			6 => Facing::DOWN,
			7 => Facing::UP
		];
		if (!$this->getSide($faces[$this->meta & 0x07])->isSolid()) {
			$this->level->useBreakOn($this);
		}
	}

	public function onActivate(Item $item, ?Player $player = null) : bool
	{
		$this->meta ^= 0x08;
		$this->getLevel()->setBlock($this, $this, true, false);

		$this->level->addSound(
			(($this->meta & 0x08) > 0) ?
				new RedstonePowerOnSound($this->add(0.5, 0.5, 0.5)) :
				new RedstonePowerOffSound($this->add(0.5, 0.5, 0.5))
		);
		return true;
	}
}
