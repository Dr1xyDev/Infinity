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

use pocketmine\level\generator\vanilla\level\generator\feature\FeatureSpread;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\Random;

class FirePlacement extends PlacementModifier {
	public function __construct(
		private FeatureSpread $spread
	){}

	public function getPositions(PlacementContext $context, Random $random, Vector3 $origin) : array{
		$list = [];

		for($i = 0; $i < $random->nextBoundedInt($random->nextBoundedInt($this->spread->getCount($random)) + 1) + 1; ++$i) {
			$x = $random->nextBoundedInt(16) + $origin->getX();
			$z = $random->nextBoundedInt(16) + $origin->getZ();
			$y = $random->nextBoundedInt(120) + 4;
			$list[] = new Vector3($x, $y, $z);
		}

		return $list;
	}
}
