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

final class BiomeWeightedTemperatureData
{
	public function __construct(
		private int $temperature,
		private int $weight,
	) {
	}

	public function getTemperature() : int
	{
		return $this->temperature;
	}

	public function getWeight() : int
	{
		return $this->weight;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$temperature = $in->getVarInt();
		$weight = $in->getLInt();

		return new self(
			$temperature,
			$weight
		);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putVarInt($this->temperature);
		$out->putLInt($this->weight);
	}
}
