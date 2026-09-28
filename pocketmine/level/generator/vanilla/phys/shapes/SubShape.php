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

use function max;
use function min;

final class SubShape extends DiscreteVoxelShape
{
	private DiscreteVoxelShape $parent;

	private int $startX;
	private int $startY;
	private int $startZ;
	private int $endX;
	private int $endY;
	private int $endZ;

	public function __construct(
		DiscreteVoxelShape $parent,
		int $startX,
		int $startY,
		int $startZ,
		int $endX,
		int $endY,
		int $endZ
	) {
		parent::__construct(
			$endX - $startX,
			$endY - $startY,
			$endZ - $startZ
		);

		$this->parent = $parent;
		$this->startX = $startX;
		$this->startY = $startY;
		$this->startZ = $startZ;
		$this->endX = $endX;
		$this->endY = $endY;
		$this->endZ = $endZ;

		if ($endX <= $startX || $endY <= $startY || $endZ <= $startZ) {
			throw new \InvalidArgumentException("SubShape size must be positive");
		}
	}

	public function isFull(int $x, int $y, int $z) : bool
	{
		return $this->parent->isFull(
			$this->startX + $x,
			$this->startY + $y,
			$this->startZ + $z
		);
	}

	public function fill(int $x, int $y, int $z) : void
	{
		$this->parent->fill(
			$this->startX + $x,
			$this->startY + $y,
			$this->startZ + $z
		);
	}

	public function firstFull(int $axis) : int
	{
		return $this->clampToShape($axis, $this->parent->firstFull($axis));
	}

	public function lastFull(int $axis) : int
	{
		return $this->clampToShape($axis, $this->parent->lastFull($axis));
	}

	/**
	 * Приводит координату из родительской формы в локальные координаты SubShape.
	 *
	 * @param int $axis         0 = X, 1 = Y, 2 = Z
	 * @param int $parentResult Значение из родителя
	 */
	private function clampToShape(int $axis, int $parentResult) : int
	{
		$start = $this->getStart($axis);
		$end = $this->getEnd($axis);

		// Аналог Mth.clamp(parentResult, start, end) - start
		$clamped = max($start, min($parentResult, $end));

		return $clamped - $start;
	}

	private function getStart(int $axis) : int
	{
		return match ($axis) {
			0 => $this->startX,
			1 => $this->startY,
			2 => $this->startZ,
			default => throw new \InvalidArgumentException("Invalid axis")
		};
	}

	private function getEnd(int $axis) : int
	{
		return match ($axis) {
			0 => $this->endX,
			1 => $this->endY,
			2 => $this->endZ,
			default => throw new \InvalidArgumentException("Invalid axis")
		};
	}

	/**
	 * SubShape не хранит свои данные — все операции делегируются родителю.
	 * Поэтому методы firstFull/lastFull/rotate и т.д. уже реализованы в parent.
	 * Здесь переопределяем только те, что зависят от смещения.
	 */
	public function forAllBoxes(callable $consumer, bool $merge = true) : void
	{
		// SubShape просто передаёт вызов родителю, но с учётом смещения
		$this->parent->forAllBoxes(function (int $x1, int $y1, int $z1, int $x2, int $y2, int $z2) use ($consumer) {
			$consumer(
				$x1 - $this->startX,
				$y1 - $this->startY,
				$z1 - $this->startZ,
				$x2 - $this->startX,
				$y2 - $this->startY,
				$z2 - $this->startZ
			);
		}, $merge);
	}
}
