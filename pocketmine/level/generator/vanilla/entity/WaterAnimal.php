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

namespace pocketmine\level\generator\vanilla\entity;

use pocketmine\level\generator\vanilla\event\entity\EntityDamageEvent;

abstract class WaterAnimal extends Mob implements Ageable
{
	public const SPAWN_PLACEMENT_TYPE = SpawnPlacementTypes::PLACEMENT_TYPE_IN_WATER;

	public function isBaby() : bool
	{
		return $this->getGenericFlag(self::DATA_FLAG_BABY);
	}

	public function canBreathe() : bool
	{
		return $this->isUnderwater();
	}

	public function canSpawnHere() : bool
	{
		return true;
	}

	public function onAirExpired() : void
	{
		$ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_SUFFOCATION, 2);
		$this->attack($ev);
	}
}
