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

use function array_fill;
use function assert;

class ZoomLayer extends Layer {
	public const int ZOOM_BITS = 1;
	public const int ZOOM_OFFSET = 1;
	public const int ZOOM_MASK = 1 << (self::ZOOM_BITS - 1);

	protected Layer $parent;

	public function __construct(int $seed, Layer $parent){
		parent::__construct($seed);
		$this->parent = $parent;
	}

	public function fillArea(LayerData $layerData, int $xo, int $yo, int $w, int $h) : void{
		$parentX = $xo >> self::ZOOM_BITS;
		$parentY = $yo >> self::ZOOM_BITS;
		$parentWidth = ($w >> self::ZOOM_BITS) + 2;
		$parentHeight = ($h >> self::ZOOM_BITS) + 2;

		$this->parent->fillArea($layerData, $parentX, $parentY, $parentWidth, $parentHeight);

		$scaledWidth = ($parentWidth - self::ZOOM_OFFSET) << self::ZOOM_BITS;
		$scaledHeight = ($parentHeight - self::ZOOM_OFFSET) << self::ZOOM_BITS;
		assert($scaledWidth * $scaledHeight <= 32 * 32, "Zoomlayer is too small!");

		$scaledArea = array_fill(0, 32 * 32, 0);

		for ($y = 0; $y < ($parentHeight - self::ZOOM_OFFSET); $y++) {
			$scaledPosition = ($y << self::ZOOM_BITS) * $scaledWidth;

			$x = 0;
			$upperLeft = $layerData->parentArea[($x + 0) + ($y + 0) * $parentWidth];
			$downLeft = $layerData->parentArea[($x + 0) + ($y + 1) * $parentWidth];

			for (; $x < ($parentWidth - self::ZOOM_OFFSET); $x++) {
				$this->initRandom(($x + $parentX) << self::ZOOM_BITS, ($y + $parentY) << self::ZOOM_BITS);

				$upperRight = $layerData->parentArea[($x + 1) + ($y + 0) * $parentWidth];
				$downRight = $layerData->parentArea[($x + 1) + ($y + 1) * $parentWidth];

				$scaledArea[$scaledPosition] = $upperLeft;

				$scaledArea[$scaledPosition + $scaledWidth] = $this->random([$upperLeft, $downLeft]);

				$scaledPosition++;

				$scaledArea[$scaledPosition] = $this->random([$upperLeft, $upperRight]);

				$scaledArea[$scaledPosition + $scaledWidth] = $this->modeOrRandom($upperLeft, $upperRight, $downLeft, $downRight);

				$scaledPosition++;

				$upperLeft = $upperRight;
				$downLeft = $downRight;
			}
		}

		for ($y = 0; $y < $h; $y++) {
			$scaledOffsetPosition = ($y + ($yo & self::ZOOM_MASK)) * $scaledWidth + ($xo & self::ZOOM_MASK);
			$destOffsetPosition = $y * $w;

			for ($i = 0; $i < $w; $i++) {
				$layerData->result[$destOffsetPosition + $i] = $scaledArea[$scaledOffsetPosition + $i];
			}
		}

		$layerData->swap();
	}

	public static function zoom(int $seed, Layer $sup, int $count) : Layer{
		$result = $sup;
		for ($i = 0; $i < $count; $i++) {
			$result = new ZoomLayer($seed + 1, $result);
		}

		return $result;
	}
}
