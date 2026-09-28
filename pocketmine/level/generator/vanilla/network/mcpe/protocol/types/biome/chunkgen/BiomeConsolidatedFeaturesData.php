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

final class BiomeConsolidatedFeaturesData
{
	/**
	 * @param BiomeConsolidatedFeatureData[] $features
	 */
	public function __construct(
		private array $features,
	) {
	}

	/**
	 * @return BiomeConsolidatedFeatureData[]
	 */
	public function getFeatures() : array
	{
		return $this->features;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$features = [];

		for ($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i) {
			$features[] = BiomeConsolidatedFeatureData::read($in);
		}

		return new self($features);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putUnsignedVarInt(count($this->features));
		foreach ($this->features as $feature) {
			$feature->write($out);
		}
	}
}
