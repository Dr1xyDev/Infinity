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

namespace pocketmine\level\generator\vanilla\level\generator\feature;

use pocketmine\level\generator\vanilla\utils\SingletonTrait;

class FeatureFactory {
	use SingletonTrait;

	/** @var Feature[] */
	private array $features = [];

	public function __construct(){
		//TODO: AquaticFeatures
		CaveFeatures::bootstrap($this);
		MiscOverworldFeatures::bootstrap($this);
		//TODO: NetherFeatures
		OreFeatures::bootstrap($this);
		//TODO: PileFeatures
		TreeFeatures::bootstrap($this);
		VegetationFeatures::bootstrap($this);
	}

	public function register(string $name, Feature $feature) : void {
		$this->features[$name] = $feature;
	}

	public function get(string $name) : ?Feature{
		return $this->features[$name] ?? null;
	}
}
