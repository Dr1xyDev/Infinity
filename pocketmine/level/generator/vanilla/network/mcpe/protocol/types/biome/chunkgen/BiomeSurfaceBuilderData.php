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

final class BiomeSurfaceBuilderData{

	public function __construct(
		private ?BiomeSurfaceMaterialData $surfaceMaterial,
		private bool $defaultOverworldSurface,
		private bool $swampSurface,
		private bool $frozenOceanSurface,
		private bool $theEndSurface,
		private ?BiomeMesaSurfaceData $mesaSurface,
		private ?BiomeCappedSurfaceData $cappedSurface,
		private ?BiomeNoiseGradientSurfaceData $noiseGradientSurface,
	){}

	public function getSurfaceMaterial() : ?BiomeSurfaceMaterialData{ return $this->surfaceMaterial; }

	public function hasDefaultOverworldSurface() : bool{ return $this->defaultOverworldSurface; }

	public function hasSwampSurface() : bool{ return $this->swampSurface; }

	public function hasFrozenOceanSurface() : bool{ return $this->frozenOceanSurface; }

	public function hasTheEndSurface() : bool{ return $this->theEndSurface; }

	public function getMesaSurface() : ?BiomeMesaSurfaceData{ return $this->mesaSurface; }

	public function getCappedSurface() : ?BiomeCappedSurfaceData{ return $this->cappedSurface; }

	public function getNoiseGradientSurface() : ?BiomeNoiseGradientSurfaceData{ return $this->noiseGradientSurface; }

	public static function read(NetworkBinaryStream $in) : self{
		$surfaceMaterial = $in->getOptional(fn() => BiomeSurfaceMaterialData::read($in));
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_844) {
			$defaultOverworldSurface = $in->getBool();
		}

		$swampSurface = $in->getBool();
		$frozenOceanSurface = $in->getBool();
		$theEndSurface = $in->getBool();
		$mesaSurface = $in->getOptional(fn() => BiomeMesaSurfaceData::read($in));
		$cappedSurface = $in->getOptional(fn() => BiomeCappedSurfaceData::read($in));
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_975) {
			$noiseGradientSurface = $in->getOptional(fn() => BiomeNoiseGradientSurfaceData::read($in));
		}

		return new self(
			$surfaceMaterial,
			$defaultOverworldSurface,
			$swampSurface,
			$frozenOceanSurface,
			$theEndSurface,
			$mesaSurface,
			$cappedSurface,
			$noiseGradientSurface ?? null
		);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putOptional($this->surfaceMaterial, fn(BiomeSurfaceMaterialData $v) => $v->write($out));
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_844) {
			$out->putBool($this->defaultOverworldSurface);
		}

		$out->putBool($this->swampSurface);
		$out->putBool($this->frozenOceanSurface);
		$out->putBool($this->theEndSurface);
		$out->putOptional($this->mesaSurface, fn(BiomeMesaSurfaceData $v) => $v->write($out));
		$out->putOptional($this->cappedSurface, fn(BiomeCappedSurfaceData $v) => $v->write($out));
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_975) {
			$out->putOptional($this->noiseGradientSurface, fn(BiomeNoiseGradientSurfaceData $v) => $v->write($out));
		}
	}
}
