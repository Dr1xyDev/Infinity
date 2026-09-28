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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol;

use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\biome\BiomeDefinitionData;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\biome\BiomeDefinitionEntry;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\biome\chunkgen\BiomeTagsData;
use function array_map;
use function count;

class BiomeDefinitionListPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::BIOME_DEFINITION_LIST_PACKET;

	public string $namedtag = "";

	/**
	 * @var BiomeDefinitionEntry[]
	 * @phpstan-var list<BiomeDefinitionEntry>
	 */
	private array $entries = [];

	/**
	 * @generate-create-func
	 *
	 * @param BiomeDefinitionEntry[] $entries
	 * @phpstan-param list<BiomeDefinitionEntry> $entries
	 */
	public static function create(string $namedtag, array $entries) : self
	{
		$result = new self();
		$result->namedtag = $namedtag;
		$result->entries = $entries;
		return $result;
	}

	protected function decodePayload() : void
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_800) {
			/**
			 * @var BiomeDefinitionData[] $definitionDataByNameIndex
			 * @phpstan-var array<int, BiomeDefinitionData> $definitionDataByNameIndex
			 */
			$definitionDataByNameIndex = [];
			/**
			 * @var string[] $biomeNames
			 * @phpstan-var array<int, string> $biomeNames
			 */
			$biomeNames = [];

			for ($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i) {
				$biomeNameIndex = $this->getLShort();

				if (isset($definitionDataByNameIndex[$biomeNameIndex])) {
					throw new PacketDecodeException("Repeated biome name index \"$biomeNameIndex\"");
				}

				$definitionDataByNameIndex[$biomeNameIndex] = BiomeDefinitionData::read($this);
			}

			for ($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i) {
				$biomeNames[] = $this->getString();
			}

			$this->entries = [];
			foreach ($definitionDataByNameIndex as $nameIndex => $data) {
				if (!isset($biomeNames[$nameIndex])) {
					throw new PacketDecodeException("Biome name index \"$nameIndex\" not found");
				}

				if (($tags = $data->getTags()) === null) {
					$stringTags = null;
				} else {
					$stringTags = [];

					foreach ($tags->getIndexes() as $tagIndex) {
						if (!isset($biomeNames[$tagIndex])) {
							throw new PacketDecodeException("Biome tag index \"$tagIndex\" not found");
						}

						$stringTags[] = $biomeNames[$tagIndex];
					}
				}

				$this->entries[] = new BiomeDefinitionEntry(
					$biomeNames[$nameIndex],
					$data->getId(),
					$data->getTemperature(),
					$data->getDownfall(),
					$data->getFoliageSnow(),
					$data->getRedSporeDensity(),
					$data->getBlueSporeDensity(),
					$data->getAshDensity(),
					$data->getWhiteAshDensity(),
					$data->getDepth(),
					$data->getScale(),
					$data->getMapWaterColor(),
					$data->hasRain(),
					$stringTags,
					$data->getChunkGenData(),
				);
			}
		} else {
			$this->namedtag = $this->getRemaining();
		}
	}

	protected function encodePayload() : void
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_800) {
			/**
			 * @var int[] $biomeNameIndexes
			 * @phpstan-var array<string, int> $biomeNameIndexes
			 */
			$biomeNameIndexes = [];
			$indexGenerator = function (string $biomeName) use (&$biomeNameIndexes) : int {
				if (isset($biomeNameIndexes[$biomeName])) {
					return $biomeNameIndexes[$biomeName];
				}

				$biomeNameIndexes[$biomeName] = count($biomeNameIndexes);
				return $biomeNameIndexes[$biomeName];
			};

			$this->putUnsignedVarInt(count($this->entries));
			foreach ($this->entries as $entry) {
				$this->putLShort($indexGenerator($entry->getBiomeName()));

				(new BiomeDefinitionData(
					$entry->getId(),
					$entry->getTemperature(),
					$entry->getDownfall(),
					$entry->getFoliageSnow(),
					$entry->getRedSporeDensity(),
					$entry->getBlueSporeDensity(),
					$entry->getAshDensity(),
					$entry->getWhiteAshDensity(),
					$entry->getDepth(),
					$entry->getScale(),
					$entry->getMapWaterColor(),
					$entry->hasRain(),
					$entry->getTags() === null ? null : new BiomeTagsData(array_map($indexGenerator, $entry->getTags())),
					$entry->getChunkGenData(),
				))->write($this);
			}

			$this->putUnsignedVarInt(count($biomeNameIndexes));
			foreach ($biomeNameIndexes as $biomeName => $index) {
				$this->putString($biomeName);
			}
		} else {
			$this->put($this->namedtag);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleBiomeDefinitionList($this);
	}
}
