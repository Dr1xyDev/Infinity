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

final class BiomeOverworldGenRulesData
{
	/**
	 * @param BiomeWeightedData[]                  $hillTransformations
	 * @param BiomeWeightedData[]                  $mutateTransformations
	 * @param BiomeWeightedData[]                  $riverTransformations
	 * @param BiomeWeightedData[]                  $shoreTransformations
	 * @param BiomeConditionalTransformationData[] $preHillsEdges
	 * @param BiomeConditionalTransformationData[] $postShoreEdges
	 * @param BiomeWeightedTemperatureData[]       $climates
	 */
	public function __construct(
		private array $hillTransformations,
		private array $mutateTransformations,
		private array $riverTransformations,
		private array $shoreTransformations,
		private array $preHillsEdges,
		private array $postShoreEdges,
		private array $climates,
	) {
	}

	/**
	 * @return BiomeWeightedData[]
	 */
	public function getHillTransformations() : array
	{
		return $this->hillTransformations;
	}

	/**
	 * @return BiomeWeightedData[]
	 */
	public function getMutateTransformations() : array
	{
		return $this->mutateTransformations;
	}

	/**
	 * @return BiomeWeightedData[]
	 */
	public function getRiverTransformations() : array
	{
		return $this->riverTransformations;
	}

	/**
	 * @return BiomeWeightedData[]
	 */
	public function getShoreTransformations() : array
	{
		return $this->shoreTransformations;
	}

	/**
	 * @return BiomeConditionalTransformationData[]
	 */
	public function getPreHillsEdges() : array
	{
		return $this->preHillsEdges;
	}

	/**
	 * @return BiomeConditionalTransformationData[]
	 */
	public function getPostShoreEdges() : array
	{
		return $this->postShoreEdges;
	}

	/**
	 * @return BiomeWeightedTemperatureData[]
	 */
	public function getClimates() : array
	{
		return $this->climates;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$hillTransformations = [];
		for ($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i) {
			$hillTransformations[] = BiomeWeightedData::read($in);
		}

		$mutateTransformations = [];
		for ($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i) {
			$mutateTransformations[] = BiomeWeightedData::read($in);
		}

		$riverTransformations = [];
		for ($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i) {
			$riverTransformations[] = BiomeWeightedData::read($in);
		}

		$shoreTransformations = [];
		for ($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i) {
			$shoreTransformations[] = BiomeWeightedData::read($in);
		}

		$preHillsEdges = [];
		for ($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i) {
			$preHillsEdges[] = BiomeConditionalTransformationData::read($in);
		}

		$postShoreEdges = [];
		for ($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i) {
			$postShoreEdges[] = BiomeConditionalTransformationData::read($in);
		}

		$climates = [];
		for ($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i) {
			$climates[] = BiomeWeightedTemperatureData::read($in);
		}

		return new self(
			$hillTransformations,
			$mutateTransformations,
			$riverTransformations,
			$shoreTransformations,
			$preHillsEdges,
			$postShoreEdges,
			$climates
		);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putUnsignedVarInt(count($this->hillTransformations));
		foreach ($this->hillTransformations as $transformation) {
			$transformation->write($out);
		}

		$out->putUnsignedVarInt(count($this->mutateTransformations));
		foreach ($this->mutateTransformations as $transformation) {
			$transformation->write($out);
		}

		$out->putUnsignedVarInt(count($this->riverTransformations));
		foreach ($this->riverTransformations as $transformation) {
			$transformation->write($out);
		}

		$out->putUnsignedVarInt(count($this->shoreTransformations));
		foreach ($this->shoreTransformations as $transformation) {
			$transformation->write($out);
		}

		$out->putUnsignedVarInt(count($this->preHillsEdges));
		foreach ($this->preHillsEdges as $edge) {
			$edge->write($out);
		}

		$out->putUnsignedVarInt(count($this->postShoreEdges));
		foreach ($this->postShoreEdges as $edge) {
			$edge->write($out);
		}

		$out->putUnsignedVarInt(count($this->climates));
		foreach ($this->climates as $climate) {
			$climate->write($out);
		}
	}
}
