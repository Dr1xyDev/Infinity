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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\biome;

use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\biome\chunkgen\BiomeDefinitionChunkGenData;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\biome\chunkgen\BiomeTagsData;
use pocketmine\level\generator\vanilla\utils\Color;

final class BiomeDefinitionData
{
	public function __construct(
		private ?int $id,
		private float $temperature,
		private float $downfall,
		private float $foliageSnow,
		private float $redSporeDensity,
		private float $blueSporeDensity,
		private float $ashDensity,
		private float $whiteAshDensity,
		private float $depth,
		private float $scale,
		private Color $mapWaterColor,
		private bool $rain,
		private ?BiomeTagsData $tags,
		private ?BiomeDefinitionChunkGenData $chunkGenData = null
	) {
	}

	public function getId() : ?int
	{
		return $this->id;
	}

	public function getTemperature() : float
	{
		return $this->temperature;
	}

	public function getDownfall() : float
	{
		return $this->downfall;
	}

	public function getFoliageSnow() : float
	{
		return $this->foliageSnow;
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

	public function getDepth() : float
	{
		return $this->depth;
	}

	public function getScale() : float
	{
		return $this->scale;
	}

	public function getMapWaterColor() : Color
	{
		return $this->mapWaterColor;
	}

	public function hasRain() : bool
	{
		return $this->rain;
	}

	public function getTags() : ?BiomeTagsData
	{
		return $this->tags;
	}

	public function getChunkGenData() : ?BiomeDefinitionChunkGenData
	{
		return $this->chunkGenData;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_827) {
			$id = $in->getLShort();
		} else {
			$id = $in->getOptional($in->getLShort(...));
		}

		$temperature = $in->getLFloat();
		$downfall = $in->getLFloat();
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_844) {
			$foliageSnow = $in->getLFloat();
		} else {
			$redSporeDensity = $in->getLFloat();
			$blueSporeDensity = $in->getLFloat();
			$ashDensity = $in->getLFloat();
			$whiteAshDensity = $in->getLFloat();
		}

		$depth = $in->getLFloat();
		$scale = $in->getLFloat();
		$mapWaterColor = Color::fromARGB($in->getLInt());
		$rain = $in->getBool();
		$tags = $in->getOptional(fn () => BiomeTagsData::read($in));
		$chunkGenData = $in->getOptional(fn () => BiomeDefinitionChunkGenData::read($in));

		return new self(
			$id,
			$temperature,
			$downfall,
			$foliageSnow ?? 0,
			$redSporeDensity ?? 0,
			$blueSporeDensity ?? 0,
			$ashDensity ?? 0,
			$whiteAshDensity ?? 0,
			$depth,
			$scale,
			$mapWaterColor,
			$rain,
			$tags,
			$chunkGenData
		);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_827) {
			$out->putLShort($this->id);
		} else {
			$out->putOptional($this->id, $out->putLShort(...));
		}

		$out->putLFloat($this->temperature);
		$out->putLFloat($this->downfall);
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_844) {
			$out->putLFloat($this->foliageSnow);
		} else {
			$out->putLFloat($this->redSporeDensity);
			$out->putLFloat($this->blueSporeDensity);
			$out->putLFloat($this->ashDensity);
			$out->putLFloat($this->whiteAshDensity);
		}

		$out->putLFloat($this->depth);
		$out->putLFloat($this->scale);
		$out->putLInt($this->mapWaterColor->toARGB());
		$out->putBool($this->rain);
		$out->putOptional($this->tags, fn (BiomeTagsData $tags) => $tags->write($out));
		$out->putOptional($this->chunkGenData, fn (BiomeDefinitionChunkGenData $chunkGenData) => $chunkGenData->write($out));
	}
}
