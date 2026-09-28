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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\MovementEffectType;

class MovementEffectPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::MOVEMENT_EFFECT_PACKET;

	private int $actorRuntimeId;
	private MovementEffectType $effectType;
	private int $duration;
	private int $tick;

	/**
	 * @generate-create-func
	 */
	public static function create(int $actorRuntimeId, MovementEffectType $effectType, int $duration, int $tick) : self
	{
		$result = new self();
		$result->actorRuntimeId = $actorRuntimeId;
		$result->effectType = $effectType;
		$result->duration = $duration;
		$result->tick = $tick;
		return $result;
	}

	public function getActorRuntimeId() : int
	{
		return $this->actorRuntimeId;
	}

	public function getEffectType() : MovementEffectType
	{
		return $this->effectType;
	}

	public function getDuration() : int
	{
		return $this->duration;
	}

	public function getTick() : int
	{
		return $this->tick;
	}

	protected function decodePayload() : void
	{
		$this->actorRuntimeId = $this->getEntityRuntimeId();
		$this->effectType = MovementEffectType::fromPacket($this->getUnsignedVarInt());
		$this->duration = $this->getUnsignedVarInt();
		$this->tick = $this->getUnsignedVarLong();
	}

	protected function encodePayload() : void
	{
		$this->putEntityRuntimeId($this->actorRuntimeId);
		$this->putUnsignedVarInt($this->effectType->value);
		$this->putUnsignedVarInt($this->duration);
		$this->putUnsignedVarLong($this->tick);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleMovementEffect($this);
	}
}
