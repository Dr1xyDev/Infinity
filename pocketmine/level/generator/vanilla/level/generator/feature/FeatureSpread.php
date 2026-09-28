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

namespace pocketmine\level\generator\vanilla\level\generator\feature;

use pocketmine\level\generator\vanilla\utils\Random;

class FeatureSpread {
	public function __construct(
		public int $base,
		public int $spread = 0
	){}

	public function getCount(Random $random) : int {
		return $this->spread === 0 ? $this->base : $this->base + $random->nextBoundedInt($this->spread + 1);
	}

	public function equals(FeatureSpread $spread) : bool {
		if ($this === $spread) {
			return true;
		} elseif ($this instanceof $spread) {
			return $this->base == $spread->base && $this->spread == $spread->spread;
		} else {
			return false;
		}
	}
}
