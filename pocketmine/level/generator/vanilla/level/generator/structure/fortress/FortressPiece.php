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

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\level\generator\structure\StructureBoundingBox;
use pocketmine\level\generator\vanilla\level\generator\structure\StructurePiece;
use pocketmine\level\generator\vanilla\level\generator\structure\StructureWorld;
use pocketmine\level\generator\vanilla\utils\Random;
use function abs;

/**
 * Base of the nether fortress pieces
 */
abstract class FortressPiece extends StructurePiece
{
	public const LOOT_TABLE = "loot_tables/chests/nether_bridge.json";

	/**
	 * Placement of the piece relative to the connection point: offset width/height/depth, width, height, depth
	 * @var int[]
	 */
	public const DIMENSIONS = [0, 0, 0, 1, 1, 1];

	private const MAX_DISTANCE = 112;
	private const MAX_DEPTH = 30;

	protected const NETHER_BRICK = BlockIds::NETHER_BRICK_BLOCK << Block::INTERNAL_METADATA_BITS;
	protected const FENCE = BlockIds::NETHER_BRICK_FENCE << Block::INTERNAL_METADATA_BITS;

	public function __construct(int $componentType, Random $random, StructureBoundingBox $box, int $facing)
	{
		parent::__construct($componentType);
		$this->setOrientation($facing);
		$this->boundingBox = $box;
	}

	/**
	 * Creates a piece of the given type at the connection point if it fits
	 *
	 * @param class-string<FortressPiece> $class
	 * @param StructurePiece[]            $pieces
	 */
	public static function createPiece(string $class, array $pieces, Random $random, int $x, int $y, int $z, int $facing, int $componentType) : ?FortressPiece
	{
		[$offsetWidth, $offsetHeight, $offsetDepth, $width, $height, $depth] = $class::DIMENSIONS;
		$box = StructureBoundingBox::getComponentToAddBoundingBox($x, $y, $z, $offsetWidth, $offsetHeight, $offsetDepth, $width, $height, $depth, $facing);
		if ($box->minY <= 10 || self::findIntersecting($pieces, $box) !== null) {
			return null;
		}
		return new $class($componentType, $random, $box, $facing);
	}

	/**
	 * @param StructurePiece[] $pieces
	 */
	private function generatePiece(FortressStartPiece $start, bool $castle, array $pieces, Random $random, int $x, int $y, int $z, int $facing, int $componentType) : ?FortressPiece
	{
		$weights = $start->getWeights($castle);
		$total = 0;
		$limited = false;
		foreach ($weights as $weight) {
			if ($weight->maxPlaceCount > 0 && $weight->placeCount < $weight->maxPlaceCount) {
				$limited = true;
			}
			$total += $weight->weight;
		}

		if ($limited && $total > 0 && $componentType <= self::MAX_DEPTH) {
			for ($attempt = 0; $attempt < 5; ++$attempt) {
				$roll = $random->nextBoundedInt($total);
				foreach ($weights as $weight) {
					$roll -= $weight->weight;
					if ($roll < 0) {
						if (!$weight->canPlace() || ($weight === $start->lastPlaced && !$weight->allowInRow)) {
							break;
						}
						$piece = self::createPiece($weight->pieceClass, $pieces, $random, $x, $y, $z, $facing, $componentType);
						if ($piece !== null) {
							++$weight->placeCount;
							$start->lastPlaced = $weight;
							if (!$weight->canPlace()) {
								$start->removeWeight($castle, $weight);
							}
							return $piece;
						}
					}
				}
			}
		}
		return self::createPiece(BridgeEnd::class, $pieces, $random, $x, $y, $z, $facing, $componentType);
	}

	/**
	 * @param StructurePiece[] $pieces
	 */
	private function generateAndAddPiece(FortressStartPiece $start, array &$pieces, Random $random, int $x, int $y, int $z, int $facing, int $componentType, bool $castle) : ?FortressPiece
	{
		$startBox = $start->getBoundingBox();
		if (abs($x - $startBox->minX) > self::MAX_DISTANCE || abs($z - $startBox->minZ) > self::MAX_DISTANCE) {
			return null;
		}
		$piece = $this->generatePiece($start, $castle, $pieces, $random, $x, $y, $z, $facing, $componentType + 1);
		if ($piece !== null) {
			$pieces[] = $piece;
			$start->pendingChildren[] = $piece;
		}
		return $piece;
	}

	/**
	 * Continues in the direction this piece faces
	 *
	 * @param StructurePiece[] $pieces
	 */
	protected function getNextComponentNormal(FortressStartPiece $start, array &$pieces, Random $random, int $offsetX, int $offsetY, bool $castle) : ?FortressPiece
	{
		$bb = $this->boundingBox;
		return match ($this->orientation) {
			self::NORTH => $this->generateAndAddPiece($start, $pieces, $random, $bb->minX + $offsetX, $bb->minY + $offsetY, $bb->minZ - 1, self::NORTH, $this->componentType, $castle),
			self::SOUTH => $this->generateAndAddPiece($start, $pieces, $random, $bb->minX + $offsetX, $bb->minY + $offsetY, $bb->maxZ + 1, self::SOUTH, $this->componentType, $castle),
			self::WEST => $this->generateAndAddPiece($start, $pieces, $random, $bb->minX - 1, $bb->minY + $offsetY, $bb->minZ + $offsetX, self::WEST, $this->componentType, $castle),
			self::EAST => $this->generateAndAddPiece($start, $pieces, $random, $bb->maxX + 1, $bb->minY + $offsetY, $bb->minZ + $offsetX, self::EAST, $this->componentType, $castle),
			default => null,
		};
	}

	/**
	 * Branches off to the west (north/south facing pieces) or north (east/west facing pieces)
	 *
	 * @param StructurePiece[] $pieces
	 */
	protected function getNextComponentX(FortressStartPiece $start, array &$pieces, Random $random, int $offsetY, int $offsetX, bool $castle) : ?FortressPiece
	{
		$bb = $this->boundingBox;
		return match ($this->orientation) {
			self::NORTH, self::SOUTH => $this->generateAndAddPiece($start, $pieces, $random, $bb->minX - 1, $bb->minY + $offsetY, $bb->minZ + $offsetX, self::WEST, $this->componentType, $castle),
			self::WEST, self::EAST => $this->generateAndAddPiece($start, $pieces, $random, $bb->minX + $offsetX, $bb->minY + $offsetY, $bb->minZ - 1, self::NORTH, $this->componentType, $castle),
			default => null,
		};
	}

	/**
	 * Branches off to the east (north/south facing pieces) or south (east/west facing pieces)
	 *
	 * @param StructurePiece[] $pieces
	 */
	protected function getNextComponentZ(FortressStartPiece $start, array &$pieces, Random $random, int $offsetY, int $offsetX, bool $castle) : ?FortressPiece
	{
		$bb = $this->boundingBox;
		return match ($this->orientation) {
			self::NORTH, self::SOUTH => $this->generateAndAddPiece($start, $pieces, $random, $bb->maxX + 1, $bb->minY + $offsetY, $bb->minZ + $offsetX, self::EAST, $this->componentType, $castle),
			self::WEST, self::EAST => $this->generateAndAddPiece($start, $pieces, $random, $bb->minX + $offsetX, $bb->minY + $offsetY, $bb->maxZ + 1, self::SOUTH, $this->componentType, $castle),
			default => null,
		};
	}

	/**
	 * Fence windows and battlements of the 13x13 castle rooms
	 */
	protected function buildBattlements(StructureWorld $world, StructureBoundingBox $box) : void
	{
		$nb = self::NETHER_BRICK;
		$fence = self::FENCE;
		for ($i = 1; $i <= 11; $i += 2) {
			$this->fill($world, $box, $i, 10, 0, $i, 11, 0, $fence);
			$this->fill($world, $box, $i, 10, 12, $i, 11, 12, $fence);
			$this->fill($world, $box, 0, 10, $i, 0, 11, $i, $fence);
			$this->fill($world, $box, 12, 10, $i, 12, 11, $i, $fence);
			$this->setBlock($world, $nb, $i, 13, 0, $box);
			$this->setBlock($world, $nb, $i, 13, 12, $box);
			$this->setBlock($world, $nb, 0, 13, $i, $box);
			$this->setBlock($world, $nb, 12, 13, $i, $box);
			if ($i !== 11) {
				$this->setBlock($world, $fence, $i + 1, 13, 0, $box);
				$this->setBlock($world, $fence, $i + 1, 13, 12, $box);
				$this->setBlock($world, $fence, 0, 13, $i + 1, $box);
				$this->setBlock($world, $fence, 12, 13, $i + 1, $box);
			}
		}
		$this->setBlock($world, $fence, 0, 13, 0, $box);
		$this->setBlock($world, $fence, 0, 13, 12, $box);
		$this->setBlock($world, $fence, 12, 13, 12, $box);
		$this->setBlock($world, $fence, 12, 13, 0, $box);
	}

	/**
	 * Cross shaped foundation with pillars of the 13x13 castle rooms
	 */
	protected function buildFoundation(StructureWorld $world, StructureBoundingBox $box) : void
	{
		$nb = self::NETHER_BRICK;
		$this->fill($world, $box, 4, 2, 0, 8, 2, 12, $nb);
		$this->fill($world, $box, 0, 2, 4, 12, 2, 8, $nb);
		$this->fill($world, $box, 4, 0, 0, 8, 1, 3, $nb);
		$this->fill($world, $box, 4, 0, 9, 8, 1, 12, $nb);
		$this->fill($world, $box, 0, 0, 4, 3, 1, 8, $nb);
		$this->fill($world, $box, 9, 0, 4, 12, 1, 8, $nb);
		for ($x = 4; $x <= 8; ++$x) {
			for ($z = 0; $z <= 2; ++$z) {
				$this->replaceAirAndLiquidDownwards($world, $nb, $x, -1, $z, $box);
				$this->replaceAirAndLiquidDownwards($world, $nb, $x, -1, 12 - $z, $box);
			}
		}
		for ($x = 0; $x <= 2; ++$x) {
			for ($z = 4; $z <= 8; ++$z) {
				$this->replaceAirAndLiquidDownwards($world, $nb, $x, -1, $z, $box);
				$this->replaceAirAndLiquidDownwards($world, $nb, 12 - $x, -1, $z, $box);
			}
		}
	}

	/**
	 * Floor below a 5x5 castle corridor down to the ground
	 */
	protected function buildCorridorPillars(StructureWorld $world, StructureBoundingBox $box, int $maxX = 4, int $maxZ = 4) : void
	{
		for ($x = 0; $x <= $maxX; ++$x) {
			for ($z = 0; $z <= $maxZ; ++$z) {
				$this->replaceAirAndLiquidDownwards($world, self::NETHER_BRICK, $x, -1, $z, $box);
			}
		}
	}

	/**
	 * @param StructurePiece[] $pieces
	 */
	public function buildComponent(StructurePiece $start, array &$pieces, Random $random) : void
	{
		if ($start instanceof FortressStartPiece) {
			$this->buildFortressComponent($start, $pieces, $random);
		}
	}

	/**
	 * @param StructurePiece[] $pieces
	 */
	protected function buildFortressComponent(FortressStartPiece $start, array &$pieces, Random $random) : void
	{
	}
}
