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

use function max;
use function min;
use const PHP_INT_MAX;
use const PHP_INT_MIN;

/**
 * Inclusive block box used by structure pieces
 */
final class StructureBoundingBox
{
	public function __construct(
		public int $minX,
		public int $minY,
		public int $minZ,
		public int $maxX,
		public int $maxY,
		public int $maxZ
	) {
	}

	public static function empty() : self
	{
		return new self(PHP_INT_MAX, PHP_INT_MAX, PHP_INT_MAX, PHP_INT_MIN, PHP_INT_MIN, PHP_INT_MIN);
	}

	/**
	 * Box of a new piece attached at x/y/z, facing the given direction. The offsets and sizes are relative to the
	 * piece: width runs to its right, depth in the direction it faces.
	 */
	public static function getComponentToAddBoundingBox(int $x, int $y, int $z, int $offsetWidth, int $offsetHeight, int $offsetDepth, int $width, int $height, int $depth, int $facing) : self
	{
		return match ($facing) {
			StructurePiece::NORTH => new self($x + $offsetWidth, $y + $offsetHeight, $z - $depth + 1 + $offsetDepth, $x + $width - 1 + $offsetWidth, $y + $height - 1 + $offsetHeight, $z + $offsetDepth),
			StructurePiece::SOUTH => new self($x + $offsetWidth, $y + $offsetHeight, $z + $offsetDepth, $x + $width - 1 + $offsetWidth, $y + $height - 1 + $offsetHeight, $z + $depth - 1 + $offsetDepth),
			StructurePiece::WEST => new self($x - $depth + 1 + $offsetDepth, $y + $offsetHeight, $z + $offsetWidth, $x + $offsetDepth, $y + $height - 1 + $offsetHeight, $z + $width - 1 + $offsetWidth),
			default => new self($x + $offsetDepth, $y + $offsetHeight, $z + $offsetWidth, $x + $depth - 1 + $offsetDepth, $y + $height - 1 + $offsetHeight, $z + $width - 1 + $offsetWidth),
		};
	}

	public function intersectsWith(self $other) : bool
	{
		return $this->maxX >= $other->minX && $this->minX <= $other->maxX &&
			$this->maxZ >= $other->minZ && $this->minZ <= $other->maxZ &&
			$this->maxY >= $other->minY && $this->minY <= $other->maxY;
	}

	public function intersectsWithXZ(int $minX, int $minZ, int $maxX, int $maxZ) : bool
	{
		return $this->maxX >= $minX && $this->minX <= $maxX && $this->maxZ >= $minZ && $this->minZ <= $maxZ;
	}

	public function expandTo(self $other) : void
	{
		$this->minX = min($this->minX, $other->minX);
		$this->minY = min($this->minY, $other->minY);
		$this->minZ = min($this->minZ, $other->minZ);
		$this->maxX = max($this->maxX, $other->maxX);
		$this->maxY = max($this->maxY, $other->maxY);
		$this->maxZ = max($this->maxZ, $other->maxZ);
	}

	public function offset(int $x, int $y, int $z) : void
	{
		$this->minX += $x;
		$this->minY += $y;
		$this->minZ += $z;
		$this->maxX += $x;
		$this->maxY += $y;
		$this->maxZ += $z;
	}

	public function isVecInside(int $x, int $y, int $z) : bool
	{
		return $x >= $this->minX && $x <= $this->maxX && $z >= $this->minZ && $z <= $this->maxZ && $y >= $this->minY && $y <= $this->maxY;
	}

	public function getXSize() : int
	{
		return $this->maxX - $this->minX + 1;
	}

	public function getYSize() : int
	{
		return $this->maxY - $this->minY + 1;
	}

	public function getZSize() : int
	{
		return $this->maxZ - $this->minZ + 1;
	}
}
