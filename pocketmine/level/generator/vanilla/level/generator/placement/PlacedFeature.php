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

use pocketmine\level\generator\vanilla\level\ChunkManager;
use pocketmine\level\generator\vanilla\level\generator\feature\Feature;
use pocketmine\level\generator\vanilla\level\generator\feature\FeaturePlaceContext;
use pocketmine\level\generator\vanilla\level\generator\Generator;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\Random;

class PlacedFeature {
	/**
	 * @param PlacementModifier[] $placement
	 */
	public function __construct(
		public Feature $feature,
		public array $placement
	){}

	public function place(ChunkManager $level, Generator $generator, Random $random, Vector3 $origin) : bool {
		return $this->placeWithContext(new PlacementContext($level, $generator), $random, $origin);
	}

	public function placeWithContext(PlacementContext $context, Random $random, Vector3 $origin) : bool {
		$placements = [$origin];

		foreach ($this->placement as $modifier) {
			$newPlacements = [];
			foreach ($placements as $pos) {
				$positions = $modifier->getPositions($context, $random, $pos);
				foreach ($positions as $newPos) {
					$newPlacements[] = $newPos;
				}
			}
			$placements = $newPlacements;
		}

		$feature = $this->feature;
		$placedAny = false;

		foreach ($placements as $pos) {
			if ($feature->place(new FeaturePlaceContext($context->getLevel(), $random, $pos))) {
				$placedAny = true;
			}
		}

		return $placedAny;
	}
}
