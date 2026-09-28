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

namespace pocketmine\level\generator\vanilla\block\utils;

use pocketmine\level\generator\vanilla\math\Axis;

class PillarRotations{
	public function __construct(
		private int $metaAxisX,
		private int $metaAxisY,
		private int $metaAxisZ
	){}

	public function getMetaAxisX() : int {
		return $this->metaAxisX;
	}

	public function getMetaAxisY() : int {
		return $this->metaAxisY;
	}

	public function getMetaAxisZ() : int {
		return $this->metaAxisZ;
	}

	public function fromAxis(int $axis) : int {
		return match($axis) {
			Axis::X => $this->metaAxisX,
			Axis::Y => $this->metaAxisY,
			Axis::Z => $this->metaAxisZ,
			default => throw new \InvalidArgumentException("Invalid axis $axis")
		};
	}
}
