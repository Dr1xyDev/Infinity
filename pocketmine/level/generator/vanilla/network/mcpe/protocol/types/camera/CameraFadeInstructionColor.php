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

final class CameraFadeInstructionColor
{
	public function __construct(
		private float $red,
		private float $green,
		private float $blue,
	) {
	}

	public function getRed() : float
	{
		return $this->red;
	}

	public function getGreen() : float
	{
		return $this->green;
	}

	public function getBlue() : float
	{
		return $this->blue;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$red = $in->getLFloat();
		$green = $in->getLFloat();
		$blue = $in->getLFloat();
		return new self($red, $green, $blue);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putLFloat($this->red);
		$out->putLFloat($this->green);
		$out->putLFloat($this->blue);
	}
}
