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

use pocketmine\level\generator\vanilla\math\Axis;
use pocketmine\level\generator\vanilla\math\AxisAlignedBB;

abstract class DiscreteVoxelShape
{
	protected int $xSize;
	protected int $ySize;
	protected int $zSize;

	public function __construct(int $xSize, int $ySize, int $zSize)
	{
		if ($xSize < 0 || $ySize < 0 || $zSize < 0) {
			throw new \InvalidArgumentException("All sizes must be non-negative: x=$xSize, y=$ySize, z=$zSize");
		}
		$this->xSize = $xSize;
		$this->ySize = $ySize;
		$this->zSize = $zSize;
	}

	/** Проверка с учётом границ */
	public function isFull(int $x, int $y, int $z) : bool
	{
		return $x >= 0 && $y >= 0 && $z >= 0 &&
			$x < $this->xSize && $y < $this->ySize && $z < $this->zSize;
	}

	abstract public function fill(int $x, int $y, int $z) : void;

	public function getSize(int $axis) : int
	{
		return match ($axis) {
			0 => $this->xSize, // Axis::X
			1 => $this->ySize, // Axis::Y
			2 => $this->zSize, // Axis::Z
			default => throw new \InvalidArgumentException("Invalid axis: $axis")
		};
	}

	public function getXSize() : int { return $this->xSize; }
	public function getYSize() : int { return $this->ySize; }
	public function getZSize() : int { return $this->zSize; }

	public function isEmpty() : bool
	{
		for ($axis = 0; $axis < 3; $axis++) {
			if ($this->firstFull($axis) >= $this->lastFull($axis)) {
				return true;
			}
		}
		return false;
	}

	abstract public function firstFull(int $axis) : int;
	abstract public function lastFull(int $axis) : int;

	public function firstFullAlong(int $mainAxis, int $b, int $c) : int
	{
		$size = $this->getSize($mainAxis);
		if ($b < 0 || $c < 0) return $size;

		$bAxis = ($mainAxis + 1) % 3;
		$cAxis = ($mainAxis + 2) % 3;
		if ($b >= $this->getSize($bAxis) || $c >= $this->getSize($cAxis)) return $size;

		for ($a = 0; $a < $size; $a++) {
			[$x, $y, $z] = $this->remap($mainAxis, $a, $b, $c);
			if ($this->isFull($x, $y, $z)) {
				return $a;
			}
		}
		return $size;
	}

	public function lastFullAlong(int $mainAxis, int $b, int $c) : int
	{
		if ($b < 0 || $c < 0) return 0;

		$bAxis = ($mainAxis + 1) % 3;
		$cAxis = ($mainAxis + 2) % 3;
		if ($b >= $this->getSize($bAxis) || $c >= $this->getSize($cAxis)) return 0;

		$size = $this->getSize($mainAxis);
		for ($a = $size - 1; $a >= 0; $a--) {
			[$x, $y, $z] = $this->remap($mainAxis, $a, $b, $c);
			if ($this->isFull($x, $y, $z)) {
				return $a + 1;
			}
		}
		return 0;
	}

	private function remap(int $mainAxis, int $a, int $b, int $c) : array
	{
		return match ($mainAxis) {
			0 => [$a, $b, $c],
			1 => [$b, $a, $c],
			2 => [$b, $c, $a],
		};
	}

	public function forAllEdges(callable $consumer, bool $mergeNeighbors = false) : void
	{
		for ($cycle = 0; $cycle < 3; $cycle++) {
			$this->forAllAxisEdges($consumer, $cycle, $mergeNeighbors);
		}
	}

	private function forAllAxisEdges(callable $consumer, int $cycle, bool $mergeNeighbors) : void
	{
		$aAxis = $cycle % 3;
		$bAxis = ($cycle + 1) % 3;
		$cAxis = ($cycle + 2) % 3;

		$aSize = $this->getSize($aAxis);
		$bSize = $this->getSize($bAxis);
		$cSize = $this->getSize($cAxis);

		for ($a = 0; $a <= $aSize; $a++) {
			for ($b = 0; $b <= $bSize; $b++) {
				$lastStart = -1;
				for ($c = 0; $c <= $cSize; $c++) {
					$fullSectors = 0;
					$oddSectors = 0;

					for ($da = 0; $da <= 1; $da++) {
						for ($db = 0; $db <= 1; $db++) {
							$ax = $a + $da - 1;
							$bx = $b + $db - 1;
							$cx = $c;

							[$x, $y, $z] = match ($cycle) {
								0 => [$ax, $bx, $cx],
								1 => [$bx, $cx, $ax],
								2 => [$cx, $ax, $bx],
							};

							if ($this->isFull($x, $y, $z)) {
								$fullSectors++;
								$oddSectors ^= ($da ^ $db);
							}
						}
					}

					$isEdge = $fullSectors === 1 || $fullSectors === 3 || ($fullSectors === 2 && ($oddSectors & 1) === 0);

					if ($isEdge) {
						if ($mergeNeighbors) {
							if ($lastStart === -1) $lastStart = $c;
						} else {
							[$x1, $y1, $z1] = $this->remapBack($cycle, $a, $b, $c);
							[$x2, $y2, $z2] = $this->remapBack($cycle, $a, $b, $c + 1);
							$consumer($x1, $y1, $z1, $x2, $y2, $z2);
						}
					} elseif ($lastStart !== -1) {
						[$x1, $y1, $z1] = $this->remapBack($cycle, $a, $b, $lastStart);
						[$x2, $y2, $z2] = $this->remapBack($cycle, $a, $b, $c);
						$consumer($x1, $y1, $z1, $x2, $y2, $z2);
						$lastStart = -1;
					}
				}
			}
		}
	}

	private function remapBack(int $cycle, int $a, int $b, int $c) : array
	{
		return match ($cycle) {
			0 => [$a, $b, $c],
			1 => [$c, $a, $b],
			2 => [$b, $c, $a],
		};
	}

	public function forAllFaces(callable $consumer) : void
	{
		for ($cycle = 0; $cycle < 3; $cycle++) {
			$this->forAllAxisFaces($consumer, $cycle);
		}
	}

	private function forAllAxisFaces(callable $consumer, int $cycle) : void
	{
		$aAxis = $cycle % 3;
		$bAxis = ($cycle + 1) % 3;
		$cAxis = ($cycle + 2) % 3;

		$aSize = $this->getSize($aAxis);
		$bSize = $this->getSize($bAxis);
		$cSize = $this->getSize($cAxis);

		$negativeFace = ($cAxis * 2);     // пример: 4 = DOWN, 5 = UP для Y
		$positiveFace = ($cAxis * 2 + 1);

		for ($a = 0; $a < $aSize; $a++) {
			for ($b = 0; $b < $bSize; $b++) {
				$lastFull = false;
				for ($c = 0; $c <= $cSize; $c++) {
					$full = $c < $cSize && $this->isFull(...$this->remapBack($cycle, $a, $b, $c));

					if (!$lastFull && $full) {
						[$x, $y, $z] = $this->remapBack($cycle, $a, $b, $c);
						$consumer($negativeFace, $x, $y, $z);
					}
					if ($lastFull && !$full) {
						[$x, $y, $z] = $this->remapBack($cycle, $a, $b, $c - 1);
						$consumer($positiveFace, $x, $y, $z);
					}
					$lastFull = $full;
				}
			}
		}
	}

	abstract public function forAllBoxes(callable $consumer, bool $merge = true) : void;

	public function getBoundingBoxes(bool $merge = true) : array
	{
		$boxes = [];
		$this->forAllBoxes(function (int $x1, int $y1, int $z1, int $x2, int $y2, int $z2) use (&$boxes) {
			$boxes[] = new AxisAlignedBB(
				$x1 / 16.0, $y1 / 16.0, $z1 / 16.0,
				$x2 / 16.0, $y2 / 16.0, $z2 / 16.0
			);
		}, $merge);
		return $boxes;
	}
}
