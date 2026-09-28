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

class AddEdgeLayer extends Layer {

	public const MODE_COOL_WARM = 0;
	public const MODE_HEAT_ICE = 1;
	public const MODE_SPECIAL = 2;

	protected const OFFSET = 1;

	protected int $mode;
	protected Layer $parent;

	public function __construct(int $seed, Layer $parent, int $mode = self::MODE_COOL_WARM){
		parent::__construct($seed);
		$this->parent = $parent;
		$this->mode = $mode;
	}

	public function fillArea(LayerData $layerData, int $xo, int $yo, int $w, int $h) : void{
		switch ($this->mode) {
			case self::MODE_COOL_WARM:
				$this->fillCoolWarm($layerData, $xo, $yo, $w, $h);
				break;
			case self::MODE_HEAT_ICE:
				$this->fillHeatIce($layerData, $xo, $yo, $w, $h);
				break;
			case self::MODE_SPECIAL:
			default:
				$this->fillIntroduceSpecial($layerData, $xo, $yo, $w, $h);
				break;
		}
	}

	protected function fillCoolWarm(LayerData $layerData, int $originX, int $originY, int $width, int $height) : void{
		$parentX = $originX - self::OFFSET;
		$parentY = $originY - self::OFFSET;
		$parentWidth = self::OFFSET + $width + self::OFFSET;
		$parentHeight = self::OFFSET + $height + self::OFFSET;

		$this->parent->fillArea($layerData, $parentX, $parentY, $parentWidth, $parentHeight);

		for ($y = 0; $y < $height; $y++) {
			for ($x = 0; $x < $width; $x++) {
				$this->initRandom($x + $originX, $y + $originY);

				$centerIndex = ($x + self::OFFSET) + ($y + self::OFFSET) * $parentWidth;
				$value = $layerData->parentArea[$centerIndex];

				if ($value === Layer::WARM_ID) {
					$n = $layerData->parentArea[$centerIndex - $parentWidth];
					$e = $layerData->parentArea[$centerIndex + 1];
					$w = $layerData->parentArea[$centerIndex - 1];
					$s = $layerData->parentArea[$centerIndex + $parentWidth];

					$coldNeighbor = ($n === Layer::COLD_ID || $e === Layer::COLD_ID || $w === Layer::COLD_ID || $s === Layer::COLD_ID);
					$iceNeighbor = ($n === Layer::ICE_ID || $e === Layer::ICE_ID || $w === Layer::ICE_ID || $s === Layer::ICE_ID);

					if ($coldNeighbor || $iceNeighbor) {
						$value = Layer::MEDIUM_ID;
					}
				}

				$layerData->result[$x + $y * $width] = $value;
			}
		}

		$layerData->swap();
	}

	protected function fillHeatIce(LayerData $layerData, int $originX, int $originY, int $width, int $height) : void{
		$parentX = $originX - self::OFFSET;
		$parentY = $originY - self::OFFSET;
		$parentWidth = self::OFFSET + $width + self::OFFSET;
		$parentHeight = self::OFFSET + $height + self::OFFSET;

		$this->parent->fillArea($layerData, $parentX, $parentY, $parentWidth, $parentHeight);

		for ($y = 0; $y < $height; $y++) {
			for ($x = 0; $x < $width; $x++) {

				$centerIndex = ($x + self::OFFSET) + ($y + self::OFFSET) * $parentWidth;
				$value = $layerData->parentArea[$centerIndex];

				if ($value === Layer::ICE_ID) {
					$n = $layerData->parentArea[$centerIndex - $parentWidth];
					$e = $layerData->parentArea[$centerIndex + 1];
					$w = $layerData->parentArea[$centerIndex - 1];
					$s = $layerData->parentArea[$centerIndex + $parentWidth];

					$mediumNeighbor = ($n === Layer::MEDIUM_ID || $e === Layer::MEDIUM_ID || $w === Layer::MEDIUM_ID || $s === Layer::MEDIUM_ID);
					$warmNeighbor = ($n === Layer::WARM_ID || $e === Layer::WARM_ID || $w === Layer::WARM_ID || $s === Layer::WARM_ID);

					if ($warmNeighbor || $mediumNeighbor) {
						$value = Layer::COLD_ID;
					}
				}

				$layerData->result[$x + $y * $width] = $value;
			}
		}

		$layerData->swap();
	}

	protected function fillIntroduceSpecial(LayerData $layerData, int $originX, int $originY, int $width, int $height) : void{
		$this->parent->fillArea($layerData, $originX, $originY, $width, $height);

		for ($y = 0; $y < $height; $y++) {
			for ($x = 0; $x < $width; $x++) {
				$this->initRandom($x + $originX, $y + $originY);

				$index = $x + $y * $width;
				$value = $layerData->parentArea[$index];

				if ($value !== 0 && $this->nextRandom(13) === 0) {
					$mutation = (1 + $this->nextRandom(15)) << self::SPECIAL_SHIFT;
					$value |= ($mutation & self::SPECIAL_MASK);
				}

				$layerData->result[$index] = $value;
			}
		}

		$layerData->swap();
	}
}
