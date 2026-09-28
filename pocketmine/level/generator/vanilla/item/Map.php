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

namespace pocketmine\level\generator\vanilla\item;

use pocketmine\level\generator\vanilla\maps\MapData;
use pocketmine\level\generator\vanilla\maps\MapManager;
use pocketmine\level\generator\vanilla\nbt\tag\ByteTag;
use pocketmine\level\generator\vanilla\nbt\tag\IntTag;
use pocketmine\level\generator\vanilla\nbt\tag\LongTag;
use pocketmine\Player;

use function boolval;
use function intval;

class Map extends Item
{
	public const TAG_MAP_IS_SCALING = "map_is_scaling"; // TAG_Byte
	public const TAG_MAP_SCALE = "map_scale"; // TAG_Byte
	public const TAG_MAP_UUID = "map_uuid"; // TAG_Long
	public const TAG_MAP_DISPLAY_PLAYERS = "map_display_players"; // TAG_Byte
	public const TAG_MAP_NAME_INDEX = "map_name_index"; // TAG_Int
	public const TAG_MAP_IS_INIT = "map_is_init"; // TAG_Byte

	public function __construct(int $meta = 0)
	{
		parent::__construct(self::FILLED_MAP, $meta, "Map");
	}

	public function getMapData() : ?MapData
	{
		return MapManager::getMapDataById($this->getMapId());
	}

	public function onUpdate(Player $player) : void
	{
		/*if($this->isMapInit()){
			if($data = $this->getMapData()){
				$data->renderMap($player);

				$data->updateVisiblePlayers($player, $this);

				if($pk = $data->getMapInfo($player)->getPacket($data)){
					$player->sendDataPacket($pk);
				}
			}
		}*/
	}

	public function initMap(Player $player, int $scale) : void
	{
		$this->setMapId($id = MapManager::getNextId());
		$this->setMapInit(true);
		$this->setMapNameIndex($id + 1);

		$data = new MapData($id);
		$data->setScale($scale);
		$data->setDimension($player->level->getDimension());
		$data->calculateMapCenter($player->getFloorX(), $player->getFloorZ());

		//$data->onMapCrated($player);

		MapManager::setMapData($data);
	}

	public function getMaxStackSize() : int
	{
		return 1;
	}

	public function setMapId(int $mapId) : void
	{
		$this->setNamedTagEntry(new LongTag(self::TAG_MAP_UUID, $mapId));
	}

	public function getMapId() : int
	{
		return $this->getNamedTag()->getLong(self::TAG_MAP_UUID, 0, true);
	}

	public function setMapNameIndex(int $nameIndex) : void
	{
		$this->setNamedTagEntry(new IntTag(self::TAG_MAP_NAME_INDEX, $nameIndex));
	}

	public function getMapNameIndex() : int
	{
		return $this->getNamedTag()->getInt(self::TAG_MAP_NAME_INDEX, 0, true);
	}

	public function setMapDisplayPlayers(bool $value) : void
	{
		$this->setNamedTagEntry(new ByteTag(self::TAG_MAP_DISPLAY_PLAYERS, intval($value)));
	}

	public function isMapDisplayPlayers() : bool
	{
		return boolval($this->getNamedTag()->getByte(self::TAG_MAP_DISPLAY_PLAYERS, 0, true));
	}

	public function setMapInit(bool $value) : void
	{
		$this->setNamedTagEntry(new ByteTag(self::TAG_MAP_IS_INIT, intval($value)));
	}

	public function isMapInit() : bool
	{
		return boolval($this->getNamedTag()->getByte(self::TAG_MAP_IS_INIT, 0, true));
	}

	public function checkCompoundTag() : void
	{
		if (
			$this->nbt === null ||
			!$this->nbt->hasTag(self::TAG_MAP_IS_INIT, ByteTag::class)
		) {
			$this->setMapId($id = MapManager::getNextId());
			$this->setMapInit(true);
			$this->setMapNameIndex($id + 1);
		}
	}
}
