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

final class CameraSetInstructionEase
{
	/**
	 * @see CameraSetInstructionEaseType
	 */
	public function __construct(
		private int $type,
		private float $duration
	) {
	}

	/**
	 * @see CameraSetInstructionEaseType
	 */
	public function getType() : int
	{
		return $this->type;
	}

	public function getDuration() : float
	{
		return $this->duration;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$type = $in->getByte();
		$duration = $in->getLFloat();
		return new self($type, $duration);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putByte($this->type);
		$out->putLFloat($this->duration);
	}
}
