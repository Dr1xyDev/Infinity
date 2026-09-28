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
use function error_log;
use function sprintf;

class RegionHillsLayer extends Layer {

	protected Layer $parent;
	protected Layer $riverLayer;

	public function __construct(int $seed, Layer $parent, Layer $riverLayer){
		parent::__construct($seed);
		$this->parent = $parent;
		$this->riverLayer = $riverLayer;
	}

	public function fillArea(LayerData $layerData, int $xo, int $yo, int $w, int $h) : void{
		$px = $xo - 1;
		$py = $yo - 1;
		$pw = $w + 2;
		$ph = $h + 2;

		$this->parent->fillArea($layerData, $px, $py, $pw, $ph);

		$riverData = new LayerData();
		$this->riverLayer->fillArea($riverData, $px, $py, $pw, $ph);

		for ($y = 0; $y < $h; $y++) {
			for ($x = 0; $x < $w; $x++) {
				$this->initRandom($x + $xo, $y + $yo);

				$centerIndex = ($x + 1) + ($y + 1) * $pw;
				$resIndex = $x + $y * $w;

				$old = $layerData->parentArea[$centerIndex];
				$river = $riverData->parentArea[$centerIndex];

				$isSpecialHills = (($river - 2) % 29) === 0;

				if ($old > 255) {
					error_log(sprintf("Warning: old biome ID is too high! %d", $old));
				}

				if ($old !== 0 && $river >= 2 && (($river - 2) % 29) === 1 && $old < BiomeIds::MUTATED_OFFSET) {
					if (BiomeFactory::getInstance()->get($old + BiomeIds::MUTATED_OFFSET) !== null) {
						$layerData->result[$resIndex] = $old + BiomeIds::MUTATED_OFFSET;
					} else {
						$layerData->result[$resIndex] = $old;
					}
				} elseif ($this->nextRandom(3) === 0 || $isSpecialHills) {
					$next = $old;

					if ($old === BiomeIds::DESERT) {
						$next = BiomeIds::DESERT_HILLS;
					} elseif ($old === BiomeIds::FOREST) {
						$next = BiomeIds::FOREST_HILLS;
					} elseif ($old === BiomeIds::BIRCH_FOREST) {
						$next = BiomeIds::BIRCH_FOREST_HILLS;
					} elseif ($old === BiomeIds::ROOFED_FOREST) {
						$next = BiomeIds::PLAINS;
					} elseif ($old === BiomeIds::TAIGA) {
						$next = BiomeIds::TAIGA_HILLS;
					} elseif ($old === BiomeIds::REDWOOD_TAIGA) {
						$next = BiomeIds::REDWOOD_TAIGA_HILLS;
					} elseif ($old === BiomeIds::TAIGA_COLD) {
						$next = BiomeIds::TAIGA_COLD_HILLS;
					} elseif ($old === BiomeIds::PLAINS) {
						$next = ($this->nextRandom(3) === 0) ? BiomeIds::FOREST_HILLS : BiomeIds::FOREST;
					} elseif ($old === BiomeIds::ICE_FLATS) {
						$next = BiomeIds::ICE_MOUNTAINS;
					} elseif ($old === BiomeIds::JUNGLE) {
						$next = BiomeIds::JUNGLE_HILLS;
					} elseif ($old === BiomeIds::OCEAN) {
						$next = BiomeIds::DEEP_OCEAN;
					} elseif ($old === BiomeIds::EXTREME_HILLS) {
						$next = BiomeIds::EXTREME_HILLS_WITH_TREES;
					} elseif ($old === BiomeIds::SAVANNA) {
						$next = BiomeIds::SAVANNA_ROCK;
					} elseif ($this->isSame($old, BiomeIds::MESA_ROCK)) {
						$next = BiomeIds::MESA;
					} elseif ($old === BiomeIds::DEEP_OCEAN) {
						if ($this->nextRandom(3) === 0) {
							$next = ($this->nextRandom(2) === 0) ? BiomeIds::PLAINS : BiomeIds::FOREST;
						}
					}

					if ($isSpecialHills && $next !== $old) {
						if (BiomeFactory::getInstance()->get($next + BiomeIds::MUTATED_OFFSET) !== null) {
							$next = $next + BiomeIds::MUTATED_OFFSET;
						} else {
							$next = $old;
						}
					}

					if ($next === $old) {
						$layerData->result[$resIndex] = $old;
					} else {
						$n = $layerData->parentArea[($x + 1) + $y * $pw];
						$e = $layerData->parentArea[($x + 2) + ($y + 1) * $pw];
						$w_side = $layerData->parentArea[$x + ($y + 1) * $pw];
						$s = $layerData->parentArea[($x + 1) + ($y + 2) * $pw];

						$c = 0;
						if ($this->isSame($n, $old)) $c++;
						if ($this->isSame($e, $old)) $c++;
						if ($this->isSame($w_side, $old)) $c++;
						if ($this->isSame($s, $old)) $c++;

						if ($c >= 3) {
							$layerData->result[$resIndex] = $next;
						} else {
							$layerData->result[$resIndex] = $old;
						}
					}
				} else {
					$layerData->result[$resIndex] = $old;
				}
			}
		}

		$layerData->swap();
	}
}
