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

use pocketmine\level\generator\vanilla\level\biome\BiomeCategory;
use pocketmine\level\generator\vanilla\level\biome\BiomeFactory;
use pocketmine\level\generator\vanilla\level\biome\BiomeIds;
use pocketmine\level\generator\vanilla\level\biome\RainType;

class ShoreLayer extends Layer {

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
				$biome = BiomeFactory::getInstance()->get($old);

				$n = $layerData->parentArea[($x + 1) + $y * $pw];
				$e = $layerData->parentArea[($x + 2) + ($y + 1) * $pw];
				$w_side = $layerData->parentArea[$x + ($y + 1) * $pw];
				$s = $layerData->parentArea[($x + 1) + ($y + 2) * $pw];

				if ($old === BiomeIds::MUSHROOM_ISLAND) {
					if ($n === BiomeIds::OCEAN || $e === BiomeIds::OCEAN || $w_side === BiomeIds::OCEAN || $s === BiomeIds::OCEAN) {
						$layerData->result[$resIndex] = BiomeIds::MUSHROOM_ISLAND_SHORE;
					} else {
						$layerData->result[$resIndex] = $old;
					}
				} elseif ($biome !== null && $biome->getCategory() === BiomeCategory::JUNGLE) {
					if (!$this->isJungleCompatible($n) || !$this->isJungleCompatible($e) ||
						!$this->isJungleCompatible($w_side) || !$this->isJungleCompatible($s)) {
						$layerData->result[$resIndex] = BiomeIds::JUNGLE_EDGE;
					} elseif ($this->isOcean($n) || $this->isOcean($e) || $this->isOcean($w_side) || $this->isOcean($s)) {
						$layerData->result[$resIndex] = BiomeIds::BEACHES;
					} else {
						$layerData->result[$resIndex] = $old;
					}
				} elseif ($old === BiomeIds::EXTREME_HILLS || $old === BiomeIds::EXTREME_HILLS_WITH_TREES || $old === BiomeIds::SMALLER_EXTREME_HILLS) {
					$layerData->result[$resIndex] = $this->getReplacedIfNeighborOcean($old, BiomeIds::STONE_BEACH, $n, $e, $w_side, $s);
				} elseif ($biome !== null && $biome->getPrecipitation() === RainType::SNOW) {
					$layerData->result[$resIndex] = $this->getReplacedIfNeighborOcean($old, BiomeIds::COLD_BEACH, $n, $e, $w_side, $s);
				} elseif ($old === BiomeIds::MESA || $old === BiomeIds::MESA_ROCK) {
					if ($this->isOcean($n) || $this->isOcean($e) || $this->isOcean($w_side) || $this->isOcean($s)) {
						$layerData->result[$resIndex] = $old;
					} elseif (!$this->isMesa($n) || !$this->isMesa($e) || !$this->isMesa($w_side) || !$this->isMesa($s)) {
						$layerData->result[$resIndex] = BiomeIds::DESERT;
					} else {
						$layerData->result[$resIndex] = $old;
					}
				} elseif ($old !== BiomeIds::OCEAN && $old !== BiomeIds::DEEP_OCEAN && $old !== BiomeIds::RIVER && $old !== BiomeIds::SWAMPLAND) {
					if ($this->isOcean($n) || $this->isOcean($e) || $this->isOcean($w_side) || $this->isOcean($s)) {
						$layerData->result[$resIndex] = BiomeIds::BEACHES;
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

	protected function getReplacedIfNeighborOcean(int $old, int $replacement, int $n, int $e, int $w_side, int $s) : int{
		if ($this->isOcean($old)) {
			return $old;
		}

		if ($this->isOcean($n) || $this->isOcean($e) || $this->isOcean($w_side) || $this->isOcean($s)) {
			return $replacement;
		}

		return $old;
	}

	protected function isJungleCompatible(int $id) : bool{
		$biome = BiomeFactory::getInstance()->get($id);
		if ($biome !== null && $biome->getCategory() === BiomeCategory::JUNGLE) {
			return true;
		}

		return !($id !== BiomeIds::JUNGLE_EDGE &&
			$id !== BiomeIds::JUNGLE &&
			$id !== BiomeIds::JUNGLE_HILLS &&
			$id !== BiomeIds::FOREST &&
			$id !== BiomeIds::TAIGA &&
			!$this->isOcean($id));
	}

	protected function isMesa(int $id) : bool{
		$biome = BiomeFactory::getInstance()->get($id);
		return $biome !== null && $biome->getCategory() === BiomeCategory::MESA;
	}
}
