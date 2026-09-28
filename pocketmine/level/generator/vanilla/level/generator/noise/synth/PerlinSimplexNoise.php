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

namespace pocketmine\level\generator\vanilla\level\generator\noise\synth;

use pocketmine\level\generator\vanilla\utils\Random;

class PerlinSimplexNoise {
	/** @var SimplexNoise[] */
	protected array $mNoiseLevels = [];

	protected int $mLevels;

	public function __construct(Random $random, int $levels){
		$this->mLevels = $levels;

		for ($i = 0; $i < $levels; $i++) {
			$this->mNoiseLevels[] = new SimplexNoise($random);
		}
	}

	public function getValue2D(float $x, float $y) : float
	{
		$value = 0.0;
		$pow = 1.0;

		for ($i = 0; $i < $this->mLevels; $i++) {
			$value += $this->mNoiseLevels[$i]->getValue2D($x * $pow, $y * $pow) / $pow;
			$pow *= 0.5;
		}

		return $value;
	}

	public function getValue3D(float $x, float $y, float $z) : float
	{
		$value = 0.0;
		$pow = 1.0;

		for ($i = 0; $i < $this->mLevels; $i++) {
			$value += $this->mNoiseLevels[$i]->getValue3D($x * $pow, $y * $pow, $z * $pow) / $pow;
			$pow *= 0.5;
		}

		return $value;
	}

	public function getRegion2D(
		array &$buffer,
		float $posX, float $posY,
		int $xSize, int $ySize,
		float $scaleX, float $scaleY,
		float $sizeScale = 1.0, float $powScale = 0.5
	) : void {
		$size = $xSize * $ySize;

		for ($i = 0; $i < $size; $i++) {
			$buffer[$i] = 0.0;
		}

		$pow = 1.0;
		$mult = 1.0;

		for ($i = 0; $i < $this->mLevels; $i++) {
			$this->mNoiseLevels[$i]->add2D(
				$buffer,
				$posX, $posY,
				$xSize, $ySize,
				$scaleX * $mult * $pow, $scaleY * $mult * $pow,
				0.55 / $pow
			);
			$mult *= $sizeScale;
			$pow *= $powScale;
		}
	}

	public function getRegion3D(
		array &$buffer,
		float $posX, float $posY, float $posZ,
		int $xSize, int $ySize, int $zSize,
		float $scaleX, float $scaleY, float $scaleZ
	) : void {
		$size = $xSize * $ySize * $zSize;

		for ($i = 0; $i < $size; $i++) {
			$buffer[$i] = 0.0;
		}

		$pow = 1.0;

		for ($i = 0; $i < $this->mLevels; $i++) {
			$this->mNoiseLevels[$i]->add3D(
				$buffer,
				$posX, $posY, $posZ,
				$xSize, $ySize, $zSize,
				$scaleX * $pow, $scaleY * $pow, $scaleZ * $pow,
				0.55 / $pow
			);
			$pow *= 0.5;
		}
	}
}
