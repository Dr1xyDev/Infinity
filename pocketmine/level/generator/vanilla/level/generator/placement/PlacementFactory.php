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

use pocketmine\level\generator\vanilla\level\generator\feature\Feature;
use pocketmine\level\generator\vanilla\utils\SingletonTrait;

class PlacementFactory {
	use SingletonTrait;

	/** @var PlacedFeature[] */
	private array $placements = [];

	public function __construct(){
		CavePlacements::bootstrap($this);
		EndPlacements::bootstrap($this);
		MiscOverworldPlacements::bootstrap($this);
		OrePlacements::bootstrap($this);
		VegetationPlacements::bootstrap($this);
	}

	/**
	 * @param PlacementModifier[] $placementModifiers
	 */
	public function register(string $name, Feature $feature, array $placementModifiers) : void {
		$this->placements[$name] = new PlacedFeature($feature, $placementModifiers);
	}

	public function get(string $name) : ?PlacedFeature{
		return $this->placements[$name] ?? null;
	}
}
