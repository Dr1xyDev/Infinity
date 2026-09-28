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
use function ceil;

class NoiseBasedCountPlacement extends RepeatingPlacement {

	public static function of(float $noiseToCountRatio, int $noiseFactor, int $noiseOffset) : NoiseBasedCountPlacement {
		return new self($noiseToCountRatio, $noiseFactor, $noiseOffset);
	}

	public function __construct(
		private float $noiseToCountRatio,
		private int $noiseFactor,
		private int $noiseOffset
	){}

	protected function count(Random $random, Vector3 $origin) : int{
		$flowerNoise = BiomeNoise::getInstance()->getInfoNoise()->getValue2D($origin->getX() / $this->noiseFactor, $origin->getZ() / $this->noiseFactor);
		return (int) ceil(($flowerNoise + $this->noiseOffset) * $this->noiseToCountRatio);
	}
}
