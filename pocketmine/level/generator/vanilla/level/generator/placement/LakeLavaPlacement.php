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

use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\Random;

class LakeLavaPlacement extends PlacementModifier {

	public function __construct(
		private int $chance
	){}

	public function getPositions(PlacementContext $context, Random $random, Vector3 $origin) : array{
		$generator = $context->getGenerator();
		if ($random->nextBoundedInt($this->chance / 10) == 0) {
			$x = $random->nextBoundedInt(16) + $origin->getX();
			$z = $random->nextBoundedInt(16) + $origin->getZ();
			$y = $random->nextBoundedInt($random->nextBoundedInt($generator->getMaxBuildHeight() - 8) + 8);
			if ($y < $generator->getSeaLevel() || $random->nextBoundedInt($this->chance / 8) == 0) {
				return [new Vector3($x, $y, $z)];
			}
		}

		return [];
	}
}
