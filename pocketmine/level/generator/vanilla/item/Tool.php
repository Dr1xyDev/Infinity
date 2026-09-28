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
use pocketmine\level\generator\vanilla\item\enchantment\Enchantment;

abstract class Tool extends Durable
{
	public function getMaxStackSize() : int
	{
		return 1;
	}

	public function getMiningEfficiency(Block $block) : float
	{
		$efficiency = 1;
		if (($block->getToolType() & $this->getBlockToolType()) !== 0) {
			$efficiency = $this->getBaseMiningEfficiency();
			if (($enchantmentLevel = $this->getEnchantmentLevel(Enchantment::EFFICIENCY)) > 0) {
				$efficiency += ($enchantmentLevel ** 2 + 1);
			}
		}

		return $efficiency;
	}

	protected function getBaseMiningEfficiency() : float
	{
		return 1;
	}
}
