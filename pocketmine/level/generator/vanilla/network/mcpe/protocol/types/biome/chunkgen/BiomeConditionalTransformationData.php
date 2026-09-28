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
use function count;

final class BiomeConditionalTransformationData
{
	/**
	 * @param BiomeWeightedData[] $weightedBiomes
	 */
	public function __construct(
		private array $weightedBiomes,
		private int $conditionJSON,
		private int $minPassingNeighbors,
	) {
	}

	/**
	 * @return BiomeWeightedData[]
	 */
	public function getWeightedBiomes() : array
	{
		return $this->weightedBiomes;
	}

	public function getConditionJSON() : int
	{
		return $this->conditionJSON;
	}

	public function getMinPassingNeighbors() : int
	{
		return $this->minPassingNeighbors;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$weightedBiomes = [];
		for ($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i) {
			$weightedBiomes[] = BiomeWeightedData::read($in);
		}

		$conditionJSON = $in->getLShort();
		$minPassingNeighbors = $in->getLInt();

		return new self(
			$weightedBiomes,
			$conditionJSON,
			$minPassingNeighbors,
		);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putUnsignedVarInt(count($this->weightedBiomes));
		foreach ($this->weightedBiomes as $biome) {
			$biome->write($out);
		}

		$out->putLShort($this->conditionJSON);
		$out->putLInt($this->minPassingNeighbors);
	}
}
