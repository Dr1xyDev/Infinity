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

namespace pocketmine\level\generator\vanilla\level\generator\layer;

class LayerUtils {

	public static function getDefaultLayers(int $seed, bool $legacyBiomes) : array{
		$islandLayer = new IslandLayer($seed + 1);
		$islandLayer = new FuzzyZoomLayer($seed + 2000, $islandLayer);
		$islandLayer = new AddIslandLayer($seed + 1, $islandLayer);
		$islandLayer = new ZoomLayer($seed + 2001, $islandLayer);
		$islandLayer = new AddIslandLayer($seed + 2, $islandLayer);
		$islandLayer = new AddIslandLayer($seed + 50, $islandLayer);
		$islandLayer = new AddIslandLayer($seed + 70, $islandLayer);
		$islandLayer = new RemoveTooMuchOceanLayer($seed + 2, $islandLayer);
		$islandLayer = new AddSnowLayer($seed + 2, $islandLayer);
		$islandLayer = new AddIslandLayer($seed + 3, $islandLayer);
		$islandLayer = new AddEdgeLayer($seed + 2, $islandLayer, AddEdgeLayer::MODE_COOL_WARM);
		$islandLayer = new AddEdgeLayer($seed + 2, $islandLayer, AddEdgeLayer::MODE_HEAT_ICE);
		$islandLayer = new AddEdgeLayer($seed + 3, $islandLayer, AddEdgeLayer::MODE_SPECIAL);
		$islandLayer = new ZoomLayer($seed + 2002, $islandLayer);
		$islandLayer = new ZoomLayer($seed + 2003, $islandLayer);
		$islandLayer = new AddIslandLayer($seed + 4, $islandLayer);
		$islandLayer = new AddMushroomIslandLayer($seed + 5, $islandLayer);
		$islandLayer = new AddDeepOceanLayer($seed + 4, $islandLayer);
		$islandLayer = ZoomLayer::zoom($seed + 1000, $islandLayer, 0);

		$zoomLevel = $legacyBiomes ? 2 : 4;

		$riverLayer = $islandLayer;
		$riverLayer = ZoomLayer::zoom($seed + 1000, $riverLayer, 0);
		$riverLayer = new RiverInitLayer($seed + 100, $riverLayer);

		$biomeLayer = $islandLayer;
		$biomeLayer = new BiomeInitLayer($seed + 200, $biomeLayer, $legacyBiomes);

		$biomeLayer = ZoomLayer::zoom($seed + 1000, $biomeLayer, 2);
		$biomeLayer = new BiomeEdgeLayer($seed + 1000, $biomeLayer, !$legacyBiomes);

		$biomeRiverLayer = $riverLayer;
		$biomeRiverLayer = ZoomLayer::zoom($seed + 1000, $biomeRiverLayer, 2);
		$biomeLayer = new RegionHillsLayer($seed + 1000, $biomeLayer, $biomeRiverLayer);

		$riverLayer = ZoomLayer::zoom($seed + 1000, $riverLayer, 2);
		$riverLayer = ZoomLayer::zoom($seed + 1000, $riverLayer, $zoomLevel);
		$riverLayer = new RiverLayer($seed + 1, $riverLayer);
		$riverLayer = new SmoothLayer($seed + 1000, $riverLayer);

		$biomeLayer = new RareBiomeSpotLayer($seed + 1001, $biomeLayer);
		for ($i = 0; $i < $zoomLevel; $i++) {
			$biomeLayer = new ZoomLayer($seed + 1000 + $i, $biomeLayer);
			if ($i == 0) {
				$biomeLayer = new AddIslandLayer($seed + 3, $biomeLayer);
			}

			if ($i == 1) {
				$biomeLayer = new ShoreLayer($seed + 1000, $biomeLayer);
			}
		}

		$biomeLayer = new SmoothLayer($seed + 1000, $biomeLayer);

		$biomeLayer = new RiverMixerLayer($seed + 100, $biomeLayer, $riverLayer);

		$zoomedLayer = new VoronoiZoom($seed + 10, $biomeLayer);

		return [$biomeLayer, $zoomedLayer];
	}
}
