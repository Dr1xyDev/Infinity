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

class DeepslateRedstoneOre extends RedstoneOre
{
	protected $id = self::DEEPSLATE_REDSTONE_ORE;

	public function getName() : string
	{
		return "Deepslate Redstone Ore";
	}

	public function getHardness() : float
	{
		return 4.5;
	}

	public function onActivate(Item $item, ?Player $player = null) : bool
	{
		$this->getLevel()->setBlock($this, BlockFactory::get(BlockIds::LIT_DEEPSLATE_REDSTONE_ORE, $this->meta));
		return false; //this shouldn't prevent block placement
	}

	public function onNearbyBlockChange() : void
	{
		$this->getLevel()->setBlock($this, BlockFactory::get(BlockIds::LIT_DEEPSLATE_REDSTONE_ORE, $this->meta));
	}
}
