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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol;

use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\DimensionIds;

class SpawnParticleEffectPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::SPAWN_PARTICLE_EFFECT_PACKET;

	/** @var int */
	public $dimensionId = DimensionIds::OVERWORLD; //wtf mojang
	/** @var int */
	public $entityUniqueId = -1; //default none
	/** @var Vector3 */
	public $position;
	/** @var string */
	public $particleName;
	/** @var string */
	public $molangVariablesJson = "";

	protected function decodePayload() : void
	{
		$this->dimensionId = $this->getByte();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->entityUniqueId = $this->getEntityUniqueId();
		}
		$this->position = $this->getVector3();
		$this->particleName = $this->getString();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_503) {
			//optional since its introduction in 1.18.30, an empty string stands for "absent"
			$this->molangVariablesJson = $this->getOptional($this->getString(...)) ?? "";
		}
	}

	protected function encodePayload() : void
	{
		$this->putByte($this->dimensionId);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->putEntityUniqueId($this->entityUniqueId);
		}
		$this->putVector3($this->position);
		$this->putString($this->particleName);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_503) {
			$this->putOptional($this->molangVariablesJson !== "" ? $this->molangVariablesJson : null, $this->putString(...));
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleSpawnParticleEffect($this);
	}
}
