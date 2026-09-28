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
 * Straight bridge on pillars
 */
class BridgeStraight extends FortressPiece
{
	public const DIMENSIONS = [-1, -3, 0, 5, 10, 19];

	protected function buildFortressComponent(FortressStartPiece $start, array &$pieces, Random $random) : void
	{
		$this->getNextComponentNormal($start, $pieces, $random, 1, 3, false);
	}

	public function addComponentParts(StructureWorld $world, Random $random, StructureBoundingBox $box) : bool
	{
		$nb = self::NETHER_BRICK;
		$fence = self::FENCE;
		$this->fill($world, $box, 0, 3, 0, 4, 4, 18, $nb);
		$this->fillWithAir($world, $box, 1, 5, 0, 3, 7, 18);
		$this->fill($world, $box, 0, 5, 0, 0, 5, 18, $nb);
		$this->fill($world, $box, 4, 5, 0, 4, 5, 18, $nb);
		$this->fill($world, $box, 0, 2, 0, 4, 2, 5, $nb);
		$this->fill($world, $box, 0, 2, 13, 4, 2, 18, $nb);
		$this->fill($world, $box, 0, 0, 0, 4, 1, 3, $nb);
		$this->fill($world, $box, 0, 0, 15, 4, 1, 18, $nb);
		for ($x = 0; $x <= 4; ++$x) {
			for ($z = 0; $z <= 2; ++$z) {
				$this->replaceAirAndLiquidDownwards($world, $nb, $x, -1, $z, $box);
				$this->replaceAirAndLiquidDownwards($world, $nb, $x, -1, 18 - $z, $box);
			}
		}
		$this->fill($world, $box, 0, 1, 1, 0, 4, 1, $fence);
		$this->fill($world, $box, 0, 3, 4, 0, 4, 4, $fence);
		$this->fill($world, $box, 0, 3, 14, 0, 4, 14, $fence);
		$this->fill($world, $box, 0, 1, 17, 0, 4, 17, $fence);
		$this->fill($world, $box, 4, 1, 1, 4, 4, 1, $fence);
		$this->fill($world, $box, 4, 3, 4, 4, 4, 4, $fence);
		$this->fill($world, $box, 4, 3, 14, 4, 4, 14, $fence);
		$this->fill($world, $box, 4, 1, 17, 4, 4, 17, $fence);
		return true;
	}
}
