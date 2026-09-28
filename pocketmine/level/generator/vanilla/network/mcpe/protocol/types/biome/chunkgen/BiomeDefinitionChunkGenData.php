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
use function count;

final class BiomeDefinitionChunkGenData
{

	/**
	 * @param BiomeReplacementData[] $replacementsData
	 */
	public function __construct(
		private ?BiomeClimateData $climate,
		private ?BiomeConsolidatedFeaturesData $consolidatedFeatures,
		private ?BiomeMountainParamsData $mountainParams,
		private ?BiomeSurfaceMaterialAdjustmentData $surfaceMaterialAdjustment,
		private ?BiomeOverworldGenRulesData $overworldGenRules,
		private ?BiomeMultinoiseGenRulesData $multinoiseGenRules,
		private ?BiomeLegacyWorldGenRulesData $legacyWorldGenRules,
		private ?array $replacementsData,
		private ?int $villageType,
		private ?BiomeSurfaceBuilderData $surfaceBuilderData,
		private ?BiomeSurfaceBuilderData $subSurfaceBuilderData
	) {
	}

	public function getClimate() : ?BiomeClimateData{
		return $this->climate;
	}

	public function getConsolidatedFeatures() : ?BiomeConsolidatedFeaturesData{
		return $this->consolidatedFeatures;
	}

	public function getMountainParams() : ?BiomeMountainParamsData{
		return $this->mountainParams;
	}

	public function getSurfaceMaterialAdjustment() : ?BiomeSurfaceMaterialAdjustmentData{
		return $this->surfaceMaterialAdjustment;
	}

	public function getOverworldGenRules() : ?BiomeOverworldGenRulesData{
		return $this->overworldGenRules;
	}

	public function getMultinoiseGenRules() : ?BiomeMultinoiseGenRulesData{
		return $this->multinoiseGenRules;
	}

	public function getLegacyWorldGenRules() : ?BiomeLegacyWorldGenRulesData{
		return $this->legacyWorldGenRules;
	}

	/**
	 * @return BiomeReplacementData[]
	 */
	public function getReplacementsData() : ?array{ return $this->replacementsData; }

	public function getVillageType() : ?int{ return $this->villageType; }

	public function getSurfaceBuilderData() : ?BiomeSurfaceBuilderData{ return $this->surfaceBuilderData; }

	public function getSubSurfaceBuilderData() : ?BiomeSurfaceBuilderData{ return $this->subSurfaceBuilderData; }

	public static function read(NetworkBinaryStream $in) : self
	{
		$climate = $in->getOptional(fn () => BiomeClimateData::read($in));
		$consolidatedFeatures = $in->getOptional(fn () => BiomeConsolidatedFeaturesData::read($in));
		$mountainParams = $in->getOptional(fn () => BiomeMountainParamsData::read($in));
		$surfaceMaterialAdjustment = $in->getOptional(fn () => BiomeSurfaceMaterialAdjustmentData::read($in));
		if ($in->getProtocol() < ProtocolInfo::PROTOCOL_975) {
			$surfaceBuilderData = BiomeSurfaceBuilderData::read($in);
		}

		$overworldGenRules = $in->getOptional(fn () => BiomeOverworldGenRulesData::read($in));
		$multinoiseGenRules = $in->getOptional(fn () => BiomeMultinoiseGenRulesData::read($in));
		$legacyWorldGenRules = $in->getOptional(fn () => BiomeLegacyWorldGenRulesData::read($in));
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_859) {
			$replacementsData = $in->getOptional(function () use ($in) : array {
				$result = [];
				for ($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i) {
					$result[] = BiomeReplacementData::read($in);
				}
				return $result;
			});

			if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_924) {
				$villageType = $in->getOptional($in->getByte(...));
				if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_975) {
					$surfaceBuilderData = $in->getOptional(fn() => BiomeSurfaceBuilderData::read($in));
					$subSurfaceBuilderData = $in->getOptional(fn() => BiomeSurfaceBuilderData::read($in));
				}
			}
		}

		return new self(
			$climate,
			$consolidatedFeatures,
			$mountainParams,
			$surfaceMaterialAdjustment,
			$overworldGenRules,
			$multinoiseGenRules,
			$legacyWorldGenRules,
			$replacementsData ?? null,
			$villageType ?? null,
			$surfaceBuilderData ?? null,
			$subSurfaceBuilderData ?? null
		);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putOptional($this->climate, fn (BiomeClimateData $climate) => $climate->write($out));
		$out->putOptional($this->consolidatedFeatures, fn (BiomeConsolidatedFeaturesData $consolidatedFeatures) => $consolidatedFeatures->write($out));
		$out->putOptional($this->mountainParams, fn (BiomeMountainParamsData $mountainParams) => $mountainParams->write($out));
		$out->putOptional($this->surfaceMaterialAdjustment, fn (BiomeSurfaceMaterialAdjustmentData $surfaceMaterialAdjustment) => $surfaceMaterialAdjustment->write($out));
		if ($out->getProtocol() < ProtocolInfo::PROTOCOL_975) {
			($this->surfaceBuilderData ?? new BiomeSurfaceBuilderData(null, true, true, true, true, null, null, null))->write($out);
		}

		$out->putOptional($this->overworldGenRules, fn (BiomeOverworldGenRulesData $overworldGenRules) => $overworldGenRules->write($out));
		$out->putOptional($this->multinoiseGenRules, fn (BiomeMultinoiseGenRulesData $multinoiseGenRules) => $multinoiseGenRules->write($out));
		$out->putOptional($this->legacyWorldGenRules, fn (BiomeLegacyWorldGenRulesData $legacyWorldGenRules) => $legacyWorldGenRules->write($out));
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_859) {
			$out->putOptional($this->replacementsData, function(array $v) use ($out) : void{
				$out->putUnsignedVarInt(count($v));
				foreach($v as $item){
					$item->write($out);
				}
			});

			if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_924) {
				$out->putOptional($this->villageType, $out->putByte(...));
				if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_975) {
					$out->putOptional($this->surfaceBuilderData, fn(BiomeSurfaceBuilderData $v) => $v->write($out));
					$out->putOptional($this->subSurfaceBuilderData, fn(BiomeSurfaceBuilderData $v) => $v->write($out));
				}
			}
		}
	}
}
