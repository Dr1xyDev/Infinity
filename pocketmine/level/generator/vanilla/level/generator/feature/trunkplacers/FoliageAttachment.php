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

namespace pocketmine\level\generator\vanilla\level\generator\feature\trunkplacers;

use pocketmine\level\generator\vanilla\math\Vector3;

class FoliageAttachment {

	public function __construct(
		private Vector3 $pos,
		private int $radiusOffset,
		private bool $doubleTrunk
	){}

	public function pos() : Vector3 {
		return $this->pos;
	}

	public function radiusOffset() : int {
		return $this->radiusOffset;
	}

	public function doubleTrunk() : bool {
		return $this->doubleTrunk;
	}
}
