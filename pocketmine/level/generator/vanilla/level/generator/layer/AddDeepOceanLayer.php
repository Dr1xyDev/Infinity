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

class AddDeepOceanLayer extends Layer {

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
				$north = $layerData->parentArea[($x + 1) + $y * $pw];
				$east = $layerData->parentArea[($x + 2) + ($y + 1) * $pw];
				$west = $layerData->parentArea[$x + ($y + 1) * $pw];
				$south = $layerData->parentArea[($x + 1) + ($y + 2) * $pw];

				$center = $layerData->parentArea[($x + 1) + ($y + 1) * $pw];

				$count = 0;
				if ($north === 0) $count++;
				if ($east === 0) $count++;
				if ($west === 0) $count++;
				if ($south === 0) $count++;

				$resultIndex = $x + $y * $w;

				if ($center === 0 && $count > 3) {
					$layerData->result[$resultIndex] = BiomeIds::DEEP_OCEAN;
				} else {
					$layerData->result[$resultIndex] = $center;
				}
			}
		}

		$layerData->swap();
	}
}
