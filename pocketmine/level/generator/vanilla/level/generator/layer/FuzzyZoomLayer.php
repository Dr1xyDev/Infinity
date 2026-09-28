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

namespace pocketmine\level\generator\vanilla\level\generator\layer;

class FuzzyZoomLayer extends ZoomLayer {

	public static function zoom(int $seed, Layer $sup, int $count) : Layer{
		$result = $sup;
		for ($i = 0; $i < $count; $i++) {
			$result = new FuzzyZoomLayer($seed + $i, $result);
		}

		return $result;
	}

	public function modeOrRandom(int $a, int $b, int $c, int $d) : int{
		return $this->random([$a, $b, $c, $d]);
	}
}
