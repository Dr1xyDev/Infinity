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

use pocketmine\level\generator\vanilla\level\generator\feature\EndFeatures;
use pocketmine\level\generator\vanilla\level\generator\feature\FeatureFactory;
use pocketmine\level\generator\vanilla\level\generator\verticalanchor\VerticalAnchor;
use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\valueproviders\UniformInt;

final class EndPlacements{

	public const END_PLATFORM = "end_platform";
	public const END_SPIKE = "end_spike";
	public const END_GATEWAY_RETURN = "end_gateway_return";
	public const END_GATEWAY_DELAYED = "end_gateway_delayed";
	public const CHORUS_PLANT = "chorus_plant";
	public const END_ISLAND_DECORATED = "end_island_decorated";

	private function __construct(){
		//NOOP
	}

	public static function bootstrap(PlacementFactory $placementFactory) : void {
		$features = FeatureFactory::getInstance();

		$placementFactory->register(self::END_PLATFORM, $features->get(EndFeatures::END_PLATFORM), [
			new FixedPlacement([new Vector3(Level::END_SPAWN_POINT_X, Level::END_SPAWN_POINT_Y, Level::END_SPAWN_POINT_Z)])
		]);
		$placementFactory->register(self::END_SPIKE, $features->get(EndFeatures::END_SPIKE), []);

		$placementFactory->register(self::CHORUS_PLANT, $features->get(EndFeatures::CHORUS_PLANT), [
			new ChorusPlantPlacement(),
			new CountPlacement(UniformInt::of(0, 4)),
			new InSquarePlacement(),
			new HeightmapPlacement(HeightmantType::SOLID)
		]);
		$placementFactory->register(self::END_ISLAND_DECORATED, $features->get(EndFeatures::END_ISLAND), [
			new EndIslandPlacement(),
			new RarityFilter(14),
			CountPlacement::countExtra(1, 0.25, 1),
			new InSquarePlacement(),
			HeightRangePlacement::uniform(VerticalAnchor::absolute(55), VerticalAnchor::absolute(70))
		]);
	}
}
