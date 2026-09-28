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

class MotionPredictionHintsPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::MOTION_PREDICTION_HINTS_PACKET;

	public int $entityRuntimeId;
	public Vector3 $motion;
	public bool $onGround;

	public static function create(int $entityRuntimeId, Vector3 $motion, bool $onGround) : self
	{
		$result = new self();
		$result->entityRuntimeId = $entityRuntimeId;
		$result->motion = $motion;
		$result->onGround = $onGround;
		return $result;
	}

	public function getEntityRuntimeIdField() : int
	{
		return $this->entityRuntimeId;
	}

	public function getMotion() : Vector3
	{
		return $this->motion;
	}

	public function isOnGround() : bool
	{
		return $this->onGround;
	}

	protected function decodePayload() : void
	{
		$this->entityRuntimeId = $this->getEntityRuntimeId();
		$this->motion = $this->getVector3();
		$this->onGround = $this->getBool();
	}

	protected function encodePayload() : void
	{
		$this->putEntityRuntimeId($this->entityRuntimeId);
		$this->putVector3($this->motion);
		$this->putBool($this->onGround);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleMotionPredictionHints($this);
	}
}
