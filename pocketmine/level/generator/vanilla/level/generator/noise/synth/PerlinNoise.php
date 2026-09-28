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

class PerlinNoise {
	/** @var ImprovedNoise[] */
	protected array $mNoiseLevels = [];

	protected int $mLevels;
	protected int $mMinLevel;

	public function __construct(Random $random, int $levels, int $minLevel = 0){
		$this->mLevels = $levels;
		$this->mMinLevel = $minLevel;

		for ($i = 0; $i < $levels; $i++) {
			$this->mNoiseLevels[] = new ImprovedNoise($random);
		}
	}

	public function getValue(float $posX, float $posY, float $posZ) : float{
		$value = 0.0;
		$pow = 1.0;

		for ($i = 0; $i < $this->mMinLevel; $i++) {
			$pow *= 0.5;
		}

		for ($i = $this->mMinLevel; $i < $this->mLevels; $i++) {
			$value += $this->mNoiseLevels[$i]->getValue($posX * $pow, $posY * $pow, $posZ * $pow) / $pow;
			$pow *= 0.5;
		}

		return $value;
	}

	public function getRegion(
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
		for ($i = 0; $i < $this->mMinLevel; $i++) {
			$pow *= 0.5;
		}

		for ($i = $this->mMinLevel; $i < $this->mLevels; $i++) {
			$this->mNoiseLevels[$i]->readArea(
				$buffer,
				$posX, $posY, $posZ,
				$xSize, $ySize, $zSize,
				$scaleX * $pow, $scaleY * $pow, $scaleZ * $pow,
				$pow
			);
			$pow *= 0.5;
		}
	}

	public function getRegion2D(
		array &$buffer,
		float $posX, float $posZ,
		int $xSize, int $zSize,
		float $scaleX, float $scaleZ
	) : void {
		$this->getRegion(
			$buffer,
			$posX, 10.0, $posZ,
			$xSize, 1, $zSize,
			$scaleX, 1.0, $scaleZ
		);
	}

	public function hashCode() : int
	{
		$x = 4711;

		for ($i = 0; $i < $this->mLevels; $i++) {
			$x *= $this->mNoiseLevels[$i]->hashCode();
		}

		return $x;
	}
}
