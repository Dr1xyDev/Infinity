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

namespace pocketmine\level\generator\vanilla\level\sound;

use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AddActorPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;

final class EntityLongFallSound extends Sound
{
	public function __construct(
		Vector3 $pos,
		private Entity $entity
	) {
		parent::__construct($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());
	}

	public function encode()
	{
		return [LevelSoundEventPacket::create(
			LevelSoundEventPacket::SOUND_FALL_BIG,
			$this,
			-1,
			($this->entity instanceof Player ?
				"minecraft:player" :
				(AddActorPacket::LEGACY_ID_MAP_BC[$this->entity::NETWORK_ID] ?? ":")), //TODO: bad hack, stuff depends on players having a -1 network ID :(
			false, //TODO: is isBaby relevant here?
			false,
			$this->entity->getId(),
			null
		)];
	}
}
