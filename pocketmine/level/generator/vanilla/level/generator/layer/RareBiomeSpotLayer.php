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

use pocketmine\level\generator\vanilla\level\biome\BiomeIds;

class RareBiomeSpotLayer extends Layer {

	protected Layer $parent;

	public function __construct(int $seed, Layer $parent){
		parent::__construct($seed);
		$this->parent = $parent;
	}

	public function fillArea(LayerData $layerData, int $xo, int $yo, int $w, int $h) : void{
		$px = $xo - 1;
		$py = $yo - 1;
		$pw = $w + 2;
		$ph = $h + 2;

		$this->parent->fillArea($layerData, $px, $py, $pw, $ph);

		for ($y = 0; $y < $h; $y++) {
			for ($x = 0; $x < $w; $x++) {
				$this->initRandom($x + $xo, $y + $yo);

				$centerIndex = ($x + 1) + ($y + 1) * $pw;
				$resIndex = $x + $y * $w;

				$old = $layerData->parentArea[$centerIndex];

				if ($this->nextRandom(57) === 0) {
					if ($old === BiomeIds::PLAINS) {
						$layerData->result[$resIndex] = BiomeIds::PLAINS + BiomeIds::MUTATED_OFFSET;
					} else {
						$layerData->result[$resIndex] = $old;
					}
				} else {
					$layerData->result[$resIndex] = $old;
				}
			}
		}

		$layerData->swap();
	}
}
