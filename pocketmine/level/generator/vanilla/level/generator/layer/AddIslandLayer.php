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

class AddIslandLayer extends Layer {

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
				$n1 = $layerData->parentArea[($x + 0) + ($y + 0) * $pw];
				$n2 = $layerData->parentArea[($x + 2) + ($y + 0) * $pw];
				$n3 = $layerData->parentArea[($x + 0) + ($y + 2) * $pw];
				$n4 = $layerData->parentArea[($x + 2) + ($y + 2) * $pw];
				$c = $layerData->parentArea[($x + 1) + ($y + 1) * $pw];

				$this->initRandom($x + $xo, $y + $yo);

				$resultIndex = $x + $y * $w;

				if ($c === 0 && ($n1 !== 0 || $n2 !== 0 || $n3 !== 0 || $n4 !== 0)) {
					$odds = 1;
					$swap = Layer::WARM_ID;

					if ($n1 !== 0 && $this->nextRandom($odds++) === 0) {
						$swap = $n1;
					}
					if ($n2 !== 0 && $this->nextRandom($odds++) === 0) {
						$swap = $n2;
					}
					if ($n3 !== 0 && $this->nextRandom($odds++) === 0) {
						$swap = $n3;
					}
					if ($n4 !== 0 && $this->nextRandom($odds++) === 0) {
						$swap = $n4;
					}

					if ($this->nextRandom(3) === 0) {
						$layerData->result[$resultIndex] = $swap;
					} else {
						if ($swap === Layer::ICE_ID) {
							$layerData->result[$resultIndex] = Layer::ICE_ID;
						} else {
							$layerData->result[$resultIndex] = 0;
						}
					}
				} elseif ($c > 0 && ($n1 === 0 || $n2 === 0 || $n3 === 0 || $n4 === 0)) {
					if ($this->nextRandom(5) === 0) {
						if ($c === Layer::ICE_ID) {
							$layerData->result[$resultIndex] = Layer::ICE_ID;
						} else {
							$layerData->result[$resultIndex] = 0;
						}
					} else {
						$layerData->result[$resultIndex] = $c;
					}
				} else {
					$layerData->result[$resultIndex] = $c;
				}
			}
		}

		$layerData->swap();
	}
}
