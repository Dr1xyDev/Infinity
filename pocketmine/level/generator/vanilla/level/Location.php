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

namespace pocketmine\level\generator\vanilla\level;

use pocketmine\level\generator\vanilla\math\Vector3;

class Location extends Position
{
	/** @var float */
	public $yaw;
	/** @var float */
	public $pitch;

	/**
	 * @param float|int $x
	 * @param float|int $y
	 * @param float|int $z
	 * @param float     $yaw
	 * @param float     $pitch
	 */
	public function __construct($x = 0, $y = 0, $z = 0, $yaw = 0.0, $pitch = 0.0, ?Level $level = null)
	{
		$this->yaw = $yaw;
		$this->pitch = $pitch;
		parent::__construct($x, $y, $z, $level);
	}

	/**
	 * @param Level|null $level default null
	 * @param float      $yaw   default 0.0
	 * @param float      $pitch default 0.0
	 */
	public static function fromObject(Vector3 $pos, ?Level $level = null, $yaw = 0.0, $pitch = 0.0) : Location
	{
		return new Location($pos->x, $pos->y, $pos->z, $yaw, $pitch, $level ?? (($pos instanceof Position) ? $pos->level : null));
	}

	/**
	 * Return a Location instance
	 */
	public function asLocation() : Location
	{
		return new Location($this->x, $this->y, $this->z, $this->yaw, $this->pitch, $this->level);
	}

	public function getYaw()
	{
		return $this->yaw;
	}

	public function getPitch()
	{
		return $this->pitch;
	}

	public function __toString()
	{
		return "Location (level=" . ($this->isValid() ? $this->getLevel()->getName() : "null") . ", x=$this->x, y=$this->y, z=$this->z, yaw=$this->yaw, pitch=$this->pitch)";
	}

	public function equals(Vector3 $v) : bool
	{
		if ($v instanceof Location) {
			return parent::equals($v) && $v->yaw == $this->yaw && $v->pitch == $this->pitch;
		}
		return parent::equals($v);
	}
}
