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

class AgentAnimationPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::AGENT_ANIMATION_PACKET;

	public const TYPE_ARM_SWING = 0;
	public const TYPE_SHRUG = 1;

	private int $animationType;
	private int $actorRuntimeId;

	/**
	 * @generate-create-func
	 */
	public static function create(int $animationType, int $actorRuntimeId) : self
	{
		$result = new self();
		$result->animationType = $animationType;
		$result->actorRuntimeId = $actorRuntimeId;
		return $result;
	}

	public function getAnimationType() : int
	{
		return $this->animationType;
	}

	public function getActorRuntimeId() : int
	{
		return $this->actorRuntimeId;
	}

	protected function decodePayload() : void
	{
		$this->animationType = $this->getByte();
		$this->actorRuntimeId = $this->getEntityRuntimeId();
	}

	protected function encodePayload() : void
	{
		$this->putByte($this->animationType);
		$this->putEntityRuntimeId($this->actorRuntimeId);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleAgentAnimation($this);
	}
}
