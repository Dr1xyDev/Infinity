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

class IslandLayer extends Layer {

	public function fillArea(LayerData $layerData, int $xo, int $yo, int $w, int $h) : void{
		for ($y = 0; $y < $h; $y++) {
			for ($x = 0; $x < $w; $x++) {
				$this->initRandom($xo + $x, $yo + $y);

				$layerData->result[$x + $y * $w] = ($this->nextRandom(10) === 0) ? 1 : 0;
			}
		}

		if ($xo > -$w && $xo <= 0 && $yo > -$h && $yo <= 0) {
			$layerData->result[-$xo + -$yo * $w] = 1;
		}

		$layerData->swap();
	}
}
