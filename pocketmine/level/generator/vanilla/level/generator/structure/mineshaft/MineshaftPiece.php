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
use pocketmine\level\generator\vanilla\level\generator\structure\StructurePiece;
use pocketmine\level\generator\vanilla\level\generator\structure\StructureWorld;
use pocketmine\level\generator\vanilla\level\generator\structure\StructureBoundingBox;
use pocketmine\level\generator\vanilla\utils\Random;
use function abs;

abstract class MineshaftPiece extends StructurePiece
{
	public const TYPE_NORMAL = 0;
	/** Mesa (badlands) mineshafts are made of dark oak and generated higher */
	public const TYPE_MESA = 1;

	public const LOOT_TABLE = "loot_tables/chests/abandoned_mineshaft.json";

	/** Pieces further than this from the start are not generated */
	private const MAX_DISTANCE = 80;
	private const MAX_DEPTH = 8;

	public function __construct(int $componentType, protected int $type)
	{
		parent::__construct($componentType);
	}

	public function getType() : int
	{
		return $this->type;
	}

	protected function getPlanks() : int
	{
		return self::full(BlockIds::PLANKS, $this->type === self::TYPE_MESA ? 5 : 0);
	}

	protected function getFence() : int
	{
		return self::full(BlockIds::FENCE, $this->type === self::TYPE_MESA ? 5 : 0);
	}

	/**
	 * @param StructurePiece[] $pieces
	 */
	private static function createRandomShaftPiece(array $pieces, Random $random, int $x, int $y, int $z, int $facing, int $componentType, int $type) : ?MineshaftPiece
	{
		$roll = $random->nextBoundedInt(100);
		if ($roll >= 80) {
			$box = MineshaftCrossing::findCrossing($pieces, $random, $x, $y, $z, $facing);
			if ($box !== null) {
				return new MineshaftCrossing($componentType, $box, $facing, $type);
			}
		} elseif ($roll >= 70) {
			$box = MineshaftStairs::findStairs($pieces, $x, $y, $z, $facing);
			if ($box !== null) {
				return new MineshaftStairs($componentType, $box, $facing, $type);
			}
		} else {
			$box = MineshaftCorridor::findCorridorSize($pieces, $random, $x, $y, $z, $facing);
			if ($box !== null) {
				return new MineshaftCorridor($componentType, $random, $box, $facing, $type);
			}
		}
		return null;
	}

	/**
	 * @param StructurePiece[] $pieces
	 */
	protected static function generateAndAddPiece(StructurePiece $start, array &$pieces, Random $random, int $x, int $y, int $z, int $facing, int $componentType) : ?MineshaftPiece
	{
		if ($componentType > self::MAX_DEPTH) {
			return null;
		}
		$startBox = $start->getBoundingBox();
		if (abs($x - $startBox->minX) > self::MAX_DISTANCE || abs($z - $startBox->minZ) > self::MAX_DISTANCE) {
			return null;
		}
		$type = $start instanceof MineshaftPiece ? $start->getType() : self::TYPE_NORMAL;
		$piece = self::createRandomShaftPiece($pieces, $random, $x, $y, $z, $facing, $componentType + 1, $type);
		if ($piece !== null) {
			$pieces[] = $piece;
			$piece->buildComponent($start, $pieces, $random);
		}
		return $piece;
	}

	/**
	 * Whether the blocks above a support beam are solid
	 */
	protected function isSupportingBox(StructureWorld $world, StructureBoundingBox $box, int $x1, int $x2, int $y, int $z) : bool
	{
		for ($x = $x1; $x <= $x2; ++$x) {
			if ($this->isAirAt($world, $x, $y + 1, $z, $box)) {
				return false;
			}
		}
		return true;
	}
}
