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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\camera;

use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;

final class CameraSetInstructionRotation
{
	public function __construct(
		private float $pitch,
		private float $yaw,
	) {
	}

	public function getPitch() : float
	{
		return $this->pitch;
	}

	public function getYaw() : float
	{
		return $this->yaw;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$pitch = $in->getLFloat();
		$yaw = $in->getLFloat();
		return new self($pitch, $yaw);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putLFloat($this->pitch);
		$out->putLFloat($this->yaw);
	}
}
