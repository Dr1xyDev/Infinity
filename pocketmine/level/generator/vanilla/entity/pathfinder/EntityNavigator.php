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

namespace pocketmine\level\generator\vanilla\entity\pathfinder;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\Lava;
use pocketmine\level\generator\vanilla\block\Liquid;
use pocketmine\level\generator\vanilla\block\Water;
use pocketmine\level\generator\vanilla\entity\Mob;
use pocketmine\level\generator\vanilla\math\Facing;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\timings\Timings;

use function abs;
use function array_unshift;
use function array_values;
use function count;
use function floor;
use const PHP_INT_MAX;

class EntityNavigator
{
	public const PROCESSOR_TYPE_SWIM = 0;
	public const PROCESSOR_TYPE_WALK = 1;
	public const PROCESSOR_TYPE_FLY = 2;

	protected Mob $mob;

	protected array $neighbors = [
		[
			0, -1
		], [
			1, 0
		], [
			0, 1
		], [
			-1, 0
		], [
			-1, -1
		], [
			1, -1
		], [
			1, 1
		], [
			-1, 1
		]
	];

	protected ?Path $currentPath = null;
	protected bool $avoidsWater = false;
	protected bool $avoidsSun = false;
	protected float $speedMultiplier = 1.0;
	protected ?Vector3 $lastPoint = null;
	protected int $stuckTick = 0;
	protected ?PathPoint $movePoint = null;
	protected int $processorType = self::PROCESSOR_TYPE_WALK;

	public function __construct(Mob $mob)
	{
		$this->mob = $mob;
	}

	public function navigate(PathPoint $from, PathPoint $to, ?float $followRange = null) : array
	{
		Timings::$mobPathFinding->startTiming();

		if ($followRange === null) {
			$followRange = $this->mob->getFollowRange();
		}
		if ($followRange <= 0) {
			$followRange = 20; //base
		}
		$blockCache = [];
		$ticks = 0;
		$from->fScore = (int) $this->calculateGridDistance($from, $to);
		$last = $from;
		$path = [];
		$open = [$from->getHashCode() => $from];
		$currentY = $this->getUsableYCoordinate();
		$closed = [];
		$highScore = $from;

		while (!empty($open)) {
			$current = $last;
			if ($last !== $highScore) {
				// Find minimum fScore node in O(n) instead of sorting the entire set in O(n log n)
				$minScore = PHP_INT_MAX;
				$current = null;
				foreach ($open as $node) {
					if ($node->fScore < $minScore) {
						$minScore = $node->fScore;
						$current = $node;
					}
				}
				$currentY = $this->getBlockByPoint($current, $blockCache)->y;
			}

			$last = null;

			if ($current->equals($to)) {
				return $this->initPath($path, $current);
			}
			if ($ticks++ > $followRange * 2) {
				return $this->initPath($path, $highScore);
			}

			unset($open[$current->getHashCode()]);
			$closed[$current->getHashCode()] = $current;

			foreach ($this->getNeighbors($current, $blockCache, $currentY) as $n) {
				if (!isset($closed[$n->getHashCode()])) {
					$g = $current->gScore + $this->calculateBlockDistance($current, $n, $blockCache);
					if ($g >= $followRange) {
						return $this->initPath($path, $highScore);
					}

					if (isset($open[$n->getHashCode()])) {
						$og = $open[$n->getHashCode()];
						if ($g >= $og->gScore) {
							continue;
						}
					}
					$open[$n->getHashCode()] = $n;
					$path[$n->getHashCode()] = $current;

					$n->gScore = $g;
					$n->fScore = (int) ($g + $this->calculateGridDistance($n, $to));

					if ($n->fScore <= $highScore->fScore) {
						$highScore = $n;
						$last = $n;
					}
				}
			}
			if ($last !== null) {
				$currentY = $this->getBlockByPoint($last, $blockCache)->y;
			}
		}

		Timings::$mobPathFinding->stopTiming();

		return [];
	}

	public function getUsableYCoordinate() : int
	{
		switch ($this->processorType) {
			case self::PROCESSOR_TYPE_WALK:
				$y = $this->mob->getFloorY();

				if ($this->mob->isSwimmer()) {
					$currentBlock = $this->mob->level->getBlock($this->mob);
					$attempts = 0;
					while ($currentBlock instanceof Water && $attempts++ < 10) {
						$currentBlock = $currentBlock->getSide(Facing::UP);
						$y++;
					}
				}

				return $y;
			default:
				return $this->mob->getFloorY();
		}
	}

	/**
	 * @return array
	 */
	public function initPath(array $path, PathPoint $current)
	{
		$totalPath = [$current];
		while (isset($path[$current->getHashCode()])) {
			$current = $path[$current->getHashCode()];
			array_unshift($totalPath, $current);
		}
		unset($totalPath[0]);

		Timings::$mobPathFinding->stopTiming();

		return array_values($totalPath);
	}

	public function calculateGridDistance(PathPoint $from, PathPoint $to) : float
	{
		return abs($from->x - $to->x) + abs($from->y - $to->y);
	}

	public function calculateBlockDistance(PathPoint $from, PathPoint $to, array $cache) : float
	{
		$block1 = $this->getBlockByPoint($from, $cache);
		$block2 = $this->getBlockByPoint($to, $cache);

		if ($block1 === null || $block2 === null) {
			return 0;
		} else {
			$block1 = $block1->asVector3();
			$block2 = $block2->asVector3();
		}

		if ($this->mob->canClimb()) {
			$block1->y = $block2->y = 0;
		}

		return $block1->distanceSquared($block2);
	}

	public function getBlockByPoint(PathPoint $tile, array $cache) : ?Block
	{
		return $cache[$tile->getHashCode()] ?? null;
	}

	/**
	 * @return PathPoint[]
	 */
	public function getNeighbors(PathPoint $tile, array &$cache, int $startY) : array
	{
		$block = $this->mob->level->getBlockAt((int) $tile->x, $startY, (int) $tile->y);

		if (!isset($cache[$tile->getHashCode()])) {
			$cache[$tile->getHashCode()] = $block;
		}

		$list = [];
		$neighborCount = count($this->neighbors);
		for ($index = 0; $index < $neighborCount; ++$index) {
			$item = new PathPoint($tile->x + $this->neighbors[$index][0], $tile->y + $this->neighbors[$index][1]);
			// Check for too high steps

			$coord = new Vector3((int) $item->x, $block->y, (int) $item->y);
			$tb = $this->mob->level->getBlock($coord);
			if (!$tb->isPassable()) {
				if (!$this->canJumpAt($block)) {
					continue; // can't jump because top block is solid
				}

				if ($this->mob->canClimb() || $this->mob->isSwimmer() || $this->mob->canFly()) {
					$blockUp = $this->mob->level->getBlock($coord->getSide(Facing::UP));
					$canMove = false;
					for ($i = 0; $i < 10; $i++) {
						if ($this->isBlocked($blockUp->asVector3())) {
							$blockUp = $this->mob->level->getBlock($blockUp->getSide(Facing::UP));
							continue;
						}

						$canMove = true;
						break;
					}

					if (!$canMove || $this->isObstructed($blockUp)) {
						continue;
					}

					$cache[$item->getHashCode()] = $blockUp;
				} else {
					$blockUp = $this->mob->level->getBlock($coord->getSide(Facing::UP));
					if (!$blockUp->isPassable()) {
						// Can't jump
						continue;
					}

					if ($this->isObstructed($blockUp)) {
						continue;
					}

					$cache[$item->getHashCode()] = $blockUp;
				}
			} else {
				$blockDown = $this->mob->level->getBlock($coord->down());
				if (!$blockDown->isSolid() && !$this->mob->isSwimmer() && !($blockDown instanceof Liquid) && !$this->mob->canFly()) { // TODO: bug?
					if ($this->mob->canClimb()) {
						$canClimb = false;
						$blockDown = $this->mob->level->getBlock($blockDown->getSide(Facing::DOWN));
						for ($i = 0; $i < 10; $i++) {
							if (!$blockDown->isSolid()) {
								$blockDown = $this->mob->level->getBlock($blockDown->add(0, -1, 0));
								continue;
							}

							$canClimb = true;
							break;
						}

						if (!$canClimb) {
							continue;
						}

						$blockDown = $this->mob->level->getBlock($blockDown->getSide(Facing::UP));

						if ($this->isObstructed($blockDown)) {
							continue;
						}

						$cache[$item->getHashCode()] = $blockDown;
					} else {
						if (!$this->mob->level->getBlock($coord->getSide(Facing::DOWN, 2))->isSolid()) {
							// Will fall
							continue;
						}

						if ($this->isObstructed($blockDown)) {
							continue;
						}

						$cache[$item->getHashCode()] = $blockDown;
					}
				} else {
					if ($this->isObstructed($coord) || (!$this->mob->isSwimmer() && $this->avoidsWater && $blockDown instanceof Liquid)) {
						continue;
					}

					$cache[$item->getHashCode()] = $block;
				}
			}
			$item->height = $cache[$item->getHashCode()]->y;
			$list[$index] = $item;
		}

		$this->checkDiagonals($list);

		return $list;
	}

	public function checkDiagonals(array &$list) : void
	{
		$checkDiagonals = [
			0 => [
				4, 5
			], 1 => [
				5, 6
			], 2 => [
				6, 7
			], 3 => [
				4, 7
			]
		];

		foreach ($checkDiagonals as $index => $diagonal) {
			if (!isset($list[$index])) {
				foreach ($diagonal as $dia) {
					unset($list[$dia]);
				}
			}
		}
	}

	public function isObstructed(Vector3 $coord) : bool
	{
		for ($i = 1; $i < $this->mob->height; $i++) {
			if ($this->isBlocked($coord->add(0, $i, 0))) {
				return true;
			}
		}

		return false;
	}

	public function canJumpAt(Vector3 $pos) : bool
	{
		for ($i = 1; $i < $this->mob->height + 1; $i++) {
			if ($this->isBlocked($pos->add(0, $i, 0))) {
				return false;
			}
		}
		return true;
	}

	public function isBlocked(Vector3 $coord) : bool
	{
		$block = $this->mob->level->getBlock($coord);
		return !$block->isPassable();
	}

	/**
	 * Removes sunny path from current path
	 */
	public function removeSunnyPath() : void
	{
		if ($this->avoidsSun && $this->mob->level->isDayTime()) {
			if ($this->mob->level->canSeeSky($this->mob)) {
				return;
			}

			foreach ($this->currentPath->getPoints() as $i => $point) {
				if ($this->mob->level->canSeeSky(new Vector3($point->x, $point->height, $point->y))) {
					$this->currentPath->limitPath($i - 1);
					return;
				}
			}
		}
	}

	/**
	 * Follows current path by index
	 */
	public function pathFollow() : void
	{
		if ($this->currentPath !== null) {
			$length = count($this->currentPath->getPoints()) - 1;

			for ($i = $this->currentPath->getCurrentIndex(); $i < count($this->currentPath->getPoints()); ++$i) {
				if ($this->currentPath->getPointByIndex($i)->height != (int) floor($this->mob->y)) {
					$length = $i;
					break;
				}
			}

			$currentPoint = $this->currentPath->getPointByIndex($this->currentPath->getCurrentIndex());
			if (floor($this->mob->x) == $currentPoint->x && floor($this->mob->z) == $currentPoint->y) {
				$this->currentPath->setCurrentIndex($this->currentPath->getCurrentIndex() + 1);
			}

			for ($a = $length; $a >= $this->currentPath->getCurrentIndex(); --$a) {
				$vec = $this->currentPath->getVectorByIndex($a);
				$vec->y = floor($this->mob->y);
				if ($this->isClearBetweenPoints($this->mob->floor(), $vec)) {
					$this->currentPath->setCurrentIndex($a);
					break;
				}
			}
		}
	}

	public function isClearBetweenPoints(Vector3 $from, Vector3 $to, bool $onlySee = false) : bool
	{
		$distanceSq = $from->distanceSquared($to);
		$direction = $to->subtractVector($from)->normalize();

		$dirLenSq = $direction->lengthSquared();
		if ($distanceSq < $dirLenSq) {
			return true;
		}

		$rayX = $from->x;
		$rayY = $from->y;
		$rayZ = $from->z;
		$dirX = $direction->x;
		$dirY = $direction->y;
		$dirZ = $direction->z;

		do {
			if (!$this->isSafeToStandAt(new Vector3((int) floor($rayX), (int) floor($rayY), (int) floor($rayZ)), $onlySee)) {
				return false;
			}
			$rayX += $dirX;
			$rayY += $dirY;
			$rayZ += $dirZ;
			$dx = $rayX - $from->x;
			$dy = $rayY - $from->y;
			$dz = $rayZ - $from->z;
		} while ($distanceSq > ($dx * $dx + $dy * $dy + $dz * $dz));

		return true;
	}

	public function isSafeToStandAt(Vector3 $pos, bool $onlySee = false) : bool
	{
		if ($this->isObstructed($pos) && !$onlySee) {
			return false;
		} elseif ($this->isBlocked($pos)) {
			return false;
		} elseif (!$onlySee) {
			$block = $this->mob->level->getBlockAt($pos->x, $pos->y - 1, $pos->z);
			if (($block instanceof Water && !$this->mob->isSwimmer() && !$this->mob->canFly() && $this->avoidsWater) || $block instanceof Lava) { // TODO: Implement this for ZombiePigman and LavaSlime
				return false;
			} else {
				return true;
			}
		}
		return true;
	}

	public function setPath(Path $path) : void
	{
		$this->currentPath = $path;
		$this->removeSunnyPath();
	}

	public function getPath() : ?Path
	{
		return $this->currentPath;
	}

	public function havePath() : bool
	{
		return $this->currentPath !== null && $this->currentPath->havePath();
	}

	public function isBusy() : bool
	{
		return $this->havePath() || $this->movePoint !== null;
	}

	public function clearPath(bool $all = true) : void
	{
		$this->currentPath = null;
		$this->lastPoint = null;
		$this->stuckTick = 0;
		if ($all) {
			$this->movePoint = null;
		}
	}

	public function setAvoidsWater(bool $value) : void
	{
		$this->avoidsWater = $value;
	}

	public function setAvoidsSun(bool $value) : void
	{
		$this->avoidsSun = $value;
	}

	public function getAvoidsWater() : bool
	{
		return $this->avoidsWater;
	}

	public function getAvoidsSun() : bool
	{
		return $this->avoidsSun;
	}

	public function isSameDestination(Vector3 $point) : bool
	{
		return $this->havePath() && $this->currentPath->getVectorByIndex(count($this->currentPath->getPoints()) - 1)->equals($point);
	}

	public function tryMoveTo(Vector3 $pos, float $speed, ?float $followRange = null) : bool
	{
		if (!$this->isSameDestination($pos->floor())) {
			$this->setSpeedMultiplier($speed);
			$this->setPath($this->findPath($pos, $followRange));
			return true;
		}
		return false;
	}

	public function findPath(Vector3 $pos, ?float $followRange = null) : Path
	{
		return new Path($this->navigate(new PathPoint(floor($this->mob->x), floor($this->mob->z)), new PathPoint(floor($pos->x), floor($pos->z)), $followRange * 2));
	}

	/**
	 * Update for navigation movement
	 */
	public function onNavigateUpdate() : bool
	{
		if ($this->currentPath !== null) {
			if ($this->havePath()) {
				$this->pathFollow();
				$next = $this->currentPath->getPointByIndex($this->currentPath->getCurrentIndex());
				if ($next !== null) {
					$this->movePoint = $next;
				} else {
					$this->clearPath(false);
				}
			} else {
				$this->clearPath(false);
			}
		}

		if ($this->movePoint !== null) {
			$f = floor($this->mob->width + 1) / 2;
			$this->mob->getMoveHelper()->moveTo($this->movePoint->x + $f, $this->movePoint->height, $this->movePoint->y + $f, $this->speedMultiplier);

			$currentPos = $this->mob->floor();

			if ($currentPos->x == $this->movePoint->x && $currentPos->z == $this->movePoint->y) {
				$this->movePoint = null;
			}

			if ($this->lastPoint !== null && $currentPos->x == $this->lastPoint->x && $currentPos->z == $this->lastPoint->z) {
				$this->stuckTick++;

				if ($this->stuckTick > 100) {
					$this->clearPath();
				}
			} else {
				$this->lastPoint = $currentPos;
				$this->stuckTick = 0;
			}

			return true;
		}

		return false;
	}

	public function getSpeedMultiplier() : float
	{
		return $this->speedMultiplier;
	}

	public function setSpeedMultiplier(float $speedMultiplier) : void
	{
		$this->speedMultiplier = $speedMultiplier;
	}

	public function getProcessorType() : int
	{
		return $this->processorType;
	}

	public function setProcessorType(int $processorType) : void
	{
		$this->processorType = $processorType;
	}
}
