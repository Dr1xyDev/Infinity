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

use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\level\generator\structure\StructureBoundingBox;
use pocketmine\level\generator\vanilla\level\generator\structure\StructurePiece;
use pocketmine\level\generator\vanilla\level\generator\structure\StructureWorld;
use pocketmine\level\generator\vanilla\utils\Random;
use function min;

/**
 * Dirt floored room with a dome, the start of every mineshaft. Uses world coordinates.
 */
class MineshaftRoom extends MineshaftPiece
{
	/** @var StructureBoundingBox[] openings to the connected corridors */
	private array $connectedRooms = [];

	public function __construct(int $componentType, Random $random, int $x, int $z, int $type)
	{
		parent::__construct($componentType, $type);
		$this->boundingBox = new StructureBoundingBox($x, 50, $z, $x + 7 + $random->nextBoundedInt(6), 54 + $random->nextBoundedInt(6), $z + 7 + $random->nextBoundedInt(6));
	}

	public function buildComponent(StructurePiece $start, array &$pieces, Random $random) : void
	{
		$type = $this->getComponentType();
		$bb = $this->boundingBox;
		$height = $bb->getYSize() - 3 - 1;
		if ($height <= 0) {
			$height = 1;
		}

		for ($offset = 0; $offset < $bb->getXSize(); $offset += 4) {
			$offset += $random->nextBoundedInt($bb->getXSize());
			if ($offset + 3 > $bb->getXSize()) {
				break;
			}
			$piece = self::generateAndAddPiece($start, $pieces, $random, $bb->minX + $offset, $bb->minY + $random->nextBoundedInt($height) + 1, $bb->minZ - 1, self::NORTH, $type);
			if ($piece !== null) {
				$pb = $piece->getBoundingBox();
				$this->connectedRooms[] = new StructureBoundingBox($pb->minX, $pb->minY, $bb->minZ, $pb->maxX, $pb->maxY, $bb->minZ + 1);
			}
		}
		for ($offset = 0; $offset < $bb->getXSize(); $offset += 4) {
			$offset += $random->nextBoundedInt($bb->getXSize());
			if ($offset + 3 > $bb->getXSize()) {
				break;
			}
			$piece = self::generateAndAddPiece($start, $pieces, $random, $bb->minX + $offset, $bb->minY + $random->nextBoundedInt($height) + 1, $bb->maxZ + 1, self::SOUTH, $type);
			if ($piece !== null) {
				$pb = $piece->getBoundingBox();
				$this->connectedRooms[] = new StructureBoundingBox($pb->minX, $pb->minY, $bb->maxZ - 1, $pb->maxX, $pb->maxY, $bb->maxZ);
			}
		}
		for ($offset = 0; $offset < $bb->getZSize(); $offset += 4) {
			$offset += $random->nextBoundedInt($bb->getZSize());
			if ($offset + 3 > $bb->getZSize()) {
				break;
			}
			$piece = self::generateAndAddPiece($start, $pieces, $random, $bb->minX - 1, $bb->minY + $random->nextBoundedInt($height) + 1, $bb->minZ + $offset, self::WEST, $type);
			if ($piece !== null) {
				$pb = $piece->getBoundingBox();
				$this->connectedRooms[] = new StructureBoundingBox($bb->minX, $pb->minY, $pb->minZ, $bb->minX + 1, $pb->maxY, $pb->maxZ);
			}
		}
		for ($offset = 0; $offset < $bb->getZSize(); $offset += 4) {
			$offset += $random->nextBoundedInt($bb->getZSize());
			if ($offset + 3 > $bb->getZSize()) {
				break;
			}
			$piece = self::generateAndAddPiece($start, $pieces, $random, $bb->maxX + 1, $bb->minY + $random->nextBoundedInt($height) + 1, $bb->minZ + $offset, self::EAST, $type);
			if ($piece !== null) {
				$pb = $piece->getBoundingBox();
				$this->connectedRooms[] = new StructureBoundingBox($bb->maxX - 1, $pb->minY, $pb->minZ, $bb->maxX, $pb->maxY, $pb->maxZ);
			}
		}
	}

	public function addComponentParts(StructureWorld $world, Random $random, StructureBoundingBox $box) : bool
	{
		if ($this->isLiquidInStructureBoundingBox($world, $box)) {
			return false;
		}
		$bb = $this->boundingBox;
		$this->fillWithBlocks($world, $box, $bb->minX, $bb->minY, $bb->minZ, $bb->maxX, $bb->minY, $bb->maxZ, self::full(BlockIds::DIRT), self::AIR, true);
		$this->fillWithAir($world, $box, $bb->minX, $bb->minY + 1, $bb->minZ, $bb->maxX, min($bb->minY + 3, $bb->maxY), $bb->maxZ);
		foreach ($this->connectedRooms as $opening) {
			$this->fillWithAir($world, $box, $opening->minX, $opening->maxY - 2, $opening->minZ, $opening->maxX, $opening->maxY, $opening->maxZ);
		}
		$this->randomlyRareFillWithBlocks($world, $box, $bb->minX, $bb->minY + 4, $bb->minZ, $bb->maxX, $bb->maxY, $bb->maxZ, self::AIR, false);
		return true;
	}

	public function offset(int $x, int $y, int $z) : void
	{
		parent::offset($x, $y, $z);
		foreach ($this->connectedRooms as $opening) {
			$opening->offset($x, $y, $z);
		}
	}
}
