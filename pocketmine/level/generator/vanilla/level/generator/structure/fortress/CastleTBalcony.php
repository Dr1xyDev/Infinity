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
 * T shaped corridor end with a balcony
 */
class CastleTBalcony extends FortressPiece
{
	public const DIMENSIONS = [-3, 0, 0, 9, 7, 9];

	protected function buildFortressComponent(FortressStartPiece $start, array &$pieces, Random $random) : void
	{
		$offset = ($this->orientation === self::WEST || $this->orientation === self::NORTH) ? 5 : 1;
		$this->getNextComponentX($start, $pieces, $random, 0, $offset, $random->nextBoundedInt(8) > 0);
		$this->getNextComponentZ($start, $pieces, $random, 0, $offset, $random->nextBoundedInt(8) > 0);
	}

	public function addComponentParts(StructureWorld $world, Random $random, StructureBoundingBox $box) : bool
	{
		$nb = self::NETHER_BRICK;
		$fence = self::FENCE;
		$this->fill($world, $box, 0, 0, 0, 8, 1, 8, $nb);
		$this->fillWithAir($world, $box, 0, 2, 0, 8, 5, 8);
		$this->fill($world, $box, 0, 6, 0, 8, 6, 5, $nb);
		$this->fill($world, $box, 0, 2, 0, 2, 5, 0, $nb);
		$this->fill($world, $box, 6, 2, 0, 8, 5, 0, $nb);
		$this->fill($world, $box, 1, 3, 0, 1, 4, 0, $fence);
		$this->fill($world, $box, 7, 3, 0, 7, 4, 0, $fence);
		$this->fill($world, $box, 0, 2, 4, 8, 2, 8, $nb);
		$this->fillWithAir($world, $box, 1, 1, 4, 2, 2, 4);
		$this->fillWithAir($world, $box, 6, 1, 4, 7, 2, 4);
		$this->fill($world, $box, 1, 3, 8, 7, 3, 8, $fence);
		$this->setBlock($world, $fence, 0, 3, 8, $box);
		$this->setBlock($world, $fence, 8, 3, 8, $box);
		$this->fill($world, $box, 0, 3, 6, 0, 3, 7, $fence);
		$this->fill($world, $box, 8, 3, 6, 8, 3, 7, $fence);
		$this->fill($world, $box, 0, 3, 4, 0, 5, 5, $nb);
		$this->fill($world, $box, 8, 3, 4, 8, 5, 5, $nb);
		$this->fill($world, $box, 1, 3, 5, 2, 5, 5, $nb);
		$this->fill($world, $box, 6, 3, 5, 7, 5, 5, $nb);
		$this->fill($world, $box, 1, 4, 5, 1, 5, 5, $fence);
		$this->fill($world, $box, 7, 4, 5, 7, 5, 5, $fence);
		$this->buildCorridorPillars($world, $box, 8, 5);
		return true;
	}
}
