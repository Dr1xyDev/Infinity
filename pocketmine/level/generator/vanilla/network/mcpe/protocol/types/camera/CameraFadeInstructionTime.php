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

final class CameraFadeInstructionTime
{
	public function __construct(
		private float $fadeInTime,
		private float $stayTime,
		private float $fadeOutTime
	) {
	}

	public function getFadeInTime() : float
	{
		return $this->fadeInTime;
	}

	public function getStayTime() : float
	{
		return $this->stayTime;
	}

	public function getFadeOutTime() : float
	{
		return $this->fadeOutTime;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$fadeInTime = $in->getLFloat();
		$stayTime = $in->getLFloat();
		$fadeOutTime = $in->getLFloat();
		return new self($fadeInTime, $stayTime, $fadeOutTime);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putLFloat($this->fadeInTime);
		$out->putLFloat($this->stayTime);
		$out->putLFloat($this->fadeOutTime);
	}
}
