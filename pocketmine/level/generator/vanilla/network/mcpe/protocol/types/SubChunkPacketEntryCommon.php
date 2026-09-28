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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types;

use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PacketDecodeException;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;

final class SubChunkPacketEntryCommon
{
	public function __construct(
		private SubChunkPositionOffset $offset,
		private int $requestResult,
		private string $terrainData,
		private ?SubChunkPacketHeightMapInfo $heightMap,
		private ?SubChunkPacketHeightMapInfo $renderHeightMap
	) {
	}

	public function getOffset() : SubChunkPositionOffset
	{
		return $this->offset;
	}

	public function getRequestResult() : int
	{
		return $this->requestResult;
	}

	public function getTerrainData() : string
	{
		return $this->terrainData;
	}

	public function getHeightMap() : ?SubChunkPacketHeightMapInfo
	{
		return $this->heightMap;
	}

	public function getRenderHeightMap() : ?SubChunkPacketHeightMapInfo
	{
		return $this->renderHeightMap;
	}

	public static function read(NetworkBinaryStream $in, bool $cacheEnabled) : self
	{
		$offset = SubChunkPositionOffset::read($in);

		$requestResult = $in->getByte();

		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_2168) {
			//since 1.26.40 the terrain data and the heightmap payloads are optionals
			$data = $in->getOptional($in->getString(...)) ?? "";
		} else {
			$data = !$cacheEnabled || $requestResult !== SubChunkRequestResult::SUCCESS_ALL_AIR ? $in->getString() : "";
		}

		$heightMapData = self::readHeightMap($in, $in->getByte(), null);

		$renderHeightMapData = null;
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_818) {
			$renderHeightMapData = self::readHeightMap($in, $in->getByte(), $heightMapData);
		}

		return new self(
			$offset,
			$requestResult,
			$data,
			$heightMapData,
			$renderHeightMapData
		);
	}

	private static function readHeightMap(NetworkBinaryStream $in, int $type, ?SubChunkPacketHeightMapInfo $copySource) : ?SubChunkPacketHeightMapInfo
	{
		$data = null;
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_2168) {
			$data = $in->getOptional(fn() => SubChunkPacketHeightMapInfo::read($in));
		} elseif ($type === SubChunkPacketHeightMapType::DATA) {
			$data = SubChunkPacketHeightMapInfo::read($in);
		}

		return match ($type) {
			SubChunkPacketHeightMapType::NO_DATA => null,
			SubChunkPacketHeightMapType::DATA => $data ?? throw new PacketDecodeException("Heightmap data type is DATA but no heightmap data was provided"),
			SubChunkPacketHeightMapType::ALL_TOO_HIGH => SubChunkPacketHeightMapInfo::allTooHigh(),
			SubChunkPacketHeightMapType::ALL_TOO_LOW => SubChunkPacketHeightMapInfo::allTooLow(),
			SubChunkPacketHeightMapType::ALL_COPIED => $copySource,
			default => throw new PacketDecodeException("Unknown heightmap data type $type")
		};
	}

	private static function writeHeightMap(NetworkBinaryStream $out, ?SubChunkPacketHeightMapInfo $heightMap, int $nullType) : void
	{
		if ($heightMap === null) {
			$type = $nullType;
		} elseif ($heightMap->isAllTooLow()) {
			$type = SubChunkPacketHeightMapType::ALL_TOO_LOW;
		} elseif ($heightMap->isAllTooHigh()) {
			$type = SubChunkPacketHeightMapType::ALL_TOO_HIGH;
		} else {
			$type = SubChunkPacketHeightMapType::DATA;
		}
		$out->putByte($type);

		$data = $type === SubChunkPacketHeightMapType::DATA ? $heightMap : null;
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_2168) {
			$out->putOptional($data, fn(SubChunkPacketHeightMapInfo $v) => $v->write($out));
		} elseif ($data !== null) {
			$data->write($out);
		}
	}

	public function write(NetworkBinaryStream $out, bool $cacheEnabled) : void
	{
		$this->offset->write($out);

		$out->putByte($this->requestResult);

		$hasTerrainData = !$cacheEnabled || $this->requestResult !== SubChunkRequestResult::SUCCESS_ALL_AIR;
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_2168) {
			$out->putOptional($hasTerrainData ? $this->terrainData : null, $out->putString(...));
		} elseif ($hasTerrainData) {
			$out->putString($this->terrainData);
		}

		self::writeHeightMap($out, $this->heightMap, SubChunkPacketHeightMapType::NO_DATA);

		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_818) {
			self::writeHeightMap($out, $this->renderHeightMap, SubChunkPacketHeightMapType::ALL_COPIED);
		}
	}
}
