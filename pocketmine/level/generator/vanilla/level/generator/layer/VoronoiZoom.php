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

class VoronoiZoom extends Layer {

	public const int ZOOM_BITS = 2;
	public const int ZOOM_OFFSET = 1;
	public const int ZOOM = 1 << self::ZOOM_BITS;
	public const int ZOOM_MASK = self::ZOOM - 1;

	protected Layer $parent;

	public function __construct(int $seed, Layer $parent){
		parent::__construct($seed);
		$this->parent = $parent;
	}

	public function fillArea(LayerData $layerData, int $xo, int $yo, int $w, int $h) : void{
		$xo -= self::ZOOM / 2;
		$yo -= self::ZOOM / 2;

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
			$x = 0;

			$upperLeft = $layerData->parentArea[($x + 0) + ($y + 0) * $parentWidth];
			$downLeft = $layerData->parentArea[($x + 0) + ($y + 1) * $parentWidth];

			for (; $x < ($parentWidth - self::ZOOM_OFFSET); $x++) {
				$s = self::ZOOM * 0.9;

				$this->initRandom(($x + $parentX) << self::ZOOM_BITS, ($y + $parentY) << self::ZOOM_BITS);
				$x0 = ($this->nextRandom(1024) / 1024.0 - 0.5) * $s;
				$y0 = ($this->nextRandom(1024) / 1024.0 - 0.5) * $s;

				$this->initRandom(($x + $parentX + 1) << self::ZOOM_BITS, ($y + $parentY) << self::ZOOM_BITS);
				$x1 = ($this->nextRandom(1024) / 1024.0 - 0.5) * $s + self::ZOOM;
				$y1 = ($this->nextRandom(1024) / 1024.0 - 0.5) * $s;

				$this->initRandom(($x + $parentX) << self::ZOOM_BITS, ($y + $parentY + 1) << self::ZOOM_BITS);
				$x2 = ($this->nextRandom(1024) / 1024.0 - 0.5) * $s;
				$y2 = ($this->nextRandom(1024) / 1024.0 - 0.5) * $s + self::ZOOM;

				$this->initRandom(($x + $parentX + 1) << self::ZOOM_BITS, ($y + $parentY + 1) << self::ZOOM_BITS);
				$x3 = ($this->nextRandom(1024) / 1024.0 - 0.5) * $s + self::ZOOM;
				$y3 = ($this->nextRandom(1024) / 1024.0 - 0.5) * $s + self::ZOOM;

				$upperRight = $layerData->parentArea[($x + 1) + ($y + 0) * $parentWidth] & 0xFF;
				$downRight = $layerData->parentArea[($x + 1) + ($y + 1) * $parentWidth] & 0xFF;

				for ($yy = 0; $yy < self::ZOOM; $yy++) {
					$scaledPosition = (($y << self::ZOOM_BITS) + $yy) * $scaledWidth + ($x << self::ZOOM_BITS);

					for ($xx = 0; $xx < self::ZOOM; $xx++) {
						$d0 = (($yy - $y0) * ($yy - $y0) + ($xx - $x0) * ($xx - $x0));
						$d1 = (($yy - $y1) * ($yy - $y1) + ($xx - $x1) * ($xx - $x1));
						$d2 = (($yy - $y2) * ($yy - $y2) + ($xx - $x2) * ($xx - $x2));
						$d3 = (($yy - $y3) * ($yy - $y3) + ($xx - $x3) * ($xx - $x3));

						if ($d0 < $d1 && $d0 < $d2 && $d0 < $d3) {
							$scaledArea[$scaledPosition++] = $upperLeft;
						} elseif ($d1 < $d0 && $d1 < $d2 && $d1 < $d3) {
							$scaledArea[$scaledPosition++] = $upperRight;
						} elseif ($d2 < $d0 && $d2 < $d1 && $d2 < $d3) {
							$scaledArea[$scaledPosition++] = $downLeft;
						} else {
							$scaledArea[$scaledPosition++] = $downRight;
						}
					}
				}

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
}
