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

#include <rules/DataPacket.h>

use pocketmine\level\generator\vanilla\math\Vector2;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;

class CorrectPlayerMovePredictionPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::CORRECT_PLAYER_MOVE_PREDICTION_PACKET;

	public const PREDICTION_TYPE_VEHICLE = 0;
	public const PREDICTION_TYPE_PLAYER = 1;

	public Vector3 $position;
	public Vector3 $delta;
	public bool $onGround;
	public int $tick;
	public int $predictionType = self::PREDICTION_TYPE_PLAYER;
	public ?Vector2 $vehicleRotation = null;
	public ?float $vehicleAngularVelocity = null;

	/**
	 * Wire format history:
	 *  - 630..711: position, delta, onGround, tick, predictionType
	 *  - 712..826: predictionType first, vehicle rotation + optional angular velocity only for VEHICLE, no trailing type
	 *  - 827+: vehicle rotation and the optional angular velocity are always present
	 */
	private function hasVehicleData() : bool
	{
		return $this->protocol >= ProtocolInfo::PROTOCOL_827 ||
			($this->protocol >= ProtocolInfo::PROTOCOL_712 && $this->predictionType === self::PREDICTION_TYPE_VEHICLE);
	}

	protected function decodePayload() : void
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_712) {
			$this->predictionType = $this->getByte();
		}
		$this->position = $this->getVector3();
		$this->delta = $this->getVector3();
		if ($this->hasVehicleData()) {
			$this->vehicleRotation = new Vector2($this->getLFloat(), $this->getLFloat());
			$this->vehicleAngularVelocity = $this->getOptional($this->getLFloat(...));
		}
		$this->onGround = $this->getBool();
		$this->tick = $this->getUnsignedVarLong();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_630 && $this->protocol < ProtocolInfo::PROTOCOL_712) {
			$this->predictionType = $this->getByte();
		}
	}

	protected function encodePayload() : void
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_712) {
			$this->putByte($this->predictionType);
		}
		$this->putVector3($this->position);
		$this->putVector3($this->delta);
		if ($this->hasVehicleData()) {
			$rotation = $this->vehicleRotation ?? new Vector2(0, 0);
			$this->putLFloat($rotation->getX());
			$this->putLFloat($rotation->getY());
			$this->putOptional($this->vehicleAngularVelocity, $this->putLFloat(...));
		}
		$this->putBool($this->onGround);
		$this->putUnsignedVarLong($this->tick);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_630 && $this->protocol < ProtocolInfo::PROTOCOL_712) {
			$this->putByte($this->predictionType);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleCorrectPlayerMovePrediction($this);
	}
}
