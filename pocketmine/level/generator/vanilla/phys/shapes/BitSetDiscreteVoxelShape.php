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

namespace pocketmine\level\generator\vanilla\phys\shapes;

use function array_fill;
use function array_values;
use function ceil;
use function max;
use function min;

final class BitSetDiscreteVoxelShape extends DiscreteVoxelShape {
	/** @var int[] */
	private array $storage;

	private int $xMin;
	private int $yMin;
	private int $zMin;
	private int $xMax;
	private int $yMax;
	private int $zMax;

	private const BITS_PER_WORD = 64;

	public function __construct(int $xSize, int $ySize, int $zSize)
	{
		parent::__construct($xSize, $ySize, $zSize);

		$totalBits = $xSize * $ySize * $zSize;
		$wordCount = (int) ceil($totalBits / self::BITS_PER_WORD);
		$this->storage = array_fill(0, $wordCount, 0);

		$this->xMin = $xSize;
		$this->yMin = $ySize;
		$this->zMin = $zSize;
		$this->xMax = 0;
		$this->yMax = 0;
		$this->zMax = 0;
	}

	public static function withFilledBounds(
		int $xSize, int $ySize, int $zSize,
		int $xMin, int $yMin, int $zMin,
		int $xMax, int $yMax, int $zMax
	) : self {
		$shape = new self($xSize, $ySize, $zSize);
		$shape->xMin = $xMin;
		$shape->yMin = $yMin;
		$shape->zMin = $zMin;
		$shape->xMax = $xMax;
		$shape->yMax = $yMax;
		$shape->zMax = $zMax;

		for ($x = $xMin; $x < $xMax; $x++) {
			for ($y = $yMin; $y < $yMax; $y++) {
				for ($z = $zMin; $z < $zMax; $z++) {
					$shape->fillUpdateBounds($x, $y, $z, false);
				}
			}
		}

		return $shape;
	}

	public function __constructFromShape(DiscreteVoxelShape $voxelShape)
	{
		$this->__construct($voxelShape->getXSize(), $voxelShape->getYSize(), $voxelShape->getZSize());

		if ($voxelShape instanceof self) {
			$this->storage = array_values($voxelShape->storage);
		} else {
			for ($x = 0; $x < $this->xSize; $x++) {
				for ($y = 0; $y < $this->ySize; $y++) {
					for ($z = 0; $z < $this->zSize; $z++) {
						if ($voxelShape->isFull($x, $y, $z)) {
							$this->setBit($this->getIndex($x, $y, $z));
						}
					}
				}
			}
		}

		$this->xMin = $voxelShape->firstFull(0);
		$this->yMin = $voxelShape->firstFull(1);
		$this->zMin = $voxelShape->firstFull(2);
		$this->xMax = $voxelShape->lastFull(0);
		$this->yMax = $voxelShape->lastFull(1);
		$this->zMax = $voxelShape->lastFull(2);
	}

	private function getIndex(int $x, int $y, int $z) : int
	{
		return ($x * $this->ySize + $y) * $this->zSize + $z;
	}

	private function setBit(int $index) : void
	{
		$word = $index >> 6;
		$bit = $index & 63;
		$this->storage[$word] |= (1 << $bit);
	}

	private function getBit(int $index) : bool
	{
		$word = $index >> 6;
		$bit = $index & 63;
		return ($this->storage[$word] & (1 << $bit)) !== 0;
	}

	public function isFull(int $x, int $y, int $z) : bool
	{
		if ($x < 0 || $y < 0 || $z < 0 || $x >= $this->xSize || $y >= $this->ySize || $z >= $this->zSize) {
			return false;
		}
		return $this->getBit($this->getIndex($x, $y, $z));
	}

	protected function isFullUnsafe(int $x, int $y, int $z) : bool
	{
		return $this->getBit($this->getIndex($x, $y, $z));
	}

	private function fillUpdateBounds(int $x, int $y, int $z, bool $updateBounds) : void
	{
		$this->setBit($this->getIndex($x, $y, $z));

		if ($updateBounds) {
			$this->xMin = min($this->xMin, $x);
			$this->yMin = min($this->yMin, $y);
			$this->zMin = min($this->zMin, $z);
			$this->xMax = max($this->xMax, $x + 1);
			$this->yMax = max($this->yMax, $y + 1);
			$this->zMax = max($this->zMax, $z + 1);
		}
	}

	public function fill(int $x, int $y, int $z) : void
	{
		if ($x < 0 || $y < 0 || $z < 0 || $x >= $this->xSize || $y >= $this->ySize || $z >= $this->zSize) {
			return;
		}
		$this->fillUpdateBounds($x, $y, $z, true);
	}

	public function isEmpty() : bool
	{
		foreach ($this->storage as $word) {
			if ($word !== 0) {
				return false;
			}
		}
		return true;
	}

	public function firstFull(int $axis) : int
	{
		return match ($axis) {
			0 => $this->xMin,
			1 => $this->yMin,
			2 => $this->zMin,
			default => throw new \InvalidArgumentException("Invalid axis")
		};
	}

	public function lastFull(int $axis) : int
	{
		return match ($axis) {
			0 => $this->xMax,
			1 => $this->yMax,
			2 => $this->zMax,
			default => throw new \InvalidArgumentException("Invalid axis")
		};
	}

	public static function forAllBoxesOptimized(DiscreteVoxelShape $voxelShape, callable $consumer, bool $mergeNeighbors) : void
	{
		$shape = $voxelShape instanceof self ? $voxelShape : new self($voxelShape->getXSize(), $voxelShape->getYSize(), $voxelShape->getZSize());
		if ($voxelShape instanceof self === false) {
			for ($x = 0; $x < $shape->xSize; $x++) {
				for ($y = 0; $y < $shape->ySize; $y++) {
					for ($z = 0; $z < $shape->zSize; $z++) {
						if ($voxelShape->isFull($x, $y, $z)) {
							$shape->setBit($shape->getIndex($x, $y, $z));
						}
					}
				}
			}
		}

		for ($y = 0; $y < $shape->ySize; $y++) {
			for ($x = 0; $x < $shape->xSize; $x++) {
				$lastStartZ = -1;

				for ($z = 0; $z <= $shape->zSize; $z++) {
					$full = $z < $shape->zSize && $shape->isFullUnsafe($x, $y, $z);

					if ($full) {
						if ($mergeNeighbors) {
							if ($lastStartZ === -1) {
								$lastStartZ = $z;
							}
						} else {
							$consumer($x, $y, $z, $x + 1, $y + 1, $z + 1);
						}
					} elseif ($lastStartZ !== -1) {
						$endX = $x + 1;
						$endY = $y + 1;

						$shape->clearZStrip($lastStartZ, $z, $x, $y);

						while ($endX < $shape->xSize && $shape->isZStripFull($lastStartZ, $z, $endX, $y)) {
							$shape->clearZStrip($lastStartZ, $z, $endX, $y);
							$endX++;
						}

						while ($endY < $shape->ySize && $shape->isXZRectangleFull($x, $endX, $lastStartZ, $z, $endY)) {
							for ($cx = $x; $cx < $endX; $cx++) {
								$shape->clearZStrip($lastStartZ, $z, $cx, $endY);
							}
							$endY++;
						}

						$consumer($x, $y, $lastStartZ, $endX, $endY, $z);
						$lastStartZ = -1;
					}
				}
			}
		}
	}

	private function isZStripFull(int $startZ, int $endZ, int $x, int $y) : bool
	{
		if ($x >= $this->xSize || $y >= $this->ySize) return false;
		$startIdx = $this->getIndex($x, $y, $startZ);
		$endIdx = $this->getIndex($x, $y, $endZ);
		$wordStart = $startIdx >> 6;
		$wordEnd = ($endIdx - 1) >> 6;

		if ($wordStart === $wordEnd) {
			return ($this->storage[$wordStart] & ((1 << ($endIdx & 63)) - 1) << ($startIdx & 63)) === ((1 << ($endIdx & 63)) - 1) << ($startIdx & 63);
		}

		if (($this->storage[$wordStart] & (~0 << ($startIdx & 63))) !== (~0 << ($startIdx & 63))) return false;
		for ($w = $wordStart + 1; $w < $wordEnd; $w++) {
			if ($this->storage[$w] !== -1) return false;
		}
		if (($this->storage[$wordEnd] & ((1 << ($endIdx & 63)) - 1)) !== ((1 << ($endIdx & 63)) - 1)) return false;

		return true;
	}

	private function isXZRectangleFull(int $startX, int $endX, int $startZ, int $endZ, int $y) : bool
	{
		for ($x = $startX; $x < $endX; $x++) {
			if (!$this->isZStripFull($startZ, $endZ, $x, $y)) {
				return false;
			}
		}
		return true;
	}

	private function clearZStrip(int $startZ, int $endZ, int $x, int $y) : void
	{
		$startIdx = $this->getIndex($x, $y, $startZ);
		$endIdx = $this->getIndex($x, $y, $endZ);
		for ($i = $startIdx; $i < $endIdx; $i++) {
			$word = $i >> 6;
			$bit = $i & 63;
			$this->storage[$word] &= ~(1 << $bit);
		}
	}

	public function forAllBoxes(callable $consumer, bool $merge = true) : void
	{
		if ($merge) {
			self::forAllBoxesOptimized($this, $consumer, true);
		} else {
			for ($x = 0; $x < $this->xSize; $x++) {
				for ($y = 0; $y < $this->ySize; $y++) {
					for ($z = 0; $z < $this->zSize; $z++) {
						if ($this->isFullUnsafe($x, $y, $z)) {
							$consumer($x, $y, $z, $x + 1, $y + 1, $z + 1);
						}
					}
				}
			}
		}
	}

	public function isInterior(int $x, int $y, int $z) : bool
	{
		return $x > 0 && $x < $this->xSize - 1 &&
			$y > 0 && $y < $this->ySize - 1 &&
			$z > 0 && $z < $this->zSize - 1 &&
			$this->isFullUnsafe($x, $y, $z) &&
			$this->isFullUnsafe($x - 1, $y, $z) &&
			$this->isFullUnsafe($x + 1, $y, $z) &&
			$this->isFullUnsafe($x, $y - 1, $z) &&
			$this->isFullUnsafe($x, $y + 1, $z) &&
			$this->isFullUnsafe($x, $y, $z - 1) &&
			$this->isFullUnsafe($x, $y, $z + 1);
	}
}
