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
 * Small covered crossing of bridges
 */
class BridgeSmallCrossing extends FortressPiece
{
	public const DIMENSIONS = [-2, 0, 0, 7, 9, 7];

	protected function buildFortressComponent(FortressStartPiece $start, array &$pieces, Random $random) : void
	{
		$this->getNextComponentNormal($start, $pieces, $random, 2, 0, false);
		$this->getNextComponentX($start, $pieces, $random, 0, 2, false);
		$this->getNextComponentZ($start, $pieces, $random, 0, 2, false);
	}

	public function addComponentParts(StructureWorld $world, Random $random, StructureBoundingBox $box) : bool
	{
		$nb = self::NETHER_BRICK;
		$fence = self::FENCE;
		$this->fill($world, $box, 0, 0, 0, 6, 1, 6, $nb);
		$this->fillWithAir($world, $box, 0, 2, 0, 6, 7, 6);
		$this->fill($world, $box, 0, 2, 0, 1, 6, 0, $nb);
		$this->fill($world, $box, 0, 2, 6, 1, 6, 6, $nb);
		$this->fill($world, $box, 5, 2, 0, 6, 6, 0, $nb);
		$this->fill($world, $box, 5, 2, 6, 6, 6, 6, $nb);
		$this->fill($world, $box, 0, 2, 0, 0, 6, 1, $nb);
		$this->fill($world, $box, 0, 2, 5, 0, 6, 6, $nb);
		$this->fill($world, $box, 6, 2, 0, 6, 6, 1, $nb);
		$this->fill($world, $box, 6, 2, 5, 6, 6, 6, $nb);
		$this->fill($world, $box, 2, 6, 0, 4, 6, 0, $nb);
		$this->fill($world, $box, 2, 5, 0, 4, 5, 0, $fence);
		$this->fill($world, $box, 2, 6, 6, 4, 6, 6, $nb);
		$this->fill($world, $box, 2, 5, 6, 4, 5, 6, $fence);
		$this->fill($world, $box, 0, 6, 2, 0, 6, 4, $nb);
		$this->fill($world, $box, 0, 5, 2, 0, 5, 4, $fence);
		$this->fill($world, $box, 6, 6, 2, 6, 6, 4, $nb);
		$this->fill($world, $box, 6, 5, 2, 6, 5, 4, $fence);
		for ($x = 0; $x <= 6; ++$x) {
			for ($z = 0; $z <= 6; ++$z) {
				$this->replaceAirAndLiquidDownwards($world, $nb, $x, -1, $z, $box);
			}
		}
		return true;
	}
}
