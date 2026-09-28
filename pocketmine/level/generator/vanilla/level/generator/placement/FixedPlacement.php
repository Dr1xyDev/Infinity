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
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\utils\Random;

class FixedPlacement extends PlacementModifier {

	/**
	 * @param Vector3[] $positions
	 */
	public function __construct(
		private array $positions
	){}

	public function getPositions(PlacementContext $context, Random $random, Vector3 $origin) : array{
		$chunkX = $origin->getX() >> Chunk::COORD_BIT_SIZE;
		$chunkZ = $origin->getZ() >> Chunk::COORD_BIT_SIZE;

		$filter = [];
		foreach($this->positions as $position){
			if (self::isSameChunk($chunkX, $chunkZ, $position)) {
				$filter[] = $position;
			}
		}

		return $filter;
	}

	private static function isSameChunk(int $chunkX, int $chunkZ, Vector3 $position) : bool {
		return $chunkX === ($position->getFloorX() >> Chunk::COORD_BIT_SIZE) && $chunkZ === ($position->getFloorZ() >> Chunk::COORD_BIT_SIZE);
	}
}
