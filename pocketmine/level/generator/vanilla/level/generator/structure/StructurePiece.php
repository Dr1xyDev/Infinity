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

namespace pocketmine\level\generator\vanilla\level\generator\structure;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\nbt\tag\IntTag;
use pocketmine\level\generator\vanilla\nbt\tag\LongTag;
use pocketmine\level\generator\vanilla\nbt\tag\StringTag;
use pocketmine\level\generator\vanilla\utils\Random;
use function max;
use function min;

/**
 * Part of a generated structure (port of the vanilla StructureComponent). Pieces are described in local coordinates:
 * x runs to the right, y up and z in the direction the piece faces; the orientation turns them into world coordinates.
 * Pieces without orientation use world coordinates directly.
 */
abstract class StructurePiece
{
	public const NORTH = 2;
	public const SOUTH = 3;
	public const WEST = 4;
	public const EAST = 5;

	public const HORIZONTAL = [self::NORTH, self::EAST, self::SOUTH, self::WEST];

	protected const AIR = BlockIds::AIR << Block::INTERNAL_METADATA_BITS;

	protected StructureBoundingBox $boundingBox;
	protected ?int $orientation = null;

	public function __construct(protected int $componentType)
	{
	}

	public static function full(int $id, int $meta = 0) : int
	{
		return ($id << Block::INTERNAL_METADATA_BITS) | $meta;
	}

	public static function randomHorizontal(Random $random) : int
	{
		return self::HORIZONTAL[$random->nextBoundedInt(4)];
	}

	public static function isAxisZ(int $facing) : bool
	{
		return $facing === self::NORTH || $facing === self::SOUTH;
	}

	public function getBoundingBox() : StructureBoundingBox
	{
		return $this->boundingBox;
	}

	public function getComponentType() : int
	{
		return $this->componentType;
	}

	public function getOrientation() : ?int
	{
		return $this->orientation;
	}

	public function setOrientation(?int $orientation) : void
	{
		$this->orientation = $orientation;
	}

	/**
	 * Adds the pieces connected to this one
	 *
	 * @param StructurePiece[] $pieces
	 */
	public function buildComponent(StructurePiece $start, array &$pieces, Random $random) : void
	{
	}

	/**
	 * Places the blocks of this piece which are inside of $box. Returns false if the piece was skipped.
	 */
	abstract public function addComponentParts(StructureWorld $world, Random $random, StructureBoundingBox $box) : bool;

	public function offset(int $x, int $y, int $z) : void
	{
		$this->boundingBox->offset($x, $y, $z);
	}

	/**
	 * @param StructurePiece[] $pieces
	 */
	public static function findIntersecting(array $pieces, StructureBoundingBox $box) : ?StructurePiece
	{
		foreach ($pieces as $piece) {
			if ($piece->boundingBox->intersectsWith($box)) {
				return $piece;
			}
		}
		return null;
	}

	protected function getXWithOffset(int $x, int $z) : int
	{
		return match ($this->orientation) {
			self::NORTH, self::SOUTH => $this->boundingBox->minX + $x,
			self::WEST => $this->boundingBox->maxX - $z,
			self::EAST => $this->boundingBox->minX + $z,
			default => $x,
		};
	}

	protected function getYWithOffset(int $y) : int
	{
		return $this->orientation === null ? $y : $y + $this->boundingBox->minY;
	}

	protected function getZWithOffset(int $x, int $z) : int
	{
		return match ($this->orientation) {
			self::NORTH => $this->boundingBox->maxZ - $z,
			self::SOUTH => $this->boundingBox->minZ + $z,
			self::WEST, self::EAST => $this->boundingBox->minZ + $x,
			default => $z,
		};
	}

	/**
	 * World direction of a local direction
	 *
	 * @return int[] [dx, dz]
	 */
	protected function toWorldDirection(int $dx, int $dz) : array
	{
		return [
			$this->getXWithOffset($dx, $dz) - $this->getXWithOffset(0, 0),
			$this->getZWithOffset($dx, $dz) - $this->getZWithOffset(0, 0)
		];
	}

	/**
	 * Meta of stairs which go up towards the given local direction
	 */
	protected function stairsMeta(int $dx, int $dz, bool $upsideDown = false) : int
	{
		[$wx, $wz] = $this->toWorldDirection($dx, $dz);
		$meta = $wx > 0 ? 0 : ($wx < 0 ? 1 : ($wz > 0 ? 2 : 3));
		return $upsideDown ? $meta | 0x04 : $meta;
	}

	/**
	 * Meta of a straight rail running along the given local direction
	 */
	protected function railMeta(int $dx, int $dz) : int
	{
		[$wx, ] = $this->toWorldDirection($dx, $dz);
		return $wx !== 0 ? 1 : 0;
	}

	/**
	 * Meta of a torch attached to the block in the given local direction
	 */
	protected function torchMeta(int $dx, int $dz) : int
	{
		[$wx, $wz] = $this->toWorldDirection($dx, $dz);
		return $wx < 0 ? 1 : ($wx > 0 ? 2 : ($wz < 0 ? 3 : 4));
	}

	protected function setBlock(StructureWorld $world, int $fullBlock, int $x, int $y, int $z, StructureBoundingBox $box) : void
	{
		$wx = $this->getXWithOffset($x, $z);
		$wy = $this->getYWithOffset($y);
		$wz = $this->getZWithOffset($x, $z);
		if ($box->isVecInside($wx, $wy, $wz)) {
			$world->setFullBlock($wx, $wy, $wz, $fullBlock);
		}
	}

	protected function getBlock(StructureWorld $world, int $x, int $y, int $z, StructureBoundingBox $box) : int
	{
		$wx = $this->getXWithOffset($x, $z);
		$wy = $this->getYWithOffset($y);
		$wz = $this->getZWithOffset($x, $z);
		return $box->isVecInside($wx, $wy, $wz) ? $world->getFullBlock($wx, $wy, $wz) : self::AIR;
	}

	protected function isAirAt(StructureWorld $world, int $x, int $y, int $z, StructureBoundingBox $box) : bool
	{
		return ($this->getBlock($world, $x, $y, $z, $box) >> Block::INTERNAL_METADATA_BITS) === BlockIds::AIR;
	}

	/**
	 * Replacement of the vanilla "sky light < 8" checks: true if the sky can't be seen from there
	 */
	protected function isDark(StructureWorld $world, int $x, int $y, int $z, StructureBoundingBox $box) : bool
	{
		$wx = $this->getXWithOffset($x, $z);
		$wy = $this->getYWithOffset($y);
		$wz = $this->getZWithOffset($x, $z);
		return !$box->isVecInside($wx, $wy, $wz) || !$world->isSkyVisible($wx, $wy, $wz);
	}

	protected function fillWithBlocks(StructureWorld $world, StructureBoundingBox $box, int $x1, int $y1, int $z1, int $x2, int $y2, int $z2, int $edge, int $inside, bool $existingOnly) : void
	{
		for ($y = $y1; $y <= $y2; ++$y) {
			for ($x = $x1; $x <= $x2; ++$x) {
				for ($z = $z1; $z <= $z2; ++$z) {
					if (!$existingOnly || !$this->isAirAt($world, $x, $y, $z, $box)) {
						$isEdge = $y === $y1 || $y === $y2 || $x === $x1 || $x === $x2 || $z === $z1 || $z === $z2;
						$this->setBlock($world, $isEdge ? $edge : $inside, $x, $y, $z, $box);
					}
				}
			}
		}
	}

	protected function fillWithAir(StructureWorld $world, StructureBoundingBox $box, int $x1, int $y1, int $z1, int $x2, int $y2, int $z2) : void
	{
		$this->fillWithBlocks($world, $box, $x1, $y1, $z1, $x2, $y2, $z2, self::AIR, self::AIR, false);
	}

	protected function fill(StructureWorld $world, StructureBoundingBox $box, int $x1, int $y1, int $z1, int $x2, int $y2, int $z2, int $block) : void
	{
		$this->fillWithBlocks($world, $box, $x1, $y1, $z1, $x2, $y2, $z2, $block, $block, false);
	}

	protected function generateMaybeBox(StructureWorld $world, StructureBoundingBox $box, Random $random, float $chance, int $x1, int $y1, int $z1, int $x2, int $y2, int $z2, int $edge, int $inside, bool $requireNonAir, bool $requireDark) : void
	{
		for ($y = $y1; $y <= $y2; ++$y) {
			for ($x = $x1; $x <= $x2; ++$x) {
				for ($z = $z1; $z <= $z2; ++$z) {
					if ($random->nextFloat() <= $chance && (!$requireNonAir || !$this->isAirAt($world, $x, $y, $z, $box)) && (!$requireDark || $this->isDark($world, $x, $y, $z, $box))) {
						$isEdge = $y === $y1 || $y === $y2 || $x === $x1 || $x === $x2 || $z === $z1 || $z === $z2;
						$this->setBlock($world, $isEdge ? $edge : $inside, $x, $y, $z, $box);
					}
				}
			}
		}
	}

	protected function randomlyPlaceBlock(StructureWorld $world, StructureBoundingBox $box, Random $random, float $chance, int $x, int $y, int $z, int $block) : void
	{
		if ($random->nextFloat() < $chance) {
			$this->setBlock($world, $block, $x, $y, $z, $box);
		}
	}

	/**
	 * Fills an ellipsoid (used for the domes of mineshaft rooms)
	 */
	protected function randomlyRareFillWithBlocks(StructureWorld $world, StructureBoundingBox $box, int $minX, int $minY, int $minZ, int $maxX, int $maxY, int $maxZ, int $block, bool $excludeAir) : void
	{
		$sizeX = (float) ($maxX - $minX + 1);
		$sizeY = (float) ($maxY - $minY + 1);
		$sizeZ = (float) ($maxZ - $minZ + 1);
		$centerX = $minX + $sizeX / 2.0;
		$centerZ = $minZ + $sizeZ / 2.0;
		for ($y = $minY; $y <= $maxY; ++$y) {
			$ny = ($y - $minY) / $sizeY;
			for ($x = $minX; $x <= $maxX; ++$x) {
				$nx = ($x - $centerX) / ($sizeX * 0.5);
				for ($z = $minZ; $z <= $maxZ; ++$z) {
					$nz = ($z - $centerZ) / ($sizeZ * 0.5);
					if ((!$excludeAir || !$this->isAirAt($world, $x, $y, $z, $box)) && $nx * $nx + $ny * $ny + $nz * $nz <= 1.05) {
						$this->setBlock($world, $block, $x, $y, $z, $box);
					}
				}
			}
		}
	}

	/**
	 * Builds a pillar down to the ground through air and liquids
	 */
	protected function replaceAirAndLiquidDownwards(StructureWorld $world, int $block, int $x, int $y, int $z, StructureBoundingBox $box) : void
	{
		$wx = $this->getXWithOffset($x, $z);
		$wy = $this->getYWithOffset($y);
		$wz = $this->getZWithOffset($x, $z);
		if (!$box->isVecInside($wx, $wy, $wz)) {
			return;
		}
		while ($wy > 1 && self::isAirOrLiquid($world->getFullBlock($wx, $wy, $wz))) {
			$world->setFullBlock($wx, $wy, $wz, $block);
			--$wy;
		}
	}

	protected static function isAirOrLiquid(int $fullBlock) : bool
	{
		$id = $fullBlock >> Block::INTERNAL_METADATA_BITS;
		return $id === BlockIds::AIR || self::isLiquidId($id);
	}

	protected static function isLiquidId(int $id) : bool
	{
		return $id === BlockIds::FLOWING_WATER || $id === BlockIds::STILL_WATER || $id === BlockIds::FLOWING_LAVA || $id === BlockIds::STILL_LAVA;
	}

	/**
	 * Checks the faces of the piece (limited to $box) for liquids
	 */
	protected function isLiquidInStructureBoundingBox(StructureWorld $world, StructureBoundingBox $box) : bool
	{
		$bb = $this->boundingBox;
		$minX = max($bb->minX - 1, $box->minX);
		$minY = max($bb->minY - 1, $box->minY);
		$minZ = max($bb->minZ - 1, $box->minZ);
		$maxX = min($bb->maxX + 1, $box->maxX);
		$maxY = min($bb->maxY + 1, $box->maxY);
		$maxZ = min($bb->maxZ + 1, $box->maxZ);

		for ($x = $minX; $x <= $maxX; ++$x) {
			for ($z = $minZ; $z <= $maxZ; ++$z) {
				if (self::isLiquidId($world->getFullBlock($x, $minY, $z) >> Block::INTERNAL_METADATA_BITS) || self::isLiquidId($world->getFullBlock($x, $maxY, $z) >> Block::INTERNAL_METADATA_BITS)) {
					return true;
				}
			}
		}
		for ($x = $minX; $x <= $maxX; ++$x) {
			for ($y = $minY; $y <= $maxY; ++$y) {
				if (self::isLiquidId($world->getFullBlock($x, $y, $minZ) >> Block::INTERNAL_METADATA_BITS) || self::isLiquidId($world->getFullBlock($x, $y, $maxZ) >> Block::INTERNAL_METADATA_BITS)) {
					return true;
				}
			}
		}
		for ($z = $minZ; $z <= $maxZ; ++$z) {
			for ($y = $minY; $y <= $maxY; ++$y) {
				if (self::isLiquidId($world->getFullBlock($minX, $y, $z) >> Block::INTERNAL_METADATA_BITS) || self::isLiquidId($world->getFullBlock($maxX, $y, $z) >> Block::INTERNAL_METADATA_BITS)) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Places a chest which gets its content from the loot table when it is opened for the first time
	 */
	protected function placeChest(StructureWorld $world, StructureBoundingBox $box, Random $random, int $x, int $y, int $z, string $lootTable) : bool
	{
		$wx = $this->getXWithOffset($x, $z);
		$wy = $this->getYWithOffset($y);
		$wz = $this->getZWithOffset($x, $z);
		if (!$box->isVecInside($wx, $wy, $wz) || ($world->getFullBlock($wx, $wy, $wz) >> Block::INTERNAL_METADATA_BITS) === BlockIds::CHEST) {
			return false;
		}

		//the front of the chest faces a free block
		$meta = 2;
		foreach ([[0, -1, 2], [0, 1, 3], [-1, 0, 4], [1, 0, 5]] as [$dx, $dz, $facing]) {
			if (($world->getFullBlock($wx + $dx, $wy, $wz + $dz) >> Block::INTERNAL_METADATA_BITS) === BlockIds::AIR) {
				$meta = $facing;
				break;
			}
		}
		$world->setFullBlock($wx, $wy, $wz, self::full(BlockIds::CHEST, $meta));

		$nbt = new CompoundTag();
		$nbt->setTag(new StringTag("id", "Chest"));
		$nbt->setTag(new IntTag("x", $wx));
		$nbt->setTag(new IntTag("y", $wy));
		$nbt->setTag(new IntTag("z", $wz));
		$nbt->setTag(new StringTag("LootTable", $lootTable));
		$nbt->setTag(new LongTag("LootTableSeed", $random->nextSignedInt() ?: 1));
		$world->addTile($nbt);
		return true;
	}

	/**
	 * Places a monster spawner for the entity with the given network id
	 */
	protected function placeSpawner(StructureWorld $world, StructureBoundingBox $box, int $x, int $y, int $z, int $entityNetworkId) : bool
	{
		$wx = $this->getXWithOffset($x, $z);
		$wy = $this->getYWithOffset($y);
		$wz = $this->getZWithOffset($x, $z);
		if (!$box->isVecInside($wx, $wy, $wz)) {
			return false;
		}
		$world->setFullBlock($wx, $wy, $wz, self::full(BlockIds::MOB_SPAWNER));

		$nbt = new CompoundTag();
		$nbt->setTag(new StringTag("id", "MobSpawner"));
		$nbt->setTag(new IntTag("x", $wx));
		$nbt->setTag(new IntTag("y", $wy));
		$nbt->setTag(new IntTag("z", $wz));
		$nbt->setTag(new IntTag("EntityId", $entityNetworkId));
		$world->addTile($nbt);
		return true;
	}
}
