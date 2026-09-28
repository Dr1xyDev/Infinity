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

namespace pocketmine\level\generator\vanilla\maps;

use pocketmine\level\generator\vanilla\nbt\BigEndianNBTStream;
use pocketmine\level\generator\vanilla\nbt\LittleEndianNBTStream;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\network\mcpe\cache\MapCache;
use pocketmine\Server;

use function file_get_contents;
use function file_put_contents;
use function is_file;
use function mkdir;
use function strval;

class MapManager
{
	/** @var MapData[] */
	protected static $maps = [];
	/** @var int */
	protected static $mapIdCounter = 0;

	private function __construct()
	{
		// NOOP
	}

	public static function setMapData(MapData $map) : void
	{
		self::$maps[$map->getId()] = $map;
	}

	public static function unsetMapData(int $mapId) : void
	{
		unset(self::$maps[$mapId]);
		MapCache::removeMap($mapId);
	}

	public static function getMapDataById(int $id) : ?MapData
	{
		if (!isset(self::$maps[$id])) {
			self::loadMapData($id);
		}
		return self::$maps[$id] ?? null;
	}

	public static function getNextId() : int
	{
		return self::$mapIdCounter++;
	}

	public static function loadIdCounts() : void
	{
		@mkdir($path = Server::getInstance()->getDataPath() . "maps/", 0777);
		$stream = new LittleEndianNBTStream();

		if (is_file($path . "idcounts.dat")) {
			/** @var CompoundTag $data */
			$data = $stream->read(file_get_contents($path . "idcounts.dat"));
			self::$mapIdCounter = $data->getInt("map", 0);
		}
	}

	public static function loadMapData(int $id) : void
	{
		@mkdir($path = Server::getInstance()->getDataPath() . "maps/");
		$stream = new BigEndianNBTStream();

		if (is_file($fp = $path . "map_" . strval($id) . ".dat")) {
			/** @var CompoundTag $data */
			$data = $stream->readCompressed(file_get_contents($fp));
			$mp = new MapData($id);
			$mp->readSaveData($data);

			self::setMapData($mp);
		}
	}

	public static function saveMaps() : void
	{
		@mkdir($path = Server::getInstance()->getDataPath() . "maps/", 0777);
		$stream = new LittleEndianNBTStream();

		$idcounts = new CompoundTag();
		$idcounts->setInt("map", self::$mapIdCounter);

		file_put_contents($path . "idcounts.dat", $stream->write($idcounts));
		$stream = new BigEndianNBTStream();

		foreach (self::$maps as $data) {
			if (!$data->isVirtual() && $data->isDirty()) {
				$tag = new CompoundTag("data");
				$data->writeSaveData($tag);

				file_put_contents($path . "map_" . strval($data->getId()) . ".dat", $stream->writeCompressed($tag));
			}
		}
	}

	public static function resetMaps() : void
	{
		self::$maps = [];
		MapCache::clearAll();
	}
}
