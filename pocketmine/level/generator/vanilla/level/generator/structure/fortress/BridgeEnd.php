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
 * Broken end of a bridge
 */
class BridgeEnd extends FortressPiece
{
	public const DIMENSIONS = [-1, -3, 0, 5, 10, 8];

	private int $fillSeed;

	public function __construct(int $componentType, Random $random, StructureBoundingBox $box, int $facing)
	{
		parent::__construct($componentType, $random, $box, $facing);
		$this->fillSeed = $random->nextInt();
	}

	public function addComponentParts(StructureWorld $world, Random $random, StructureBoundingBox $box) : bool
	{
		//the same broken shape in every chunk
		$shape = new Random($this->fillSeed);
		$nb = self::NETHER_BRICK;
		for ($x = 0; $x <= 4; ++$x) {
			for ($y = 3; $y <= 4; ++$y) {
				$this->fill($world, $box, $x, $y, 0, $x, $y, $shape->nextBoundedInt(8), $nb);
			}
		}
		$this->fill($world, $box, 0, 5, 0, 0, 5, $shape->nextBoundedInt(8), $nb);
		$this->fill($world, $box, 4, 5, 0, 4, 5, $shape->nextBoundedInt(8), $nb);
		for ($x = 0; $x <= 4; ++$x) {
			$this->fill($world, $box, $x, 2, 0, $x, 2, $shape->nextBoundedInt(5), $nb);
		}
		for ($x = 0; $x <= 4; ++$x) {
			for ($y = 0; $y <= 1; ++$y) {
				$this->fill($world, $box, $x, $y, 0, $x, $y, $shape->nextBoundedInt(3), $nb);
			}
		}
		return true;
	}
}
