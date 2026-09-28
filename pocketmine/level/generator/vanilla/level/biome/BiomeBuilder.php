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

namespace pocketmine\level\generator\vanilla\level\biome;

class BiomeBuilder {
	private ?RainType $precipitation = null;
	private ?BiomeCategory $category = null;
	private ?float $depth = null;
	private ?float $scale = null;
	private ?float $temperature = null;
	private ?float $downfall = null;
	private ?BiomeGenerationSettings $generationSettings = null;

	public function precipitation(RainType $precipitation) : self {
		$this->precipitation = $precipitation;
		return $this;
	}

	public function category(BiomeCategory $biomeCategory) : self {
		$this->category = $biomeCategory;
		return $this;
	}

	public function depth(float $depthIn) : self {
		$this->depth = $depthIn;
		return $this;
	}

	public function scale(float $scaleIn) : self {
		$this->scale = $scaleIn;
		return $this;
	}

	public function temperature(float $temperature) : self {
		$this->temperature = $temperature;
		return $this;
	}

	public function downfall(float $downfallIn) : self {
		$this->downfall = $downfallIn;
		return $this;
	}

	public function withGenerationSettings(BiomeGenerationSettings $generationSettings) : self {
		$this->generationSettings = $generationSettings;
		return $this;
	}

	public function build() : Biome {
		if (
			$this->precipitation !== null &&
			$this->category !== null &&
			$this->depth !== null &&
			$this->scale !== null &&
			$this->temperature !== null &&
			$this->downfall !== null &&
			$this->generationSettings !== null
		) {
			return new Biome(
				new BiomeClimate(
					$this->precipitation,
					$this->temperature,
					$this->downfall
				),
				$this->category,
				$this->depth,
				$this->scale,
				$this->generationSettings,
			);
		} else {
			throw new \InvalidArgumentException("You are missing parameters to build a proper biome");
		}
	}
}
