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

class MoveActorAbsolutePacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::MOVE_ACTOR_ABSOLUTE_PACKET;

	public const FLAG_GROUND = 0x01;
	public const FLAG_TELEPORT = 0x02;
	public const FLAG_FORCE_MOVE_LOCAL_ENTITY = 0x04;
	public const FLAG_FORCE_COMPLETION = 0x8;

	public int $entityRuntimeId;
	public Vector3 $position;
	public float $pitch;
	public float $yaw;
	public float $headYaw; //always zero for non-mobs
	public int $flags = 0;

	protected function decodePayload() : void
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->entityRuntimeId = $this->getEntityRuntimeId();
			$this->flags = $this->getByte();
			$this->position = $this->getVector3();
			$this->pitch = $this->getByteRotation();
			$this->yaw = $this->getByteRotation();
			$this->headYaw = $this->getByteRotation();
		} else {
			$this->entityRuntimeId = $this->getEntityRuntimeId();
			$this->position = $this->getVector3();
			$this->pitch = $this->getByteRotation();
			$this->headYaw = $this->getByteRotation();
			$this->yaw = $this->getByteRotation();

			$onGround = $this->getBool();
			$isTeleported = $this->getBool();
			if ($onGround) {
				$this->flags |= self::FLAG_GROUND;
			}
			if ($isTeleported) {
				$this->flags |= self::FLAG_TELEPORT;
			}
		}
	}

	protected function encodePayload() : void
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->putEntityRuntimeId($this->entityRuntimeId);
			$this->putByte($this->flags);
			$this->putVector3($this->position);
			$this->putByteRotation($this->pitch);
			$this->putByteRotation($this->yaw);
			$this->putByteRotation($this->headYaw);
		} else {
			$this->putEntityRuntimeId($this->entityRuntimeId);
			$this->putVector3($this->position);
			$this->putByteRotation($this->pitch);
			$this->putByteRotation($this->headYaw);
			$this->putByteRotation($this->yaw);

			$this->putBool(($this->flags & MoveActorAbsolutePacket::FLAG_GROUND) !== 0);
			$this->putBool(($this->flags & MoveActorAbsolutePacket::FLAG_TELEPORT) !== 0);
		}
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleMoveActorAbsolute($this);
	}
}
