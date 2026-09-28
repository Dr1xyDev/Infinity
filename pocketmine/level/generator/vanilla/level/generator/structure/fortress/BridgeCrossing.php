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
 * Big crossing of two bridges
 */
class BridgeCrossing extends FortressPiece
{
	public const DIMENSIONS = [-8, -3, 0, 19, 10, 19];

	protected function buildFortressComponent(FortressStartPiece $start, array &$pieces, Random $random) : void
	{
		$this->getNextComponentNormal($start, $pieces, $random, 8, 3, false);
		$this->getNextComponentX($start, $pieces, $random, 3, 8, false);
		$this->getNextComponentZ($start, $pieces, $random, 3, 8, false);
	}

	public function addComponentParts(StructureWorld $world, Random $random, StructureBoundingBox $box) : bool
	{
		$nb = self::NETHER_BRICK;
		$this->fill($world, $box, 7, 3, 0, 11, 4, 18, $nb);
		$this->fill($world, $box, 0, 3, 7, 18, 4, 11, $nb);
		$this->fillWithAir($world, $box, 8, 5, 0, 10, 7, 18);
		$this->fillWithAir($world, $box, 0, 5, 8, 18, 7, 10);
		$this->fill($world, $box, 7, 5, 0, 7, 5, 7, $nb);
		$this->fill($world, $box, 7, 5, 11, 7, 5, 18, $nb);
		$this->fill($world, $box, 11, 5, 0, 11, 5, 7, $nb);
		$this->fill($world, $box, 11, 5, 11, 11, 5, 18, $nb);
		$this->fill($world, $box, 0, 5, 7, 7, 5, 7, $nb);
		$this->fill($world, $box, 11, 5, 7, 18, 5, 7, $nb);
		$this->fill($world, $box, 0, 5, 11, 7, 5, 11, $nb);
		$this->fill($world, $box, 11, 5, 11, 18, 5, 11, $nb);
		$this->fill($world, $box, 7, 2, 0, 11, 2, 5, $nb);
		$this->fill($world, $box, 7, 2, 13, 11, 2, 18, $nb);
		$this->fill($world, $box, 7, 0, 0, 11, 1, 3, $nb);
		$this->fill($world, $box, 7, 0, 15, 11, 1, 18, $nb);
		for ($x = 7; $x <= 11; ++$x) {
			for ($z = 0; $z <= 2; ++$z) {
				$this->replaceAirAndLiquidDownwards($world, $nb, $x, -1, $z, $box);
				$this->replaceAirAndLiquidDownwards($world, $nb, $x, -1, 18 - $z, $box);
			}
		}
		$this->fill($world, $box, 0, 2, 7, 5, 2, 11, $nb);
		$this->fill($world, $box, 13, 2, 7, 18, 2, 11, $nb);
		$this->fill($world, $box, 0, 0, 7, 3, 1, 11, $nb);
		$this->fill($world, $box, 15, 0, 7, 18, 1, 11, $nb);
		for ($x = 0; $x <= 2; ++$x) {
			for ($z = 7; $z <= 11; ++$z) {
				$this->replaceAirAndLiquidDownwards($world, $nb, $x, -1, $z, $box);
				$this->replaceAirAndLiquidDownwards($world, $nb, 18 - $x, -1, $z, $box);
			}
		}
		return true;
	}
}
