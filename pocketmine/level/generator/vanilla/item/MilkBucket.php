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

use pocketmine\level\generator\vanilla\entity\Living;
use pocketmine\Player;

class MilkBucket extends Item implements MaybeConsumable
{
	public function getMaxStackSize() : int
	{
		return 1;
	}

	public function getResidue()
	{
		return ItemFactory::get(Item::BUCKET);
	}

	public function getAdditionalEffects() : array
	{
		return [];
	}

	public function canBeConsumed() : bool
	{
		return true;
	}

	public function onConsume(Living $consumer)
	{
		$consumer->removeAllEffects();
	}

	public function canStartUsingItem(Player $player) : bool
	{
		return true;
	}
}
