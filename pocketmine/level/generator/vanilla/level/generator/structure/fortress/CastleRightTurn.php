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
 * Corridor turning to the local +x side, sometimes with a chest
 */
class CastleRightTurn extends FortressPiece
{
	public const DIMENSIONS = [-1, 0, 0, 5, 7, 5];

	private bool $chest;

	public function __construct(int $componentType, Random $random, StructureBoundingBox $box, int $facing)
	{
		parent::__construct($componentType, $random, $box, $facing);
		$this->chest = $random->nextBoundedInt(3) === 0;
	}

	protected function buildFortressComponent(FortressStartPiece $start, array &$pieces, Random $random) : void
	{
		$this->getNextComponentZ($start, $pieces, $random, 0, 1, true);
	}

	public function addComponentParts(StructureWorld $world, Random $random, StructureBoundingBox $box) : bool
	{
		$nb = self::NETHER_BRICK;
		$fence = self::FENCE;
		$this->fill($world, $box, 0, 0, 0, 4, 1, 4, $nb);
		$this->fillWithAir($world, $box, 0, 2, 0, 4, 5, 4);
		$this->fill($world, $box, 0, 2, 0, 0, 5, 4, $nb);
		$this->fill($world, $box, 0, 3, 1, 0, 4, 1, $fence);
		$this->fill($world, $box, 0, 3, 3, 0, 4, 3, $fence);
		$this->fill($world, $box, 4, 2, 0, 4, 5, 0, $nb);
		$this->fill($world, $box, 1, 2, 4, 4, 5, 4, $nb);
		$this->fill($world, $box, 1, 3, 4, 1, 4, 4, $fence);
		$this->fill($world, $box, 3, 3, 4, 3, 4, 4, $fence);
		if ($this->chest && $this->placeChest($world, $box, $random, 1, 2, 3, self::LOOT_TABLE)) {
			$this->chest = false;
		}
		$this->fill($world, $box, 0, 6, 0, 4, 6, 4, $nb);
		$this->buildCorridorPillars($world, $box);
		return true;
	}
}
