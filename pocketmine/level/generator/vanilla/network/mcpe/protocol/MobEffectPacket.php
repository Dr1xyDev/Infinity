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

use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;

class MobEffectPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::MOB_EFFECT_PACKET;

	public const int EVENT_ADD = 1;
	public const int EVENT_MODIFY = 2;
	public const int EVENT_REMOVE = 3;

	public int $entityRuntimeId;
	public int $eventId;
	public int $effectId;
	public int $amplifier = 0;
	public bool $particles = true;
	public int $duration = 0;
	public int $tick = 0;
	public bool $ambient = true;

	protected function decodePayload() : void{
		$this->entityRuntimeId = $this->getEntityRuntimeId();
		$this->eventId = $this->getByte();
		$this->effectId = $this->getVarInt();
		$this->amplifier = $this->getVarInt();
		$this->particles = $this->getBool();
		$this->duration = $this->getVarInt();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_662) {
			if ($this->protocol >= ProtocolInfo::PROTOCOL_748) {
				$this->tick = $this->getUnsignedVarLong();
				if ($this->protocol >= ProtocolInfo::PROTOCOL_897) {
					$this->ambient = $this->getBool();
				}
			} else {
				$this->tick = $this->getLLong();
			}
		}
	}

	protected function encodePayload() : void{
		$this->putEntityRuntimeId($this->entityRuntimeId);
		$this->putByte($this->eventId);
		$this->putVarInt($this->effectId);
		$this->putVarInt($this->amplifier);
		$this->putBool($this->particles);
		$this->putVarInt($this->duration);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_662) {
			if ($this->protocol >= ProtocolInfo::PROTOCOL_748) {
				$this->putUnsignedVarLong($this->tick);
				if ($this->protocol >= ProtocolInfo::PROTOCOL_897) {
					$this->putBool($this->ambient);
				}
			} else {
				$this->putLLong($this->tick);
			}
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleMobEffect($this);
	}
}
