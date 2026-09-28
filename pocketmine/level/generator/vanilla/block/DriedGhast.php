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
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;

class DriedGhast extends Solid
{
	protected $id = self::DRIED_GHAST;

	public const STAGE_0 = 0;
	public const STAGE_1 = 4;
	public const STAGE_2 = 8;
	public const STAGE_3 = 12;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getHardness() : float
	{
		return 0;
	}

	public function getName() : string
	{
		return "Dried Ghast";
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		$faces = [
			0 => 1,
			1 => 2,
			2 => 3,
			3 => 0
		];
		$this->meta |= $player !== null ? $faces[$player->getDirection()] & 0x03 : 0;
		$this->getLevel()->setBlock($blockReplace, $this, true, true);
		return true;
	}

	public function getVariantBitmask() : int
	{
		return 0;
	}
}
