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

use InvalidArgumentException;
use pocketmine\level\generator\vanilla\math\Vector3;
use function assert;

class Position extends Vector3
{
	/** @var Level|null */
	public $level = null;

	/**
	 * @param float|int $x
	 * @param float|int $y
	 * @param float|int $z
	 */
	public function __construct($x = 0, $y = 0, $z = 0, ?Level $level = null)
	{
		parent::__construct($x, $y, $z);
		$this->setLevel($level);
	}

	public static function fromObject(Vector3 $pos, ?Level $level = null)
	{
		return new Position($pos->x, $pos->y, $pos->z, $level);
	}

	/**
	 * Return a Position instance
	 */
	public function asPosition() : Position
	{
		return new Position($this->x, $this->y, $this->z, $this->level);
	}

	/**
	 * Returns the target Level, or null if the target is not valid.
	 * If a reference exists to a Level which is closed, the reference will be destroyed and null will be returned.
	 *
	 * @return Level|null
	 */
	public function getLevel()
	{
		if ($this->level !== null && $this->level->isClosed()) {
			\GlobalLogger::get()->debug("Position was holding a reference to an unloaded world");
			$this->level = null;
		}

		return $this->level;
	}

	/**
	 * Sets the target Level of the position.
	 *
	 * @return $this
	 *
	 * @throws InvalidArgumentException if the specified Level has been closed
	 */
	public function setLevel(?Level $level = null)
	{
		if ($level !== null && $level->isClosed()) {
			throw new InvalidArgumentException("Specified world has been unloaded and cannot be used");
		}

		$this->level = $level;
		return $this;
	}

	/**
	 * Checks if this object has a valid reference to a loaded Level
	 */
	public function isValid() : bool
	{
		if ($this->level !== null && $this->level->isClosed()) {
			$this->level = null;

			return false;
		}

		return $this->level !== null;
	}

	/**
	 * Returns a side Vector
	 *
	 * @return Position
	 */
	public function getSide(int $side, int $step = 1)
	{
		assert($this->isValid());

		return Position::fromObject(parent::getSide($side, $step), $this->level);
	}

	public function __toString()
	{
		return "Position(level=" . ($this->isValid() ? $this->getLevel()->getName() : "null") . ",x=" . $this->x . ",y=" . $this->y . ",z=" . $this->z . ")";
	}

	public function equals(Vector3 $v) : bool
	{
		if ($v instanceof Position) {
			return parent::equals($v) && $v->getLevel() === $this->getLevel();
		}
		return parent::equals($v);
	}
}
