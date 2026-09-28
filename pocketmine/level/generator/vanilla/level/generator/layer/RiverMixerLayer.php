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

class RiverMixerLayer extends Layer {

	protected Layer $parent;
	protected Layer $rivers;

	public function __construct(int $seed, Layer $biomes, Layer $rivers){
		parent::__construct($seed);
		$this->parent = $biomes;
		$this->rivers = $rivers;
	}

	public function fillArea(LayerData $layerData, int $xo, int $yo, int $w, int $h) : void{
		$this->parent->fillArea($layerData, $xo, $yo, $w, $h);

		$riverData = new LayerData();
		$this->rivers->fillArea($riverData, $xo, $yo, $w, $h);

		$size = $w * $h;

		for ($i = 0; $i < $size; $i++) {
			$biomeId = $layerData->parentArea[$i];

			if ($biomeId === BiomeIds::OCEAN || $biomeId === BiomeIds::DEEP_OCEAN) {
				$layerData->result[$i] = $biomeId;
			} else {
				$riverId = $riverData->parentArea[$i];

				if ($riverId === BiomeIds::RIVER) {
					if ($biomeId === BiomeIds::ICE_FLATS) {
						$layerData->result[$i] = BiomeIds::FROZEN_RIVER;
					} elseif ($biomeId === BiomeIds::MUSHROOM_ISLAND || $biomeId === BiomeIds::MUSHROOM_ISLAND_SHORE) {
						$layerData->result[$i] = BiomeIds::MUSHROOM_ISLAND_SHORE;
					}else {
						$layerData->result[$i] = $riverId & 0xFF;
					}
				} else {
					$layerData->result[$i] = $biomeId;
				}
			}
		}

		$layerData->swap();
	}
}
