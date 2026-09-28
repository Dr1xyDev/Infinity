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

class CameraShakePacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::CAMERA_SHAKE_PACKET;

	public const int TYPE_POSITIONAL = 0;
	public const int TYPE_ROTATIONAL = 1;

	public const int ACTION_ADD = 0;
	public const int ACTION_STOP = 1;

	public float $intensity;
	public float $duration;
	public int $shakeType;
	public int $shakeAction;

	public static function create(float $intensity, float $duration, int $shakeType, int $shakeAction) : self
	{
		$result = new self();
		$result->intensity = $intensity;
		$result->duration = $duration;
		$result->shakeType = $shakeType;
		$result->shakeAction = $shakeAction;
		return $result;
	}

	public function getIntensity() : float
	{
		return $this->intensity;
	}

	public function getDuration() : float
	{
		return $this->duration;
	}

	public function getShakeType() : int
	{
		return $this->shakeType;
	}

	public function getShakeAction() : int
	{
		return $this->shakeAction;
	}

	protected function decodePayload() : void
	{
		$this->intensity = $this->getLFloat();
		$this->duration = $this->getLFloat();
		$this->shakeType = $this->getByte();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_428) {
			$this->shakeAction = $this->getByte();
		}
	}

	protected function encodePayload() : void
	{
		$this->putLFloat($this->intensity);
		$this->putLFloat($this->duration);
		$this->putByte($this->shakeType);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_428) {
			$this->putByte($this->shakeAction);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleCameraShake($this);
	}
}
