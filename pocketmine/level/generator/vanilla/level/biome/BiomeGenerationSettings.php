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

use pocketmine\level\generator\vanilla\level\generator\feature\Feature;
use pocketmine\level\generator\vanilla\level\generator\feature\FlowersFeature;
use pocketmine\level\generator\vanilla\level\generator\placement\PlacedFeature;
use pocketmine\level\generator\vanilla\level\generator\surfacebuilders\ConfiguredSurfaceBuilder;

class BiomeGenerationSettings {

	/** @var array<Feature> */
	private array $flowerFeatures = [];

	/**
	 * @param array<int, array<PlacedFeature>> $features
	 */
	public function __construct(
		private ConfiguredSurfaceBuilder $surfaceBuilder,
		private array $features
	){
		foreach ($this->features as $stage => $listFeatures) {
			foreach ($listFeatures as $placedFeature) {
				$feature = $placedFeature->feature;
				if ($feature instanceof FlowersFeature) {
					$this->flowerFeatures[] = $feature;
				}
			}
		}
	}

	public function getSurfaceBuilder() : ConfiguredSurfaceBuilder {
		return $this->surfaceBuilder;
	}

	public function getFeatures() : array {
		return $this->features;
	}

	public function getFlowerFeatures() : array {
		return $this->flowerFeatures;
	}
}
