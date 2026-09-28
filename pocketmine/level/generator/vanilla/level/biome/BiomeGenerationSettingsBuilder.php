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

use pocketmine\level\generator\vanilla\level\generator\GenerationStageDecoration;
use pocketmine\level\generator\vanilla\level\generator\placement\PlacedFeature;
use pocketmine\level\generator\vanilla\level\generator\surfacebuilders\ConfiguredSurfaceBuilder;
use function count;

class BiomeGenerationSettingsBuilder {

	private ?ConfiguredSurfaceBuilder $surfaceBuilder = null;
	/** @var array<int, array<PlacedFeature>> */
	private array $features = [];

	public function __construct(){
		//NOOP
	}

	public function withSurfaceBuilder(ConfiguredSurfaceBuilder $configuredSurfaceBuilder) : self {
		$this->surfaceBuilder = $configuredSurfaceBuilder;
		return $this;
	}

	public function withFeature(GenerationStageDecoration $decorationStage, PlacedFeature $feature) : self {
		$this->populateStageEntries($decorationStage->value);
		$this->features[$decorationStage->value][] = $feature;
		return $this;
	}

	private function populateStageEntries(int $stage) : void {
		while(count($this->features) <= $stage) {
			$this->features[] = [];
		}
	}

	public function build() : BiomeGenerationSettings {
		if ($this->surfaceBuilder === null) {
			throw new \InvalidArgumentException("Surface Builder is not defined");
		}

		return new BiomeGenerationSettings($this->surfaceBuilder, $this->features);
	}
}
