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

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\convert\block\BlockProtocolConvertor;
use pocketmine\level\generator\vanilla\network\mcpe\convert\block\RuntimeBlockMapping;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AddActorPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\Player;

final class EntityLandSound extends Sound
{
	public function __construct(
		Vector3 $pos,
		private Entity $entity,
		private Block $blockLandedOn
	) {
		parent::__construct($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());
	}

	public function encode()
	{
		$blockLandedOn = BlockProtocolConvertor::getInstance()->get($this->blockLandedOn, $this->protocol) ?? $this->blockLandedOn;
		return [LevelSoundEventPacket::create(
			LevelSoundEventPacket::SOUND_FALL,
			$this,
			($this->protocol >= ProtocolInfo::PROTOCOL_407 ?
				RuntimeBlockMapping::getInstance($this->protocol)->toRuntimeId($blockLandedOn->getFullId()) :
				$blockLandedOn->getId()),
			($this->entity instanceof Player ?
				"minecraft:player" :
				(AddActorPacket::LEGACY_ID_MAP_BC[$this->entity::NETWORK_ID] ?? ":")), //TODO: bad hack, stuff depends on players having a -1 network ID :(
			false, //TODO: is isBaby relevant here?
			false,
			$this->entity->getId(),
			null
		)];
	}

	public function isUseProtocol() : bool
	{
		return true;
	}
}
