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

use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\math\Vector3;
use function abs;
use function array_values;
use function count;
use function file_get_contents;
use function file_put_contents;
use function is_array;
use function is_file;
use function is_int;
use function json_decode;
use function json_encode;
use function rename;
use function usort;
use const JSON_THROW_ON_ERROR;

/**
 * Remembers where the nether portals of a world are, so linked portals are found without scanning the terrain.
 * Every portal is stored by the bottom-left block of its inside. The file is written when the world is saved.
 */
final class PortalIndex
{
	public const FILE_NAME = "portals.json";

	/** @var int[][] block hash => [x, y, z, axis] */
	private array $portals = [];
	private bool $dirty = false;

	public function __construct(private string $file)
	{
		$this->load();
	}

	private function load() : void
	{
		if (!is_file($this->file)) {
			return;
		}
		try {
			$data = json_decode((string) @file_get_contents($this->file), true, 4, JSON_THROW_ON_ERROR);
		} catch (\JsonException) {
			\GlobalLogger::get()->warning("Ignoring corrupted portal index " . $this->file);
			return;
		}
		if (!is_array($data)) {
			return;
		}
		foreach ($data as $entry) {
			if (is_array($entry) && count($entry) === 4 && is_int($entry[0] ?? null) && is_int($entry[1] ?? null) && is_int($entry[2] ?? null) && is_int($entry[3] ?? null)) {
				$this->portals[Level::blockHash($entry[0], $entry[1], $entry[2])] = [$entry[0], $entry[1], $entry[2], $entry[3]];
			}
		}
	}

	public function add(PortalShape $shape) : void
	{
		$hash = Level::blockHash($shape->getX(), $shape->getY(), $shape->getZ());
		$entry = [$shape->getX(), $shape->getY(), $shape->getZ(), $shape->getAxis()];
		if (($this->portals[$hash] ?? null) !== $entry) {
			$this->portals[$hash] = $entry;
			$this->dirty = true;
		}
	}

	public function remove(int $x, int $y, int $z) : void
	{
		$hash = Level::blockHash($x, $y, $z);
		if (isset($this->portals[$hash])) {
			unset($this->portals[$hash]);
			$this->dirty = true;
		}
	}

	/**
	 * Returns the portals inside the square with the given horizontal radius around the position, closest first
	 *
	 * @return int[][] list of [x, y, z, axis]
	 */
	public function getNearby(Vector3 $pos, int $radius) : array
	{
		$result = [];
		foreach ($this->portals as $entry) {
			if (abs($entry[0] - $pos->x) <= $radius && abs($entry[2] - $pos->z) <= $radius) {
				$result[] = $entry;
			}
		}
		usort($result, static fn(array $a, array $b) : int => self::distanceSquared($a, $pos) <=> self::distanceSquared($b, $pos));
		return $result;
	}

	/**
	 * @param int[] $entry
	 */
	private static function distanceSquared(array $entry, Vector3 $pos) : float
	{
		return ($entry[0] - $pos->x) ** 2 + ($entry[1] - $pos->y) ** 2 + ($entry[2] - $pos->z) ** 2;
	}

	/**
	 * @return int[][] list of [x, y, z, axis]
	 */
	public function getAll() : array
	{
		return array_values($this->portals);
	}

	public function save() : void
	{
		if (!$this->dirty) {
			return;
		}
		$tmp = $this->file . ".tmp";
		if (@file_put_contents($tmp, json_encode(array_values($this->portals), JSON_THROW_ON_ERROR)) !== false && @rename($tmp, $this->file)) {
			$this->dirty = false;
		} else {
			\GlobalLogger::get()->warning("Failed to save portal index " . $this->file);
		}
	}
}
