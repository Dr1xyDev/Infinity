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

use pocketmine\level\generator\vanilla\math\Vector2;

class PathPoint extends Vector2
{
	/** @var int */
	public $fScore = 0;
	public $gScore = 0;
	public float $height = 0;

	public function getHashCode() : int
	{
		return ($this->x * 397) ^ $this->y;
	}

	public function equals(Vector2 $v) : bool
	{
		return $this->x == $v->x && $this->y == $v->y;
	}
}
