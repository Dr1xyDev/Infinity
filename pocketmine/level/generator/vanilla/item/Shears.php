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

namespace pocketmine\level\generator\vanilla\item;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\BlockToolType;

class Shears extends Tool
{
	public function __construct(int $meta = 0)
	{
		parent::__construct(self::SHEARS, $meta, "Shears");
	}

	public function getMaxDurability() : int
	{
		return 239;
	}

	public function getBlockToolType() : int
	{
		return BlockToolType::TYPE_SHEARS;
	}

	public function getBlockToolHarvestLevel() : int
	{
		return 1;
	}

	protected function getBaseMiningEfficiency() : float
	{
		return 15;
	}

	public function onDestroyBlock(Block $block) : bool
	{
		if ($block->getHardness() === 0.0 || $block->isCompatibleWithTool($this)) {
			return $this->applyDamage(1);
		}
		return false;
	}
}
