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

use pocketmine\level\generator\vanilla\level\generator\feature\configurations\SpikeConfiguration;

final class EndFeatures{

	public const END_PLATFORM = "end_platform";
	public const END_SPIKE = "end_spike";
	public const END_GATEWAY_RETURN = "end_gateway_return";
	public const END_GATEWAY_DELAYED = "end_gateway_delayed";
	public const CHORUS_PLANT = "chorus_plant";
	public const END_ISLAND = "end_island";

	private function __construct(){
		//NOOP
	}

	public static function bootstrap(FeatureFactory $featureFactory) : void {
		$featureFactory->register(self::END_PLATFORM, new EndPlatformFeature());
		$featureFactory->register(self::END_SPIKE, new EndSpikeFeature(new SpikeConfiguration([])));
		$featureFactory->register(self::CHORUS_PLANT, new ChorusPlantFeature());
		$featureFactory->register(self::END_ISLAND, new EndIslandFeature());
	}
}
