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

use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\biome\chunkgen\BiomeDefinitionChunkGenData;
use pocketmine\level\generator\vanilla\utils\Color;

final class BiomeDefinitionEntry
{
	/**
	 * @param string[]|null $tags
	 * @phpstan-param list<string>|null $tags
	 */
	public function __construct(
		private string $biomeName,
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
		private ?array $tags,
		private ?BiomeDefinitionChunkGenData $chunkGenData = null
	) {
	}

	public function getBiomeName() : string
	{
		return $this->biomeName;
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

	/**
	 * @return string[]|null
	 * @phpstan-return list<string>|null
	 */
	public function getTags() : ?array
	{
		return $this->tags;
	}

	public function getChunkGenData() : ?BiomeDefinitionChunkGenData
	{
		return $this->chunkGenData;
	}
}
