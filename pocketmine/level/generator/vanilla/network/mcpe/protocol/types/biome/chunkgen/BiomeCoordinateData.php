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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\biome\chunkgen;

use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;

final class BiomeCoordinateData
{
	public function __construct(
		private int $minValueType,
		private int $minValue,
		private int $maxValueType,
		private int $maxValue,
		private int $gridOffset,
		private int $gridStepSize,
		private int $distribution
	) {
	}

	public function getMinValueType() : int
	{
		return $this->minValueType;
	}

	public function getMinValue() : int
	{
		return $this->minValue;
	}

	public function getMaxValueType() : int
	{
		return $this->maxValueType;
	}

	public function getMaxValue() : int
	{
		return $this->maxValue;
	}

	public function getGridOffset() : int
	{
		return $this->gridOffset;
	}

	public function getGridStepSize() : int
	{
		return $this->gridStepSize;
	}

	public function getDistribution() : int
	{
		return $this->distribution;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$minValueType = $in->getVarInt();
		$minValue = $in->getLShort();
		$maxValueType = $in->getVarInt();
		$maxValue = $in->getLShort();
		$gridOffset = $in->getLInt();
		$gridStepSize = $in->getLInt();
		$distribution = $in->getVarInt();

		return new self(
			$minValueType,
			$minValue,
			$maxValueType,
			$maxValue,
			$gridOffset,
			$gridStepSize,
			$distribution
		);
	}

	public function write(NetworkBinaryStream $out) : void
	{

	}
}
