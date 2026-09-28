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

use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\level\generator\structure\StructureBoundingBox;
use pocketmine\level\generator\vanilla\level\generator\structure\StructureWorld;
use pocketmine\level\generator\vanilla\utils\Random;
use function max;
use function min;

/**
 * Corridor with stairs going 6 blocks down
 */
class CastleStairs extends FortressPiece
{
	public const DIMENSIONS = [-1, -7, 0, 5, 14, 10];

	protected function buildFortressComponent(FortressStartPiece $start, array &$pieces, Random $random) : void
	{
		$this->getNextComponentNormal($start, $pieces, $random, 1, 0, true);
	}

	public function addComponentParts(StructureWorld $world, Random $random, StructureBoundingBox $box) : bool
	{
		$nb = self::NETHER_BRICK;
		$fence = self::FENCE;
		//the floor gets lower going forward, so the stairs go up backwards
		$stairs = self::full(BlockIds::NETHER_BRICK_STAIRS, $this->stairsMeta(0, -1));
		for ($z = 0; $z <= 9; ++$z) {
			$floor = max(1, 7 - $z);
			$ceiling = min(max($floor + 5, 14 - $z), 13);
			$this->fill($world, $box, 0, 0, $z, 4, $floor, $z, $nb);
			$this->fillWithAir($world, $box, 1, $floor + 1, $z, 3, $ceiling - 1, $z);
			if ($z <= 6) {
				$this->setBlock($world, $stairs, 1, $floor + 1, $z, $box);
				$this->setBlock($world, $stairs, 2, $floor + 1, $z, $box);
				$this->setBlock($world, $stairs, 3, $floor + 1, $z, $box);
			}
			$this->fill($world, $box, 0, $ceiling, $z, 4, $ceiling, $z, $nb);
			$this->fill($world, $box, 0, $floor + 1, $z, 0, $ceiling - 1, $z, $nb);
			$this->fill($world, $box, 4, $floor + 1, $z, 4, $ceiling - 1, $z, $nb);
			if (($z & 1) === 0) {
				$this->fill($world, $box, 0, $floor + 2, $z, 0, $floor + 3, $z, $fence);
				$this->fill($world, $box, 4, $floor + 2, $z, 4, $floor + 3, $z, $fence);
			}
			for ($x = 0; $x <= 4; ++$x) {
				$this->replaceAirAndLiquidDownwards($world, $nb, $x, -1, $z, $box);
			}
		}
		return true;
	}
}
