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
 * Crossing of corridors, sometimes two floors high. Uses world coordinates.
 */
class MineshaftCrossing extends MineshaftPiece
{
	private bool $isMultipleFloors;

	public function __construct(int $componentType, StructureBoundingBox $box, private int $corridorDirection, int $type)
	{
		parent::__construct($componentType, $type);
		$this->boundingBox = $box;
		$this->isMultipleFloors = $box->getYSize() > 3;
	}

	/**
	 * @param StructurePiece[] $pieces
	 */
	public static function findCrossing(array $pieces, Random $random, int $x, int $y, int $z, int $facing) : ?StructureBoundingBox
	{
		$box = new StructureBoundingBox($x, $y, $z, $x, $y + 2, $z);
		if ($random->nextBoundedInt(4) === 0) {
			$box->maxY += 4;
		}
		switch ($facing) {
			case self::NORTH:
				$box->minX = $x - 1;
				$box->maxX = $x + 3;
				$box->minZ = $z - 4;
				break;
			case self::SOUTH:
				$box->minX = $x - 1;
				$box->maxX = $x + 3;
				$box->maxZ = $z + 4;
				break;
			case self::WEST:
				$box->minX = $x - 4;
				$box->minZ = $z - 1;
				$box->maxZ = $z + 3;
				break;
			default:
				$box->maxX = $x + 4;
				$box->minZ = $z - 1;
				$box->maxZ = $z + 3;
		}
		return self::findIntersecting($pieces, $box) !== null ? null : $box;
	}

	public function buildComponent(StructurePiece $start, array &$pieces, Random $random) : void
	{
		$type = $this->getComponentType();
		$bb = $this->boundingBox;
		switch ($this->corridorDirection) {
			case self::NORTH:
				self::generateAndAddPiece($start, $pieces, $random, $bb->minX + 1, $bb->minY, $bb->minZ - 1, self::NORTH, $type);
				self::generateAndAddPiece($start, $pieces, $random, $bb->minX - 1, $bb->minY, $bb->minZ + 1, self::WEST, $type);
				self::generateAndAddPiece($start, $pieces, $random, $bb->maxX + 1, $bb->minY, $bb->minZ + 1, self::EAST, $type);
				break;
			case self::SOUTH:
				self::generateAndAddPiece($start, $pieces, $random, $bb->minX + 1, $bb->minY, $bb->maxZ + 1, self::SOUTH, $type);
				self::generateAndAddPiece($start, $pieces, $random, $bb->minX - 1, $bb->minY, $bb->minZ + 1, self::WEST, $type);
				self::generateAndAddPiece($start, $pieces, $random, $bb->maxX + 1, $bb->minY, $bb->minZ + 1, self::EAST, $type);
				break;
			case self::WEST:
				self::generateAndAddPiece($start, $pieces, $random, $bb->minX + 1, $bb->minY, $bb->minZ - 1, self::NORTH, $type);
				self::generateAndAddPiece($start, $pieces, $random, $bb->minX + 1, $bb->minY, $bb->maxZ + 1, self::SOUTH, $type);
				self::generateAndAddPiece($start, $pieces, $random, $bb->minX - 1, $bb->minY, $bb->minZ + 1, self::WEST, $type);
				break;
			default:
				self::generateAndAddPiece($start, $pieces, $random, $bb->minX + 1, $bb->minY, $bb->minZ - 1, self::NORTH, $type);
				self::generateAndAddPiece($start, $pieces, $random, $bb->minX + 1, $bb->minY, $bb->maxZ + 1, self::SOUTH, $type);
				self::generateAndAddPiece($start, $pieces, $random, $bb->maxX + 1, $bb->minY, $bb->minZ + 1, self::EAST, $type);
		}

		if ($this->isMultipleFloors) {
			if ($random->nextBoolean()) {
				self::generateAndAddPiece($start, $pieces, $random, $bb->minX + 1, $bb->minY + 4, $bb->minZ - 1, self::NORTH, $type);
			}
			if ($random->nextBoolean()) {
				self::generateAndAddPiece($start, $pieces, $random, $bb->minX - 1, $bb->minY + 4, $bb->minZ + 1, self::WEST, $type);
			}
			if ($random->nextBoolean()) {
				self::generateAndAddPiece($start, $pieces, $random, $bb->maxX + 1, $bb->minY + 4, $bb->minZ + 1, self::EAST, $type);
			}
			if ($random->nextBoolean()) {
				self::generateAndAddPiece($start, $pieces, $random, $bb->minX + 1, $bb->minY + 4, $bb->maxZ + 1, self::SOUTH, $type);
			}
		}
	}

	public function addComponentParts(StructureWorld $world, Random $random, StructureBoundingBox $box) : bool
	{
		if ($this->isLiquidInStructureBoundingBox($world, $box)) {
			return false;
		}
		$bb = $this->boundingBox;
		$planks = $this->getPlanks();
		if ($this->isMultipleFloors) {
			$this->fillWithAir($world, $box, $bb->minX + 1, $bb->minY, $bb->minZ, $bb->maxX - 1, $bb->minY + 2, $bb->maxZ);
			$this->fillWithAir($world, $box, $bb->minX, $bb->minY, $bb->minZ + 1, $bb->maxX, $bb->minY + 2, $bb->maxZ - 1);
			$this->fillWithAir($world, $box, $bb->minX + 1, $bb->maxY - 2, $bb->minZ, $bb->maxX - 1, $bb->maxY, $bb->maxZ);
			$this->fillWithAir($world, $box, $bb->minX, $bb->maxY - 2, $bb->minZ + 1, $bb->maxX, $bb->maxY, $bb->maxZ - 1);
			$this->fillWithAir($world, $box, $bb->minX + 1, $bb->minY + 3, $bb->minZ + 1, $bb->maxX - 1, $bb->minY + 3, $bb->maxZ - 1);
		} else {
			$this->fillWithAir($world, $box, $bb->minX + 1, $bb->minY, $bb->minZ, $bb->maxX - 1, $bb->maxY, $bb->maxZ);
			$this->fillWithAir($world, $box, $bb->minX, $bb->minY, $bb->minZ + 1, $bb->maxX, $bb->maxY, $bb->maxZ - 1);
		}

		$this->placeSupportPillar($world, $box, $bb->minX + 1, $bb->minY, $bb->minZ + 1, $bb->maxY);
		$this->placeSupportPillar($world, $box, $bb->minX + 1, $bb->minY, $bb->maxZ - 1, $bb->maxY);
		$this->placeSupportPillar($world, $box, $bb->maxX - 1, $bb->minY, $bb->minZ + 1, $bb->maxY);
		$this->placeSupportPillar($world, $box, $bb->maxX - 1, $bb->minY, $bb->maxZ - 1, $bb->maxY);

		for ($x = $bb->minX; $x <= $bb->maxX; ++$x) {
			for ($z = $bb->minZ; $z <= $bb->maxZ; ++$z) {
				if ($this->isAirAt($world, $x, $bb->minY - 1, $z, $box) && $this->isDark($world, $x, $bb->minY - 1, $z, $box)) {
					$this->setBlock($world, $planks, $x, $bb->minY - 1, $z, $box);
				}
			}
		}
		return true;
	}

	private function placeSupportPillar(StructureWorld $world, StructureBoundingBox $box, int $x, int $y, int $z, int $maxY) : void
	{
		if (!$this->isAirAt($world, $x, $maxY + 1, $z, $box)) {
			$this->fill($world, $box, $x, $y, $z, $x, $maxY, $z, $this->getPlanks());
		}
	}
}
