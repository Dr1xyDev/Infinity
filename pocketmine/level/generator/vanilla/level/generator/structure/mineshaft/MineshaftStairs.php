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

namespace pocketmine\level\generator\vanilla\level\generator\structure\mineshaft;

use pocketmine\level\generator\vanilla\level\generator\structure\StructureBoundingBox;
use pocketmine\level\generator\vanilla\level\generator\structure\StructurePiece;
use pocketmine\level\generator\vanilla\level\generator\structure\StructureWorld;
use pocketmine\level\generator\vanilla\utils\Random;

/**
 * Tunnel going 5 blocks down
 */
class MineshaftStairs extends MineshaftPiece
{
	public function __construct(int $componentType, StructureBoundingBox $box, int $facing, int $type)
	{
		parent::__construct($componentType, $type);
		$this->setOrientation($facing);
		$this->boundingBox = $box;
	}

	/**
	 * @param StructurePiece[] $pieces
	 */
	public static function findStairs(array $pieces, int $x, int $y, int $z, int $facing) : ?StructureBoundingBox
	{
		$box = new StructureBoundingBox($x, $y - 5, $z, $x, $y + 2, $z);
		switch ($facing) {
			case self::NORTH:
				$box->maxX = $x + 2;
				$box->minZ = $z - 8;
				break;
			case self::SOUTH:
				$box->maxX = $x + 2;
				$box->maxZ = $z + 8;
				break;
			case self::WEST:
				$box->minX = $x - 8;
				$box->maxZ = $z + 2;
				break;
			default:
				$box->maxX = $x + 8;
				$box->maxZ = $z + 2;
		}
		return self::findIntersecting($pieces, $box) !== null ? null : $box;
	}

	public function buildComponent(StructurePiece $start, array &$pieces, Random $random) : void
	{
		$type = $this->getComponentType();
		$bb = $this->boundingBox;
		switch ($this->orientation) {
			case self::NORTH:
				self::generateAndAddPiece($start, $pieces, $random, $bb->minX, $bb->minY, $bb->minZ - 1, self::NORTH, $type);
				break;
			case self::SOUTH:
				self::generateAndAddPiece($start, $pieces, $random, $bb->minX, $bb->minY, $bb->maxZ + 1, self::SOUTH, $type);
				break;
			case self::WEST:
				self::generateAndAddPiece($start, $pieces, $random, $bb->minX - 1, $bb->minY, $bb->minZ, self::WEST, $type);
				break;
			default:
				self::generateAndAddPiece($start, $pieces, $random, $bb->maxX + 1, $bb->minY, $bb->minZ, self::EAST, $type);
		}
	}

	public function addComponentParts(StructureWorld $world, Random $random, StructureBoundingBox $box) : bool
	{
		if ($this->isLiquidInStructureBoundingBox($world, $box)) {
			return false;
		}
		$this->fillWithAir($world, $box, 0, 5, 0, 2, 7, 1);
		$this->fillWithAir($world, $box, 0, 0, 7, 2, 2, 8);
		for ($i = 0; $i < 5; ++$i) {
			$this->fillWithAir($world, $box, 0, 5 - $i - ($i < 4 ? 1 : 0), 2 + $i, 2, 7 - $i, 2 + $i);
		}
		return true;
	}
}
