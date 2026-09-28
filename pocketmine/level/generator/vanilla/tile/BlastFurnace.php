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

namespace pocketmine\level\generator\vanilla\tile;

use pocketmine\level\generator\vanilla\block\BlockFactory;
use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\inventory\FurnaceType;

class BlastFurnace extends Furnace
{
	public function getDefaultName() : string
	{
		return "Blast Furnace";
	}

	public function getFurnaceType() : FurnaceType
	{
		return FurnaceType::BLAST_FURNACE;
	}

	protected function onStartSmelting() : void
	{
		$block = $this->getBlock();
		if($block->getId() === BlockIds::BLAST_FURNACE){
			$this->getLevel()->setBlock($this, BlockFactory::get(BlockIds::LIT_BLAST_FURNACE, $block->getDamage()), true);
		}
	}

	protected function onStopSmelting() : void
	{
		$block = $this->getBlock();
		if($block->getId() === BlockIds::LIT_BLAST_FURNACE){
			$this->getLevel()->setBlock($this, BlockFactory::get(BlockIds::BLAST_FURNACE, $block->getDamage()), true);
		}
	}
}
