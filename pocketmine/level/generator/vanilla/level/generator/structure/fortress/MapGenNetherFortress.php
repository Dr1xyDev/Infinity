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

namespace pocketmine\level\generator\vanilla\level\generator\structure\fortress;

use pocketmine\level\generator\vanilla\level\generator\structure\MapGenStructure;
use pocketmine\level\generator\vanilla\level\generator\structure\StructureStart;
use pocketmine\level\generator\vanilla\utils\Random;

/**
 * Nether fortresses: at most one in every region of 16x16 chunks
 */
class MapGenNetherFortress extends MapGenStructure
{
	protected function canSpawnStructureAtCoords(int $chunkX, int $chunkZ, Random $random) : bool
	{
		$regionX = $chunkX >> 4;
		$regionZ = $chunkZ >> 4;
		$random->setSeed(($regionX ^ ($regionZ << 4)) ^ $this->worldSeed);
		$random->nextInt();
		if ($random->nextBoundedInt(3) !== 0) {
			return false;
		}
		if ($chunkX !== ($regionX << 4) + 4 + $random->nextBoundedInt(8)) {
			return false;
		}
		return $chunkZ === ($regionZ << 4) + 4 + $random->nextBoundedInt(8);
	}

	protected function createStart(int $chunkX, int $chunkZ, Random $random) : StructureStart
	{
		return new FortressStart($chunkX, $chunkZ, $random);
	}
}
