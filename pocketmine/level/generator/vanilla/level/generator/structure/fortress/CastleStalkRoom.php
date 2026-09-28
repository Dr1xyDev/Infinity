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
 * Room with nether wart on soul sand and a staircase to the upper exit
 */
class CastleStalkRoom extends FortressPiece
{
	public const DIMENSIONS = [-5, -3, 0, 13, 14, 13];

	protected function buildFortressComponent(FortressStartPiece $start, array &$pieces, Random $random) : void
	{
		$this->getNextComponentNormal($start, $pieces, $random, 5, 3, true);
		$this->getNextComponentNormal($start, $pieces, $random, 5, 11, true);
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
		$this->buildBattlements($world, $box);

		//staircase to the exit in the upper floor
		$stairsForward = self::full(BlockIds::NETHER_BRICK_STAIRS, $this->stairsMeta(0, 1));
		for ($step = 0; $step <= 6; ++$step) {
			$z = $step + 4;
			for ($x = 5; $x <= 7; ++$x) {
				$this->setBlock($world, $stairsForward, $x, 5 + $step, $z, $box);
			}
			if ($z >= 5 && $z <= 8) {
				$this->fill($world, $box, 5, 5, $z, 7, $step + 4, $z, $nb);
			} elseif ($z >= 9 && $z <= 10) {
				$this->fill($world, $box, 5, 8, $z, 7, $step + 4, $z, $nb);
			}
			if ($step >= 1) {
				$this->fillWithAir($world, $box, 5, 6 + $step, $z, 7, 9 + $step, $z);
			}
		}
		for ($x = 5; $x <= 7; ++$x) {
			$this->setBlock($world, $stairsForward, $x, 12, 11, $box);
		}
		$this->fill($world, $box, 5, 6, 7, 5, 7, 7, $fence);
		$this->fill($world, $box, 7, 6, 7, 7, 7, 7, $fence);
		$this->fillWithAir($world, $box, 5, 13, 12, 7, 13, 12);

		//nether wart beds
		$this->fill($world, $box, 2, 5, 2, 3, 5, 3, $nb);
		$this->fill($world, $box, 2, 5, 9, 3, 5, 10, $nb);
		$this->fill($world, $box, 2, 5, 4, 2, 5, 8, $nb);
		$this->fill($world, $box, 9, 5, 2, 10, 5, 3, $nb);
		$this->fill($world, $box, 9, 5, 9, 10, 5, 10, $nb);
		$this->fill($world, $box, 10, 5, 4, 10, 5, 8, $nb);
		$stairsLeft = self::full(BlockIds::NETHER_BRICK_STAIRS, $this->stairsMeta(-1, 0));
		$stairsRight = self::full(BlockIds::NETHER_BRICK_STAIRS, $this->stairsMeta(1, 0));
		foreach ([2, 3, 9, 10] as $z) {
			$this->setBlock($world, $stairsLeft, 4, 5, $z, $box);
			$this->setBlock($world, $stairsRight, 8, 5, $z, $box);
		}
		$soulSand = self::full(BlockIds::SOUL_SAND);
		$wart = self::full(BlockIds::NETHER_WART_PLANT);
		$this->fill($world, $box, 3, 4, 4, 4, 4, 8, $soulSand);
		$this->fill($world, $box, 8, 4, 4, 9, 4, 8, $soulSand);
		$this->fill($world, $box, 3, 5, 4, 4, 5, 8, $wart);
		$this->fill($world, $box, 8, 5, 4, 9, 5, 8, $wart);

		$this->buildFoundation($world, $box);
		return true;
	}
}
