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

final class BiomeLegacyWorldGenRulesData
{
	/**
	 * @param BiomeConditionalTransformationData[] $legacyPreHills
	 */
	public function __construct(
		private array $legacyPreHills,
	) {
	}

	/**
	 * @return BiomeConditionalTransformationData[]
	 */
	public function getLegacyPreHills() : array
	{
		return $this->legacyPreHills;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$legacyPreHills = [];
		for ($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i) {
			$legacyPreHills[] = BiomeConditionalTransformationData::read($in);
		}

		return new self(
			$legacyPreHills,
		);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putUnsignedVarInt(count($this->legacyPreHills));
		foreach ($this->legacyPreHills as $biome) {
			$biome->write($out);
		}
	}
}
