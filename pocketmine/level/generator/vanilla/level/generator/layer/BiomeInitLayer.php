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
use function count;

class BiomeInitLayer extends Layer {

	/** @var int[] */
	protected array $warmBiomes = [];
	/** @var int[] */
	protected array $mediumBiomes = [];
	/** @var int[] */
	protected array $coldBiomes = [];
	/** @var int[] */
	protected array $iceBiomes = [];

	protected Layer $parent;

	public function __construct(int $seed, Layer $parent, bool $legacyBiomes){
		parent::__construct($seed);
		$this->parent = $parent;
		$this->warmBiomes = [
			BiomeIds::DESERT,
			BiomeIds::DESERT,
			BiomeIds::DESERT,
			BiomeIds::SAVANNA,
			BiomeIds::SAVANNA,
			BiomeIds::PLAINS
		];

		$this->mediumBiomes = [
			BiomeIds::FOREST
		];

		if (!$legacyBiomes) {
			$this->mediumBiomes[] = BiomeIds::ROOFED_FOREST;
		}

		$this->mediumBiomes[] = BiomeIds::EXTREME_HILLS;
		$this->mediumBiomes[] = BiomeIds::PLAINS;
		$this->mediumBiomes[] = BiomeIds::PLAINS;
		$this->mediumBiomes[] = BiomeIds::PLAINS;
		$this->mediumBiomes[] = BiomeIds::BIRCH_FOREST;
		$this->mediumBiomes[] = BiomeIds::SWAMPLAND;

		$this->coldBiomes = [
			BiomeIds::FOREST,
			BiomeIds::EXTREME_HILLS,
			BiomeIds::TAIGA,
			BiomeIds::PLAINS
		];

		$this->iceBiomes = [
			BiomeIds::ICE_FLATS,
			BiomeIds::ICE_FLATS,
			BiomeIds::ICE_FLATS,
			BiomeIds::TAIGA_COLD
		];
	}

	public function fillArea(LayerData $layerData, int $xo, int $yo, int $w, int $h) : void{
		$this->parent->fillArea($layerData, $xo, $yo, $w, $h);

		for ($y = 0; $y < $h; $y++) {
			for ($x = 0; $x < $w; $x++) {
				$this->initRandom($x + $xo, $y + $yo);

				$index = $x + $y * $w;
				$old = $layerData->parentArea[$index];

				$special = ($old & self::SPECIAL_MASK) >> self::SPECIAL_SHIFT;

				$old &= ~self::SPECIAL_MASK;

				if ($this->isOcean($old)) {
					$layerData->result[$index] = $old;
				} elseif ($old === BiomeIds::MUSHROOM_ISLAND) {
					$layerData->result[$index] = $old;
				} elseif ($old === Layer::WARM_ID) {
					if ($special > 0) {
						$layerData->result[$index] = ($this->nextRandom(3) === 0) ? BiomeIds::MESA_CLEAR_ROCK : BiomeIds::MESA_ROCK;
					} else {
						$layerData->result[$index] = $this->warmBiomes[$this->nextRandom(count($this->warmBiomes))];
					}
				} elseif ($old === Layer::MEDIUM_ID) {
					if ($special > 0) {
						$layerData->result[$index] = BiomeIds::JUNGLE;
					} else {
						$layerData->result[$index] = $this->mediumBiomes[$this->nextRandom(count($this->mediumBiomes))];
					}
				} elseif ($old === Layer::COLD_ID) {
					if ($special > 0) {
						$layerData->result[$index] = BiomeIds::REDWOOD_TAIGA;
					} else {
						$layerData->result[$index] = $this->coldBiomes[$this->nextRandom(count($this->coldBiomes))];
					}
				} elseif ($old === Layer::ICE_ID) {
					$layerData->result[$index] = $this->iceBiomes[$this->nextRandom(count($this->iceBiomes))];
				} else {
					$layerData->result[$index] = BiomeIds::MUSHROOM_ISLAND;
				}
			}
		}

		$layerData->swap();
	}
}
