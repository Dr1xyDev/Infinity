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

class ActorFallPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::ACTOR_FALL_PACKET;

	public int $entityRuntimeId;
	public float $fallDistance;
	public bool $isInVoid;

	/**
	 * @generate-create-func
	 */
	public static function create(int $entityRuntimeId, float $fallDistance, bool $isInVoid) : self
	{
		$result = new self();
		$result->entityRuntimeId = $entityRuntimeId;
		$result->fallDistance = $fallDistance;
		$result->isInVoid = $isInVoid;
		return $result;
	}

	protected function decodePayload() : void
	{
		$this->entityRuntimeId = $this->getEntityRuntimeId();
		$this->fallDistance = $this->getLFloat();
		$this->isInVoid = $this->getBool();
	}

	protected function encodePayload() : void
	{
		$this->putEntityRuntimeId($this->entityRuntimeId);
		$this->putLFloat($this->fallDistance);
		$this->putBool($this->isInVoid);
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleActorFall($this);
	}
}
