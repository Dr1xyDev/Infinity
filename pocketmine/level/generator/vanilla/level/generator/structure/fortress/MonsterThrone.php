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

use pocketmine\level\generator\vanilla\level\generator\structure\StructureBoundingBox;
use pocketmine\level\generator\vanilla\level\generator\structure\StructureWorld;
use pocketmine\level\generator\vanilla\utils\Random;

/**
 * Raised platform with the blaze spawner
 */
class MonsterThrone extends FortressPiece
{
	public const DIMENSIONS = [-2, 0, 0, 7, 8, 9];

	/** Network id of blazes */
	private const BLAZE = 43;

	private bool $hasSpawner = false;

	public function addComponentParts(StructureWorld $world, Random $random, StructureBoundingBox $box) : bool
	{
		$nb = self::NETHER_BRICK;
		$fence = self::FENCE;
		$this->fillWithAir($world, $box, 0, 2, 0, 6, 7, 7);
		$this->fill($world, $box, 1, 0, 0, 5, 1, 7, $nb);
		$this->fill($world, $box, 1, 2, 1, 5, 2, 7, $nb);
		$this->fill($world, $box, 1, 3, 2, 5, 3, 7, $nb);
		$this->fill($world, $box, 1, 4, 3, 5, 4, 7, $nb);
		$this->fill($world, $box, 1, 2, 0, 1, 4, 2, $nb);
		$this->fill($world, $box, 5, 2, 0, 5, 4, 2, $nb);
		$this->fill($world, $box, 1, 5, 2, 1, 5, 3, $nb);
		$this->fill($world, $box, 5, 5, 2, 5, 5, 3, $nb);
		$this->fill($world, $box, 0, 5, 3, 0, 5, 8, $nb);
		$this->fill($world, $box, 6, 5, 3, 6, 5, 8, $nb);
		$this->fill($world, $box, 1, 5, 8, 5, 5, 8, $nb);
		$this->setBlock($world, $fence, 1, 6, 3, $box);
		$this->setBlock($world, $fence, 5, 6, 3, $box);
		$this->fill($world, $box, 0, 6, 3, 0, 6, 8, $fence);
		$this->fill($world, $box, 6, 6, 3, 6, 6, 8, $fence);
		$this->fill($world, $box, 1, 6, 8, 5, 7, 8, $fence);
		$this->fill($world, $box, 2, 8, 8, 4, 8, 8, $fence);

		if (!$this->hasSpawner && $this->placeSpawner($world, $box, 3, 5, 5, self::BLAZE)) {
			$this->hasSpawner = true;
		}

		for ($x = 0; $x <= 6; ++$x) {
			for ($z = 0; $z <= 6; ++$z) {
				$this->replaceAirAndLiquidDownwards($world, $nb, $x, -1, $z, $box);
			}
		}
		return true;
	}
}
