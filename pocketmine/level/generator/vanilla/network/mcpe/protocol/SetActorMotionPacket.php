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

class SetActorMotionPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::SET_ACTOR_MOTION_PACKET;

	/** @var int */
	public $entityRuntimeId;
	/** @var Vector3 */
	public $motion;
	/** @var int */
	public $tick;

	protected function decodePayload() : void
	{
		$this->entityRuntimeId = $this->getEntityRuntimeId();
		$this->motion = $this->getVector3();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_662) {
			$this->tick = $this->getUnsignedVarLong();
		}
	}

	protected function encodePayload() : void
	{
		$this->putEntityRuntimeId($this->entityRuntimeId);
		$this->putVector3($this->motion);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_662) {
			$this->putUnsignedVarLong($this->tick);
		}
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleSetActorMotion($this);
	}
}
