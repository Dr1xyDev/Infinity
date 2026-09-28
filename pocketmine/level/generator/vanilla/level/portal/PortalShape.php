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

namespace pocketmine\level\generator\vanilla\level\portal;

use pocketmine\level\generator\vanilla\block\BlockFactory;
use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\level\Position;
use pocketmine\level\generator\vanilla\math\Vector3;

/**
 * Nether portal frame, detected like vanilla does it: the corners of the frame are optional,
 * the inside is 2..21 blocks wide and 3..21 blocks high.
 */
final class PortalShape
{
	/** The portal plane runs along the X axis (portal block meta 1) */
	public const AXIS_X = 1;
	/** The portal plane runs along the Z axis (portal block meta 2) */
	public const AXIS_Z = 2;

	public const MIN_WIDTH = 2;
	public const MAX_WIDTH = 21;
	public const MIN_HEIGHT = 3;
	public const MAX_HEIGHT = 21;

	private function __construct(
		private int $axis,
		private int $x,
		private int $y,
		private int $z,
		private int $width,
		private int $height
	) {
	}

	/**
	 * Builds a shape from already known values without checking the world
	 */
	public static function create(int $axis, int $x, int $y, int $z, int $width = self::MIN_WIDTH, int $height = self::MIN_HEIGHT) : self
	{
		return new self($axis === self::AXIS_Z ? self::AXIS_Z : self::AXIS_X, $x, $y, $z, $width, $height);
	}

	/**
	 * Finds the portal frame around the given block which must be inside of it
	 *
	 * @param int|null $axis one of the AXIS_* constants, or null to try both
	 */
	public static function find(Level $level, int $x, int $y, int $z, ?int $axis = null) : ?self
	{
		foreach ($axis === null ? [self::AXIS_X, self::AXIS_Z] : [$axis] as $tryAxis) {
			$shape = self::findForAxis($level, $x, $y, $z, $tryAxis);
			if ($shape !== null) {
				return $shape;
			}
		}
		return null;
	}

	private static function findForAxis(Level $level, int $x, int $y, int $z, int $axis) : ?self
	{
		[$dx, $dz] = $axis === self::AXIS_X ? [1, 0] : [0, 1];

		if (!self::isEmpty($level, $x, $y, $z)) {
			return null;
		}

		//bottom of the inside
		for ($i = 0; $i < self::MAX_HEIGHT && $y > Level::Y_MIN && self::isEmpty($level, $x, $y - 1, $z); ++$i) {
			--$y;
		}
		if (!self::isFrame($level, $x, $y - 1, $z)) {
			return null;
		}

		//left edge of the inside
		for ($i = 0; $i < self::MAX_WIDTH; ++$i) {
			$nx = $x - $dx;
			$nz = $z - $dz;
			if (!self::isEmpty($level, $nx, $y, $nz) || !self::isFrame($level, $nx, $y - 1, $nz)) {
				break;
			}
			$x = $nx;
			$z = $nz;
		}
		if (!self::isFrame($level, $x - $dx, $y, $z - $dz)) {
			return null;
		}

		$width = 0;
		while ($width <= self::MAX_WIDTH && self::isEmpty($level, $x + $dx * $width, $y, $z + $dz * $width) && self::isFrame($level, $x + $dx * $width, $y - 1, $z + $dz * $width)) {
			++$width;
		}
		if ($width < self::MIN_WIDTH || $width > self::MAX_WIDTH || !self::isFrame($level, $x + $dx * $width, $y, $z + $dz * $width)) {
			return null;
		}

		$height = self::MAX_HEIGHT;
		for ($h = 0; $h < self::MAX_HEIGHT; ++$h) {
			$yy = $y + $h;
			if (!self::isFrame($level, $x - $dx, $yy, $z - $dz) || !self::isFrame($level, $x + $dx * $width, $yy, $z + $dz * $width)) {
				$height = $h;
				break;
			}
			for ($w = 0; $w < $width; ++$w) {
				if (!self::isEmpty($level, $x + $dx * $w, $yy, $z + $dz * $w)) {
					$height = $h;
					break 2;
				}
			}
		}
		if ($height < self::MIN_HEIGHT) {
			return null;
		}
		for ($w = 0; $w < $width; ++$w) {
			if (!self::isFrame($level, $x + $dx * $w, $y + $height, $z + $dz * $w)) {
				return null;
			}
		}

		return new self($axis, $x, $y, $z, $width, $height);
	}

	private static function isEmpty(Level $level, int $x, int $y, int $z) : bool
	{
		$id = $level->getBlockAt($x, $y, $z)->getId();
		return $id === BlockIds::AIR || $id === BlockIds::FIRE || $id === BlockIds::SOUL_FIRE || $id === BlockIds::PORTAL;
	}

	private static function isFrame(Level $level, int $x, int $y, int $z) : bool
	{
		return $level->getBlockAt($x, $y, $z)->getId() === BlockIds::OBSIDIAN;
	}

	public function getAxis() : int
	{
		return $this->axis;
	}

	/**
	 * Bottom-left block of the inside of the portal
	 */
	public function getX() : int
	{
		return $this->x;
	}

	public function getY() : int
	{
		return $this->y;
	}

	public function getZ() : int
	{
		return $this->z;
	}

	public function getWidth() : int
	{
		return $this->width;
	}

	public function getHeight() : int
	{
		return $this->height;
	}

	/**
	 * Returns whether every block inside the frame is a portal block
	 */
	public function isLit(Level $level) : bool
	{
		[$dx, $dz] = $this->axis === self::AXIS_X ? [1, 0] : [0, 1];
		for ($w = 0; $w < $this->width; ++$w) {
			for ($h = 0; $h < $this->height; ++$h) {
				if ($level->getBlockAt($this->x + $dx * $w, $this->y + $h, $this->z + $dz * $w)->getId() !== BlockIds::PORTAL) {
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Fills the inside of the frame with portal blocks facing the right way
	 */
	public function light(Level $level) : void
	{
		[$dx, $dz] = $this->axis === self::AXIS_X ? [1, 0] : [0, 1];
		$portal = BlockFactory::get(BlockIds::PORTAL, $this->axis);
		for ($w = 0; $w < $this->width; ++$w) {
			for ($h = 0; $h < $this->height; ++$h) {
				$level->setBlock(new Vector3($this->x + $dx * $w, $this->y + $h, $this->z + $dz * $w), $portal);
			}
		}
	}

	/**
	 * Builds an obsidian frame with the minimal size and lights it
	 *
	 * @param bool $withPlatform also places an obsidian floor to stand on and clears the space around the portal
	 */
	public function build(Level $level, bool $withPlatform) : void
	{
		[$dx, $dz] = $this->axis === self::AXIS_X ? [1, 0] : [0, 1];
		//perpendicular direction
		[$px, $pz] = [$dz, $dx];
		$obsidian = BlockFactory::get(BlockIds::OBSIDIAN);
		$air = BlockFactory::get(BlockIds::AIR);

		if ($withPlatform) {
			for ($d = -1; $d <= 1; ++$d) {
				if ($d === 0) {
					continue;
				}
				for ($w = 0; $w < $this->width; ++$w) {
					$bx = $this->x + $dx * $w + $px * $d;
					$bz = $this->z + $dz * $w + $pz * $d;
					$level->setBlock(new Vector3($bx, $this->y - 1, $bz), $obsidian);
					for ($h = 0; $h < $this->height; ++$h) {
						$level->setBlock(new Vector3($bx, $this->y + $h, $bz), $air);
					}
				}
			}
		}

		for ($w = -1; $w <= $this->width; ++$w) {
			for ($h = -1; $h <= $this->height; ++$h) {
				if ($w === -1 || $w === $this->width || $h === -1 || $h === $this->height) {
					$level->setBlock(new Vector3($this->x + $dx * $w, $this->y + $h, $this->z + $dz * $w), $obsidian);
				}
			}
		}

		$this->light($level);
	}

	/**
	 * Place where entities arrive: the middle of the bottom of the portal
	 */
	public function getDestination(Level $level) : Position
	{
		if ($this->axis === self::AXIS_X) {
			return new Position($this->x + $this->width / 2, $this->y, $this->z + 0.5, $level);
		}
		return new Position($this->x + 0.5, $this->y, $this->z + $this->width / 2, $level);
	}
}
