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

class MovePlayerPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::MOVE_PLAYER_PACKET;

	public const MODE_NORMAL = 0;
	public const MODE_RESET = 1;
	public const MODE_TELEPORT = 2;
	public const MODE_PITCH = 3; //facepalm Mojang

	/** @var int */
	public $entityRuntimeId;
	/** @var Vector3 */
	public $position;
	/** @var float */
	public $pitch;
	/** @var float */
	public $yaw;
	/** @var float */
	public $headYaw;
	/** @var int */
	public $mode = self::MODE_NORMAL;
	/** @var bool */
	public $onGround = false; //TODO
	/** @var int */
	public $ridingEid = 0;
	/** @var int */
	public $teleportCause = 0;
	/** @var int */
	public $teleportItem = 0;
	/** @var int */
	public $tick = 0;

	protected function decodePayload() : void
	{
		$this->entityRuntimeId = $this->getEntityRuntimeId();
		$this->position = $this->getVector3();
		$this->pitch = $this->getLFloat();
		$this->yaw = $this->getLFloat();
		$this->headYaw = $this->getLFloat();
		$this->mode = $this->getByte();
		$this->onGround = $this->getBool();
		$this->ridingEid = $this->getEntityRuntimeId();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_2168) {
			//since 1.26.40, the teleport data is an optional, independent from the mode
			if ($this->getBool()) {
				$this->teleportCause = $this->getLInt();
				$this->teleportItem = $this->getLInt();
			}
		} elseif ($this->mode === MovePlayerPacket::MODE_TELEPORT) {
			$this->teleportCause = $this->getLInt();
			$this->teleportItem = $this->getLInt();
		}
		if ($this->protocol >= ProtocolInfo::PROTOCOL_419) {
			$this->tick = $this->getUnsignedVarLong();
		}
	}

	protected function encodePayload() : void
	{
		$this->putEntityRuntimeId($this->entityRuntimeId);
		$this->putVector3($this->position);
		$this->putLFloat($this->pitch);
		$this->putLFloat($this->yaw);
		$this->putLFloat($this->headYaw);
		$this->putByte($this->mode);
		$this->putBool($this->onGround);
		$this->putEntityRuntimeId($this->ridingEid);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_2168) {
			$this->putBool($hasTeleportData = $this->mode === MovePlayerPacket::MODE_TELEPORT);
			if ($hasTeleportData) {
				$this->putLInt($this->teleportCause);
				$this->putLInt($this->teleportItem);
			}
		} elseif ($this->mode === MovePlayerPacket::MODE_TELEPORT) {
			$this->putLInt($this->teleportCause);
			$this->putLInt($this->teleportItem);
		}
		if ($this->protocol >= ProtocolInfo::PROTOCOL_419) {
			$this->putUnsignedVarLong($this->tick);
		}
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleMovePlayer($this);
	}
}
