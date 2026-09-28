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

use pocketmine\level\generator\vanilla\event\entity\EntityDamageEvent;
use pocketmine\level\generator\vanilla\event\entity\ProjectileHitEvent;
use pocketmine\level\generator\vanilla\level\particle\EndermanTeleportParticle;
use pocketmine\level\generator\vanilla\level\sound\EndermanTeleportSound;

class EnderPearl extends Throwable
{
	public const NETWORK_ID = self::ENDER_PEARL;

	protected function onHit(ProjectileHitEvent $event) : void
	{
		$owner = $this->getOwningEntity();
		if ($owner !== null) {
			//TODO: check end gateways (when they are added)
			//TODO: spawn endermites at origin

			$this->level->addParticle(new EndermanTeleportParticle($owner));
			$this->level->addSound(new EndermanTeleportSound($owner));
			$owner->teleport($target = $event->getRayTraceResult()->getHitVector());
			$this->level->addSound(new EndermanTeleportSound($target));

			$owner->attack(new EntityDamageEvent($owner, EntityDamageEvent::CAUSE_FALL, 5));
		}
	}
}
