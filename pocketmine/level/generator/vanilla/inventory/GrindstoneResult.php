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

namespace pocketmine\level\generator\vanilla\inventory;

use pocketmine\level\generator\vanilla\item\Item;

class GrindstoneResult {

	/**
	 * @param int $enchantmentValue sum of the minimum enchanting costs of the removed enchantments, which the dropped
	 *                              experience is rolled from
	 */
	public function __construct(
		private Item $output,
		private int $enchantmentValue
	){}

	public function getOutput() : Item{
		return $this->output;
	}

	public function getEnchantmentValue() : int{
		return $this->enchantmentValue;
	}
}
