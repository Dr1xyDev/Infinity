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
use pocketmine\Player;

class GlowingRedstoneOre extends RedstoneOre
{
	protected $id = self::GLOWING_REDSTONE_ORE;

	protected $itemId = self::REDSTONE_ORE;

	public function getName() : string
	{
		return "Glowing Redstone Ore";
	}

	public function getLightLevel() : int
	{
		return 9;
	}

	public function onActivate(Item $item, ?Player $player = null) : bool
	{
		return false;
	}

	public function onNearbyBlockChange() : void
	{

	}

	public function ticksRandomly() : bool
	{
		return true;
	}

	public function onRandomTick() : void
	{
		$this->getLevel()->setBlock($this, BlockFactory::get(Block::REDSTONE_ORE, $this->meta), false, false);
	}
}
