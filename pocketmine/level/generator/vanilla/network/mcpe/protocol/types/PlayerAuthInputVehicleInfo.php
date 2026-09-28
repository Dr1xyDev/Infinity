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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types;

use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;

final class PlayerAuthInputVehicleInfo
{
	public function __construct(
		private float $vehicleRotationX,
		private float $vehicleRotationZ,
		private int $predictedVehicleActorUniqueId
	) {
	}

	public function getVehicleRotationX() : float
	{
		return $this->vehicleRotationX;
	}

	public function getVehicleRotationZ() : float
	{
		return $this->vehicleRotationZ;
	}

	public function getPredictedVehicleActorUniqueId() : int
	{
		return $this->predictedVehicleActorUniqueId;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_662) {
			$vehicleRotationX = $in->getLFloat();
			$vehicleRotationZ = $in->getLFloat();
		}
		$predictedVehicleActorUniqueId = $in->getEntityUniqueId();

		return new self($vehicleRotationX ?? 0, $vehicleRotationZ ?? 0, $predictedVehicleActorUniqueId);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_662) {
			$out->putLFloat($this->vehicleRotationX);
			$out->putLFloat($this->vehicleRotationZ);
		}
		$out->putEntityUniqueId($this->predictedVehicleActorUniqueId);
	}
}
