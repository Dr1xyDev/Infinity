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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;

final class BiomeClimateData
{
	public function __construct(
		private float $temperature,
		private float $downfall,
		private float $redSporeDensity,
		private float $blueSporeDensity,
		private float $ashDensity,
		private float $whiteAshDensity,
		private float $snowAccumulationMin,
		private float $snowAccumulationMax,
	) {
	}

	public function getTemperature() : float
	{
		return $this->temperature;
	}

	public function getDownfall() : float
	{
		return $this->downfall;
	}

	public function getRedSporeDensity() : float
	{
		return $this->redSporeDensity;
	}

	public function getBlueSporeDensity() : float
	{
		return $this->blueSporeDensity;
	}

	public function getAshDensity() : float
	{
		return $this->ashDensity;
	}

	public function getWhiteAshDensity() : float
	{
		return $this->whiteAshDensity;
	}

	public function getSnowAccumulationMin() : float
	{
		return $this->snowAccumulationMin;
	}

	public function getSnowAccumulationMax() : float
	{
		return $this->snowAccumulationMax;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$temperature = $in->getLFloat();
		$downfall = $in->getLFloat();
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_844) {
			$redSporeDensity = $in->getLFloat();
			$blueSporeDensity = $in->getLFloat();
			$ashDensity = $in->getLFloat();
			$whiteAshDensity = $in->getLFloat();
		}

		$snowAccumulationMin = $in->getLFloat();
		$snowAccumulationMax = $in->getLFloat();

		return new self(
			$temperature,
			$downfall,
			$redSporeDensity ?? 0,
			$blueSporeDensity ?? 0,
			$ashDensity ?? 0,
			$whiteAshDensity ?? 0,
			$snowAccumulationMin,
			$snowAccumulationMax
		);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putLFloat($this->temperature);
		$out->putLFloat($this->downfall);
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_844) {
			$out->putLFloat($this->redSporeDensity);
			$out->putLFloat($this->blueSporeDensity);
			$out->putLFloat($this->ashDensity);
			$out->putLFloat($this->whiteAshDensity);
		}

		$out->putLFloat($this->snowAccumulationMin);
		$out->putLFloat($this->snowAccumulationMax);
	}
}
