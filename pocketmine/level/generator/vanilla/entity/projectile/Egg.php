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

namespace pocketmine\level\generator\vanilla\entity\projectile;

use pocketmine\level\generator\vanilla\event\entity\ProjectileHitEvent;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\level\particle\ItemBreakParticle;

class Egg extends Throwable
{
	public const NETWORK_ID = self::EGG;

	//TODO: spawn chickens on collision

	protected function onHit(ProjectileHitEvent $event) : void
	{
		for ($i = 0; $i < 6; ++$i) {
			$this->level->addParticle(new ItemBreakParticle($this, ItemFactory::get(Item::EGG)));
		}
	}
}
