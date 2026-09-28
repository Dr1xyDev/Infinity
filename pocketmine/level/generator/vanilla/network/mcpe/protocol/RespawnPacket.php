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

class RespawnPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::RESPAWN_PACKET;

	public const SEARCHING_FOR_SPAWN = 0;
	public const READY_TO_SPAWN = 1;
	public const CLIENT_READY_TO_SPAWN = 2;

	/** @var Vector3 */
	public $position;
	/** @var int */
	public $respawnState = self::SEARCHING_FOR_SPAWN;
	/** @var int */
	public $entityRuntimeId;

	protected function decodePayload() : void
	{
		$this->position = $this->getVector3();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->respawnState = $this->getByte();
			$this->entityRuntimeId = $this->getEntityRuntimeId();
		}
	}

	protected function encodePayload() : void
	{
		$this->putVector3($this->position);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->putByte($this->respawnState);
			$this->putEntityRuntimeId($this->entityRuntimeId);
		}
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleRespawn($this);
	}
}
