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

use pocketmine\level\generator\vanilla\level\format\Chunk;
use pocketmine\level\generator\vanilla\level\generator\EndIslandNoiseGenerator;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\Random;

class ChorusPlantPlacement extends PlacementModifier {

	public function __construct(){

	}

	public function getPositions(PlacementContext $context, Random $random, Vector3 $origin) : array{
		$chunkX = $origin->getFloorX() >> Chunk::COORD_BIT_SIZE;
		$chunkZ = $origin->getFloorZ() >> Chunk::COORD_BIT_SIZE;
		if ($chunkX * $chunkX + $chunkZ * $chunkZ <= 4096) {
			return [];
		}

		$generator = $context->getGenerator();
		if ($generator instanceof EndIslandNoiseGenerator) {
			if ($generator->getIslandHeightValue($chunkX, $chunkZ, 1, 1) > 40.0) {
				return [$origin];
			}
		}

		return [];
	}
}
