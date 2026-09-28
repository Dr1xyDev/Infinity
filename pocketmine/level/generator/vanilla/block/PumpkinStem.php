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
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\item\ItemIds;

class PumpkinStem extends Stem {
	protected $id = self::PUMPKIN_STEM;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Pumpkin Stem";
	}

	public function getSeed() : Item {
		return ItemFactory::get(ItemIds::PUMPKIN_SEEDS);
	}

	public function getCrop() : Item {
		return ItemFactory::get(ItemIds::PUMPKIN);
	}
}
