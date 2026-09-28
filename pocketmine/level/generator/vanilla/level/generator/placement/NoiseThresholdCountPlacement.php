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

namespace pocketmine\level\generator\vanilla\level\generator\placement;

use pocketmine\level\generator\vanilla\level\biome\BiomeNoise;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\Random;

class NoiseThresholdCountPlacement extends RepeatingPlacement {

	public static function of(float $noiseLevel, int $belowNoise, int $aboveNoise) : NoiseThresholdCountPlacement {
		return new self($noiseLevel, $belowNoise, $aboveNoise);
	}

	public function __construct(
		private float $noiseLevel,
		private int $belowNoise,
		private int $aboveNoise
	){}

	protected function count(Random $random, Vector3 $origin) : int{
		$flowerNoise = BiomeNoise::getInstance()->getInfoNoise()->getValue2D($origin->getX() / 200.0, $origin->getZ() / 200.0);
		return $flowerNoise < $this->noiseLevel ? $this->belowNoise : $this->aboveNoise;
	}
}
