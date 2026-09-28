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
use pocketmine\level\generator\vanilla\utils\Binary;

use function array_fill;
use function count;

class SubChunkPacketHeightMapInfo
{
	/**
	 * @param int[] $heights ZZZZXXXX key bit order
	 * @phpstan-param list<int> $heights
	 */
	public function __construct(private array $heights)
	{
		if (count($heights) !== 256) {
			throw new \InvalidArgumentException("Expected exactly 256 heightmap values");
		}
	}

	/** @return int[] */
	public function getHeights() : array
	{
		return $this->heights;
	}

	public function getHeight(int $x, int $z) : int
	{
		return $this->heights[(($z & 0xf) << 4) | ($x & 0xf)];
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		//since 1.26.50 the heightmap is a list of 16 rows, each one a list of 16 heights
		$rows = $in->getProtocol() >= ProtocolInfo::PROTOCOL_2193;
		$heights = [];
		for ($i = 0; $i < 256; ++$i) {
			if ($rows && ($i & 0xf) === 0) {
				$rowLength = $in->getUnsignedVarInt();
				if ($rowLength !== 16) {
					throw new PacketDecodeException("Expected height map row to hold exactly 16 heights, got $rowLength");
				}
			}
			$heights[] = Binary::signByte($in->getByte());
		}
		return new self($heights);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$rows = $out->getProtocol() >= ProtocolInfo::PROTOCOL_2193;
		for ($i = 0; $i < 256; ++$i) {
			if ($rows && ($i & 0xf) === 0) {
				$out->putUnsignedVarInt(16);
			}
			$out->putByte(Binary::unsignByte($this->heights[$i]));
		}
	}

	public static function allTooLow() : self
	{
		return new self(array_fill(0, 256, -1));
	}

	public static function allTooHigh() : self
	{
		return new self(array_fill(0, 256, 16));
	}

	public function isAllTooLow() : bool
	{
		foreach ($this->heights as $height) {
			if ($height >= 0) {
				return false;
			}
		}
		return true;
	}

	public function isAllTooHigh() : bool
	{
		foreach ($this->heights as $height) {
			if ($height <= 15) {
				return false;
			}
		}
		return true;
	}
}
