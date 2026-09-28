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

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\BlockFactory;
use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\level\generator\structure\StructureBoundingBox;
use pocketmine\level\generator\vanilla\level\generator\structure\StructurePiece;
use pocketmine\level\generator\vanilla\level\generator\structure\StructureWorld;
use pocketmine\level\generator\vanilla\nbt\NBT;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\nbt\tag\DoubleTag;
use pocketmine\level\generator\vanilla\nbt\tag\FloatTag;
use pocketmine\level\generator\vanilla\nbt\tag\ListTag;
use pocketmine\level\generator\vanilla\nbt\tag\LongTag;
use pocketmine\level\generator\vanilla\nbt\tag\StringTag;
use pocketmine\level\generator\vanilla\utils\Random;
use function intdiv;

/**
 * Straight 3x3 tunnel with wooden supports every 5 blocks, sometimes rails, cobwebs and chest minecarts
 */
class MineshaftCorridor extends MineshaftPiece
{
	/** Network id of cave spiders, used for the spawner */
	private const CAVE_SPIDER = 40;

	private bool $hasRails;
	private bool $hasSpiders;
	private bool $spawnerPlaced = false;
	private int $sectionCount;

	public function __construct(int $componentType, Random $random, StructureBoundingBox $box, int $facing, int $type)
	{
		parent::__construct($componentType, $type);
		$this->setOrientation($facing);
		$this->boundingBox = $box;
		$this->hasRails = $random->nextBoundedInt(3) === 0;
		$this->hasSpiders = !$this->hasRails && $random->nextBoundedInt(23) === 0;
		$this->sectionCount = intdiv(self::isAxisZ($facing) ? $box->getZSize() : $box->getXSize(), 5);
	}

	/**
	 * @param StructurePiece[] $pieces
	 */
	public static function findCorridorSize(array $pieces, Random $random, int $x, int $y, int $z, int $facing) : ?StructureBoundingBox
	{
		$box = new StructureBoundingBox($x, $y, $z, $x, $y + 2, $z);
		for ($sections = $random->nextBoundedInt(3) + 2; $sections > 0; --$sections) {
			$length = $sections * 5;
			switch ($facing) {
				case self::NORTH:
					$box->maxX = $x + 2;
					$box->minZ = $z - ($length - 1);
					break;
				case self::SOUTH:
					$box->maxX = $x + 2;
					$box->maxZ = $z + ($length - 1);
					break;
				case self::WEST:
					$box->minX = $x - ($length - 1);
					$box->maxZ = $z + 2;
					break;
				default:
					$box->maxX = $x + ($length - 1);
					$box->maxZ = $z + 2;
			}
			if (self::findIntersecting($pieces, $box) === null) {
				return $box;
			}
		}
		return null;
	}

	public function buildComponent(StructurePiece $start, array &$pieces, Random $random) : void
	{
		$type = $this->getComponentType();
		$roll = $random->nextBoundedInt(4);
		$bb = $this->boundingBox;
		switch ($this->orientation) {
			case self::NORTH:
				if ($roll <= 1) {
					self::generateAndAddPiece($start, $pieces, $random, $bb->minX, $bb->minY - 1 + $random->nextBoundedInt(3), $bb->minZ - 1, self::NORTH, $type);
				} elseif ($roll === 2) {
					self::generateAndAddPiece($start, $pieces, $random, $bb->minX - 1, $bb->minY - 1 + $random->nextBoundedInt(3), $bb->minZ, self::WEST, $type);
				} else {
					self::generateAndAddPiece($start, $pieces, $random, $bb->maxX + 1, $bb->minY - 1 + $random->nextBoundedInt(3), $bb->minZ, self::EAST, $type);
				}
				break;
			case self::SOUTH:
				if ($roll <= 1) {
					self::generateAndAddPiece($start, $pieces, $random, $bb->minX, $bb->minY - 1 + $random->nextBoundedInt(3), $bb->maxZ + 1, self::SOUTH, $type);
				} elseif ($roll === 2) {
					self::generateAndAddPiece($start, $pieces, $random, $bb->minX - 1, $bb->minY - 1 + $random->nextBoundedInt(3), $bb->maxZ - 3, self::WEST, $type);
				} else {
					self::generateAndAddPiece($start, $pieces, $random, $bb->maxX + 1, $bb->minY - 1 + $random->nextBoundedInt(3), $bb->maxZ - 3, self::EAST, $type);
				}
				break;
			case self::WEST:
				if ($roll <= 1) {
					self::generateAndAddPiece($start, $pieces, $random, $bb->minX - 1, $bb->minY - 1 + $random->nextBoundedInt(3), $bb->minZ, self::WEST, $type);
				} elseif ($roll === 2) {
					self::generateAndAddPiece($start, $pieces, $random, $bb->minX, $bb->minY - 1 + $random->nextBoundedInt(3), $bb->minZ - 1, self::NORTH, $type);
				} else {
					self::generateAndAddPiece($start, $pieces, $random, $bb->minX, $bb->minY - 1 + $random->nextBoundedInt(3), $bb->maxZ + 1, self::SOUTH, $type);
				}
				break;
			default:
				if ($roll <= 1) {
					self::generateAndAddPiece($start, $pieces, $random, $bb->maxX + 1, $bb->minY - 1 + $random->nextBoundedInt(3), $bb->minZ, self::EAST, $type);
				} elseif ($roll === 2) {
					self::generateAndAddPiece($start, $pieces, $random, $bb->maxX - 3, $bb->minY - 1 + $random->nextBoundedInt(3), $bb->minZ - 1, self::NORTH, $type);
				} else {
					self::generateAndAddPiece($start, $pieces, $random, $bb->maxX - 3, $bb->minY - 1 + $random->nextBoundedInt(3), $bb->maxZ + 1, self::SOUTH, $type);
				}
		}

		if ($type < 8) {
			if (!self::isAxisZ($this->orientation)) {
				for ($x = $bb->minX + 3; $x + 3 <= $bb->maxX; $x += 5) {
					$side = $random->nextBoundedInt(5);
					if ($side === 0) {
						self::generateAndAddPiece($start, $pieces, $random, $x, $bb->minY, $bb->minZ - 1, self::NORTH, $type + 1);
					} elseif ($side === 1) {
						self::generateAndAddPiece($start, $pieces, $random, $x, $bb->minY, $bb->maxZ + 1, self::SOUTH, $type + 1);
					}
				}
			} else {
				for ($z = $bb->minZ + 3; $z + 3 <= $bb->maxZ; $z += 5) {
					$side = $random->nextBoundedInt(5);
					if ($side === 0) {
						self::generateAndAddPiece($start, $pieces, $random, $bb->minX - 1, $bb->minY, $z, self::WEST, $type + 1);
					} elseif ($side === 1) {
						self::generateAndAddPiece($start, $pieces, $random, $bb->maxX + 1, $bb->minY, $z, self::EAST, $type + 1);
					}
				}
			}
		}
	}

	public function addComponentParts(StructureWorld $world, Random $random, StructureBoundingBox $box) : bool
	{
		if ($this->isLiquidInStructureBoundingBox($world, $box)) {
			return false;
		}
		$length = $this->sectionCount * 5 - 1;
		$planks = $this->getPlanks();
		$web = self::full(BlockIds::COBWEB);

		$this->fillWithAir($world, $box, 0, 0, 0, 2, 1, $length);
		$this->generateMaybeBox($world, $box, $random, 0.8, 0, 2, 0, 2, 2, $length, self::AIR, self::AIR, false, false);
		if ($this->hasSpiders) {
			$this->generateMaybeBox($world, $box, $random, 0.6, 0, 0, 0, 2, 1, $length, $web, self::AIR, false, true);
		}

		for ($section = 0; $section < $this->sectionCount; ++$section) {
			$z = 2 + $section * 5;
			$this->placeSupport($world, $box, 0, 0, $z, 2, 2, $random);
			$this->placeCobWeb($world, $box, $random, 0.1, 0, 2, $z - 1);
			$this->placeCobWeb($world, $box, $random, 0.1, 2, 2, $z - 1);
			$this->placeCobWeb($world, $box, $random, 0.1, 0, 2, $z + 1);
			$this->placeCobWeb($world, $box, $random, 0.1, 2, 2, $z + 1);
			$this->placeCobWeb($world, $box, $random, 0.05, 0, 2, $z - 2);
			$this->placeCobWeb($world, $box, $random, 0.05, 2, 2, $z - 2);
			$this->placeCobWeb($world, $box, $random, 0.05, 0, 2, $z + 2);
			$this->placeCobWeb($world, $box, $random, 0.05, 2, 2, $z + 2);

			if ($random->nextBoundedInt(100) === 0) {
				$this->placeChestMinecart($world, $box, $random, 2, 0, $z - 1);
			}
			if ($random->nextBoundedInt(100) === 0) {
				$this->placeChestMinecart($world, $box, $random, 0, 0, $z + 1);
			}

			if ($this->hasSpiders && !$this->spawnerPlaced) {
				$spawnerZ = $z - 1 + $random->nextBoundedInt(3);
				if ($this->isDark($world, 1, 0, $spawnerZ, $box) && $this->placeSpawner($world, $box, 1, 0, $spawnerZ, self::CAVE_SPIDER)) {
					$this->spawnerPlaced = true;
				}
			}
		}

		//floor over holes
		for ($x = 0; $x <= 2; ++$x) {
			for ($z = 0; $z <= $length; ++$z) {
				if ($this->isAirAt($world, $x, -1, $z, $box) && $this->isDark($world, $x, -1, $z, $box)) {
					$this->setBlock($world, $planks, $x, -1, $z, $box);
				}
			}
		}

		if ($this->hasRails) {
			$rail = self::full(BlockIds::RAIL, $this->railMeta(0, 1));
			for ($z = 0; $z <= $length; ++$z) {
				$below = $this->getBlock($world, 1, -1, $z, $box);
				if (($below >> Block::INTERNAL_METADATA_BITS) !== BlockIds::AIR && BlockFactory::fromFullBlock($below)->isSolid()) {
					$this->randomlyPlaceBlock($world, $box, $random, $this->isDark($world, 1, 0, $z, $box) ? 0.7 : 0.9, 1, 0, $z, $rail);
				}
			}
		}
		return true;
	}

	private function placeSupport(StructureWorld $world, StructureBoundingBox $box, int $x1, int $y1, int $z, int $y2, int $x2, Random $random) : void
	{
		if (!$this->isSupportingBox($world, $box, $x1, $x2, $y2, $z)) {
			return;
		}
		$planks = $this->getPlanks();
		$fence = $this->getFence();
		$this->fill($world, $box, $x1, $y1, $z, $x1, $y2 - 1, $z, $fence);
		$this->fill($world, $box, $x2, $y1, $z, $x2, $y2 - 1, $z, $fence);
		if ($random->nextBoundedInt(4) === 0) {
			$this->fill($world, $box, $x1, $y2, $z, $x1, $y2, $z, $planks);
			$this->fill($world, $box, $x2, $y2, $z, $x2, $y2, $z, $planks);
		} else {
			$this->fill($world, $box, $x1, $y2, $z, $x2, $y2, $z, $planks);
			//torches hanging on both sides of the beam
			$this->randomlyPlaceBlock($world, $box, $random, 0.05, $x1 + 1, $y2, $z - 1, self::full(BlockIds::TORCH, $this->torchMeta(0, 1)));
			$this->randomlyPlaceBlock($world, $box, $random, 0.05, $x1 + 1, $y2, $z + 1, self::full(BlockIds::TORCH, $this->torchMeta(0, -1)));
		}
	}

	private function placeCobWeb(StructureWorld $world, StructureBoundingBox $box, Random $random, float $chance, int $x, int $y, int $z) : void
	{
		if ($this->isDark($world, $x, $y, $z, $box)) {
			$this->randomlyPlaceBlock($world, $box, $random, $chance, $x, $y, $z, self::full(BlockIds::COBWEB));
		}
	}

	/**
	 * Rail with a minecart with chest on it, filled from the loot table when opened
	 */
	private function placeChestMinecart(StructureWorld $world, StructureBoundingBox $box, Random $random, int $x, int $y, int $z) : void
	{
		$wx = $this->getXWithOffset($x, $z);
		$wy = $this->getYWithOffset($y);
		$wz = $this->getZWithOffset($x, $z);
		if (!$box->isVecInside($wx, $wy, $wz) || !$this->isAirAt($world, $x, $y, $z, $box) || $this->isAirAt($world, $x, $y - 1, $z, $box)) {
			return;
		}
		$this->setBlock($world, self::full(BlockIds::RAIL, $random->nextBoolean() ? 0 : 1), $x, $y, $z, $box);

		$nbt = new CompoundTag();
		$nbt->setTag(new StringTag("id", "MinecartChest"));
		$nbt->setTag(new ListTag("Pos", [new DoubleTag("", $wx + 0.5), new DoubleTag("", $wy + 0.5), new DoubleTag("", $wz + 0.5)], NBT::TAG_Double));
		$nbt->setTag(new ListTag("Motion", [new DoubleTag("", 0.0), new DoubleTag("", 0.0), new DoubleTag("", 0.0)], NBT::TAG_Double));
		$nbt->setTag(new ListTag("Rotation", [new FloatTag("", 0.0), new FloatTag("", 0.0)], NBT::TAG_Float));
		$nbt->setTag(new StringTag("LootTable", self::LOOT_TABLE));
		$nbt->setTag(new LongTag("LootTableSeed", $random->nextSignedInt() ?: 1));
		$world->addEntity($nbt);
	}
}
