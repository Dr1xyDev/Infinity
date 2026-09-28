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

final class BiomeMultinoiseGenRulesData
{
	public function __construct(
		private float $temperature,
		private float $humidity,
		private float $altitude,
		private float $weirdness,
		private float $weight,
	) {
	}

	public function getTemperature() : float
	{
		return $this->temperature;
	}

	public function getHumidity() : float
	{
		return $this->humidity;
	}

	public function getAltitude() : float
	{
		return $this->altitude;
	}

	public function getWeirdness() : float
	{
		return $this->weirdness;
	}

	public function getWeight() : float
	{
		return $this->weight;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$temperature = $in->getLFloat();
		$humidity = $in->getLFloat();
		$altitude = $in->getLFloat();
		$weirdness = $in->getLFloat();
		$weight = $in->getLFloat();

		return new self(
			$temperature,
			$humidity,
			$altitude,
			$weirdness,
			$weight
		);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putLFloat($this->temperature);
		$out->putLFloat($this->humidity);
		$out->putLFloat($this->altitude);
		$out->putLFloat($this->weirdness);
		$out->putLFloat($this->weight);
	}
}
