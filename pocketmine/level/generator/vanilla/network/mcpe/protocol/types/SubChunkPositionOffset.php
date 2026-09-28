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
use pocketmine\level\generator\vanilla\utils\Binary;

final class SubChunkPositionOffset
{
	public function __construct(
		private int $xOffset,
		private int $yOffset,
		private int $zOffset,
	) {
		self::clampOffset($this->xOffset);
		self::clampOffset($this->yOffset);
		self::clampOffset($this->zOffset);
	}

	private static function clampOffset(int $v) : void
	{
		if ($v < INT8_MIN || $v > INT8_MAX) {
			throw new \InvalidArgumentException("Offsets must be within the range of a byte (" . INT8_MIN . " ... " . INT8_MAX . ")");
		}
	}

	public function getXOffset() : int
	{
		return $this->xOffset;
	}

	public function getYOffset() : int
	{
		return $this->yOffset;
	}

	public function getZOffset() : int
	{
		return $this->zOffset;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$xOffset = Binary::signByte($in->getByte());
		$yOffset = Binary::signByte($in->getByte());
		$zOffset = Binary::signByte($in->getByte());

		return new self($xOffset, $yOffset, $zOffset);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putByte($this->xOffset);
		$out->putByte($this->yOffset);
		$out->putByte($this->zOffset);
	}
}
