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

use pocketmine\level\generator\vanilla\level\biome\BiomeFactory;
use pocketmine\level\generator\vanilla\level\biome\BiomeIds;
use pocketmine\level\generator\vanilla\level\biome\TempCategory;

class BiomeEdgeLayer extends Layer {

	protected Layer $parent;
	protected bool $generateJungles;

	public function __construct(int $seed, Layer $parent, bool $generateJungles){
		parent::__construct($seed);
		$this->parent = $parent;
		$this->generateJungles = $generateJungles;
	}

	public function fillArea(LayerData $layerData, int $xo, int $yo, int $w, int $h) : void {
		$pw = $w + 2;
		$this->parent->fillArea($layerData, $xo - 1, $yo - 1, $pw, $h + 2);

		for ($y = 0; $y < $h; $y++) {
			for ($x = 0; $x < $w; $x++) {
				$this->initRandom($x + $xo, $y + $yo);

				$old = $layerData->parentArea[($x + 1) + ($y + 1) * $pw];
				$resIndex = $x + $y * $w;

				if ($this->checkEdge($layerData, $x, $y, $w, $old, BiomeIds::EXTREME_HILLS, BiomeIds::SMALLER_EXTREME_HILLS)) {
				} elseif ($this->checkEdgeStrict($layerData, $x, $y, $w, $old, BiomeIds::MESA_ROCK, BiomeIds::MESA)) {
				} elseif ($this->checkEdgeStrict($layerData, $x, $y, $w, $old, BiomeIds::MESA_CLEAR_ROCK, BiomeIds::MESA)) {
				} elseif ($this->checkEdgeStrict($layerData, $x, $y, $w, $old, BiomeIds::REDWOOD_TAIGA, BiomeIds::TAIGA)) {
				} elseif ($old === BiomeIds::DESERT) {
					$n = $layerData->parentArea[($x + 1) + $y * $pw];
					$e = $layerData->parentArea[($x + 2) + ($y + 1) * $pw];
					$w_side = $layerData->parentArea[$x + ($y + 1) * $pw];
					$s = $layerData->parentArea[($x + 1) + ($y + 2) * $pw];

					if ($n === BiomeIds::ICE_FLATS || $e === BiomeIds::ICE_FLATS || $w_side === BiomeIds::ICE_FLATS || $s === BiomeIds::ICE_FLATS) {
						$layerData->result[$resIndex] = BiomeIds::EXTREME_HILLS_WITH_TREES;
					} else {
						$layerData->result[$resIndex] = $old;
					}
				} elseif ($this->generateJungles && $old === BiomeIds::SWAMPLAND) {
					$n = $layerData->parentArea[($x + 1) + $y * $pw];
					$e = $layerData->parentArea[($x + 2) + ($y + 1) * $pw];
					$w_side = $layerData->parentArea[$x + ($y + 1) * $pw];
					$s = $layerData->parentArea[($x + 1) + ($y + 2) * $pw];

					if ($n === BiomeIds::DESERT || $e === BiomeIds::DESERT || $w_side === BiomeIds::DESERT || $s === BiomeIds::DESERT ||
						$n === BiomeIds::TAIGA_COLD || $e === BiomeIds::TAIGA_COLD || $w_side === BiomeIds::TAIGA_COLD || $s === BiomeIds::TAIGA_COLD ||
						$n === BiomeIds::ICE_FLATS || $e === BiomeIds::ICE_FLATS || $w_side === BiomeIds::ICE_FLATS || $s === BiomeIds::ICE_FLATS) {
						$layerData->result[$resIndex] = BiomeIds::PLAINS;
					} elseif ($n === BiomeIds::JUNGLE || $s === BiomeIds::JUNGLE || $e === BiomeIds::JUNGLE || $w_side === BiomeIds::JUNGLE) {
						$layerData->result[$resIndex] = BiomeIds::JUNGLE_EDGE;
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

	protected function checkEdge(LayerData $layerData, int $x, int $y, int $w, int $old, int $checkFor, int $addEdge) : bool {
		if ($this->isSame($old, $checkFor)) {
			$pw = $w + 2;
			$n = $layerData->parentArea[($x + 1) + $y * $pw];
			$e = $layerData->parentArea[($x + 2) + ($y + 1) * $pw];
			$w_side = $layerData->parentArea[$x + ($y + 1) * $pw];
			$s = $layerData->parentArea[($x + 1) + ($y + 2) * $pw];

			if (!$this->isValidTemperatureEdge($n, $checkFor) ||
				!$this->isValidTemperatureEdge($e, $checkFor) ||
				!$this->isValidTemperatureEdge($w_side, $checkFor) ||
				!$this->isValidTemperatureEdge($s, $checkFor)) {
				$layerData->result[$x + $y * $w] = $addEdge;
			} else {
				$layerData->result[$x + $y * $w] = $old;
			}
			return true;
		}
		return false;
	}

	protected function checkEdgeStrict(LayerData $layerData, int $x, int $y, int $w, int $old, int $checkFor, int $addEdge) : bool {
		if ($old === $checkFor) {
			$pw = $w + 2;
			$n = $layerData->parentArea[($x + 1) + $y * $pw];
			$e = $layerData->parentArea[($x + 2) + ($y + 1) * $pw];
			$w_side = $layerData->parentArea[$x + ($y + 1) * $pw];
			$s = $layerData->parentArea[($x + 1) + ($y + 2) * $pw];

			if (!$this->isSame($n, $checkFor) || !$this->isSame($e, $checkFor) ||
				!$this->isSame($w_side, $checkFor) || !$this->isSame($s, $checkFor)) {
				$layerData->result[$x + $y * $w] = $addEdge;
			} else {
				$layerData->result[$x + $y * $w] = $old;
			}
			return true;
		}
		return false;
	}

	protected function isValidTemperatureEdge(int $a, int $b) : bool {
		if ($this->isSame($a, $b)) {
			return true;
		}

		$biomeFactory = BiomeFactory::getInstance();
		$biome1 = $biomeFactory->get($a);
		$biome2 = $biomeFactory->get($b);

		if ($biome1 !== null && $biome2 !== null) {
			$at = $biome1->getTempCategory();
			$bt = $biome2->getTempCategory();
			return ($at === $bt || $at === TempCategory::MEDIUM || $bt === TempCategory::MEDIUM);
		}

		return false;
	}
}
