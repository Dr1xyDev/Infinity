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

/**
 * Big room with a lava well leading from the bridges into the castle
 */
class CastleEntrance extends FortressPiece
{
	public const DIMENSIONS = [-5, -3, 0, 13, 14, 13];

	protected function buildFortressComponent(FortressStartPiece $start, array &$pieces, Random $random) : void
	{
		$this->getNextComponentNormal($start, $pieces, $random, 5, 3, true);
	}

	public function addComponentParts(StructureWorld $world, Random $random, StructureBoundingBox $box) : bool
	{
		$nb = self::NETHER_BRICK;
		$fence = self::FENCE;
		$this->fill($world, $box, 0, 3, 0, 12, 4, 12, $nb);
		$this->fillWithAir($world, $box, 0, 5, 0, 12, 13, 12);
		$this->fill($world, $box, 0, 5, 0, 1, 12, 12, $nb);
		$this->fill($world, $box, 11, 5, 0, 12, 12, 12, $nb);
		$this->fill($world, $box, 2, 5, 11, 4, 12, 12, $nb);
		$this->fill($world, $box, 8, 5, 11, 10, 12, 12, $nb);
		$this->fill($world, $box, 5, 9, 11, 7, 12, 12, $nb);
		$this->fill($world, $box, 2, 5, 0, 4, 12, 1, $nb);
		$this->fill($world, $box, 8, 5, 0, 10, 12, 1, $nb);
		$this->fill($world, $box, 5, 9, 0, 7, 12, 1, $nb);
		$this->fill($world, $box, 2, 11, 2, 10, 12, 10, $nb);
		$this->fill($world, $box, 5, 8, 0, 7, 8, 0, $fence);
		$this->buildBattlements($world, $box);
		for ($z = 3; $z <= 9; $z += 2) {
			$this->fill($world, $box, 1, 7, $z, 1, 8, $z, $fence);
			$this->fill($world, $box, 11, 7, $z, 11, 8, $z, $fence);
		}
		$this->buildFoundation($world, $box);

		//lava well
		$this->fill($world, $box, 5, 5, 5, 7, 5, 7, $nb);
		$this->fillWithAir($world, $box, 6, 1, 6, 6, 4, 6);
		$this->setBlock($world, $nb, 6, 0, 6, $box);
		$this->setBlock($world, self::full(BlockIds::FLOWING_LAVA), 6, 5, 6, $box);
		return true;
	}
}
