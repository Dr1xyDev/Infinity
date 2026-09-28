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

use pocketmine\level\generator\vanilla\math\Vector3;

use function array_slice;
use function count;
use function end;

class Path
{
	/* @var PathPoint[] */
	protected $points = [];

	protected $currentIndex = 0;

	public function __construct(array $points = [])
	{
		$this->points = $points;
	}

	public function havePath() : bool
	{
		return !empty($this->points) && $this->currentIndex < count($this->points) - 1;
	}

	public function getVectorByIndex(int $index) : ?Vector3
	{
		$point = $this->getPointByIndex($index);
		if ($point === null) {
			return null;
		}

		return new Vector3($point->x, $point->height, $point->y);
	}

	public function getFinalPathPoint() : ?PathPoint
	{
		return end($this->points);
	}

	public function getPointByIndex(int $index) : ?PathPoint
	{
		return $this->points[$index] ?? null;
	}

	public function removePoint(int $index) : void
	{
		unset($this->points[$index]);
	}

	/**
	 * @return PathPoint[]
	 */
	public function getPoints() : array
	{
		return $this->points;
	}

	public function getCurrentIndex() : int
	{
		return $this->currentIndex;
	}

	public function setCurrentIndex(int $currentIndex) : void
	{
		$this->currentIndex = $currentIndex;
	}

	public function limitPath(int $maxLength) : void
	{
		$this->points = array_slice($this->points, 0, $maxLength + 1);
	}
}
