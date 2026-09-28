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

namespace pocketmine\level\generator\vanilla\network\mcpe\convert;

use pocketmine\level\generator\vanilla\utils\AssumptionFailedError;
use pocketmine\level\generator\vanilla\utils\Filesystem;
use pocketmine\level\generator\vanilla\utils\Utils;
use function array_key_exists;
use function is_array;
use function is_numeric;
use function is_string;
use function json_decode;
use const pocketmine\BEDROCK_DATA_PATH;

/**
 * This class handles translation between network item ID+metadata to PocketMine-MP internal ID+metadata and vice versa.
 */
final class ItemTranslator
{
	/**
	 * @var int[]
	 * @phpstan-var array<int, int>
	 */
	private array $simpleCoreToNetMapping = [];
	/**
	 * @var int[]
	 * @phpstan-var array<int, int>
	 */
	private array $simpleNetToCoreMapping = [];

	/**
	 * runtimeId = array[internalId][metadata]
	 * @var int[][]
	 * @phpstan-var array<int, array<int, int>>
	 */
	private array $complexCoreToNetMapping = [];
	/**
	 * [internalId, metadata] = array[runtimeId]
	 * @var int[][]
	 * @phpstan-var array<int, array{int, int}>
	 */
	private array $complexNetToCoreMapping = [];

	/** @var self[] */
	private static array $instance = [];

	public static function getInstance(int $protocolVersion) : self
	{
		$protocolVersion = ProtocolConvertor::getInstance()->getItemPaletteProtocol($protocolVersion);
		if (!isset(self::$instance[$protocolVersion])) {
			self::$instance[$protocolVersion] = self::make($protocolVersion);
		}
		return self::$instance[$protocolVersion];
	}

	private static function make(int $protocolVersion) : self
	{
		$data = Filesystem::resourceGetContents(BEDROCK_DATA_PATH . "items/" . $protocolVersion . "/r16_to_current_item_map.json");
		$json = json_decode($data, true);
		if (!is_array($json) || !isset($json["simple"], $json["complex"]) || !is_array($json["simple"]) || !is_array($json["complex"])) {
			throw new AssumptionFailedError("Invalid item table format");
		}

		$legacyStringToIntMap = LegacyItemIdToStringIdMap::getInstance($protocolVersion);

		/** @phpstan-var array<string, int> $simpleMappings */
		$simpleMappings = [];
		foreach ($json["simple"] as $oldId => $newId) {
			if (!is_string($oldId) || !is_string($newId)) {
				throw new AssumptionFailedError("Invalid item table format");
			}

			$intId = $legacyStringToIntMap->stringToLegacy($oldId);
			if ($intId === null) {
				//new item without a fixed legacy ID - we can't handle this right now
				continue;
			}
			$simpleMappings[$newId] = $intId;
		}
		foreach (Utils::stringifyKeys($legacyStringToIntMap->getStringToLegacyMap()) as $stringId => $intId) {
			if (isset($simpleMappings[$stringId])) {
				throw new \UnexpectedValueException("Old ID $stringId collides with new ID");
			}
			$simpleMappings[$stringId] = $intId;
		}

		/** @phpstan-var array<string, array{int, int}> $complexMappings */
		$complexMappings = [];
		foreach ($json["complex"] as $oldId => $map) {
			if (!is_string($oldId) || !is_array($map)) {
				throw new AssumptionFailedError("Invalid item table format");
			}
			foreach ($map as $meta => $newId) {
				if (!is_numeric($meta) || !is_string($newId)) {
					throw new AssumptionFailedError("Invalid item table format");
				}
				$intId = $legacyStringToIntMap->stringToLegacy($oldId);
				if ($intId === null) {
					//new item without a fixed legacy ID - we can't handle this right now
					continue;
				}
				if (isset($complexMappings[$newId]) && $complexMappings[$newId][0] === $intId && $complexMappings[$newId][1] <= $meta) {
					//TODO: HACK! Multiple legacy ID/meta pairs can be mapped to the same new ID (see minecraft:log)
					//Assume that the first one is the most relevant for now
					//However, this could catch fire in the future if this assumption is broken
					continue;
				}
				$complexMappings[$newId] = [$intId, (int) $meta];
			}
		}

		return new self(GlobalItemTypeDictionary::getInstance($protocolVersion)->getDictionary(), $simpleMappings, $complexMappings);
	}

	/**
	 * @param int[]   $simpleMappings
	 * @param int[][] $complexMappings
	 * @phpstan-param array<string, int> $simpleMappings
	 * @phpstan-param array<string, array<int, int>> $complexMappings
	 */
	public function __construct(ItemTypeDictionary $dictionary, array $simpleMappings, array $complexMappings)
	{
		foreach ($dictionary->getEntries() as $entry) {
			$stringId = $entry->getStringId();
			$netId = $entry->getNumericId();
			if (isset($complexMappings[$stringId])) {
				[$id, $meta] = $complexMappings[$stringId];
				$this->complexCoreToNetMapping[$id][$meta] = $netId;
				$this->complexNetToCoreMapping[$netId] = [$id, $meta];
			} elseif (isset($simpleMappings[$stringId])) {
				$this->simpleCoreToNetMapping[$simpleMappings[$stringId]] = $netId;
				$this->simpleNetToCoreMapping[$netId] = $simpleMappings[$stringId];
			} else {
				//not all items have a legacy mapping - for now, we only support the ones that do
				continue;
			}
		}
	}

	/**
	 * @return int[]|null
	 * @phpstan-return array{int, int}|null
	 */
	public function toNetworkIdQuiet(int $internalId, int $internalMeta) : ?array
	{
		if ($internalMeta === -1) {
			$internalMeta = 0x7fff;
		}
		if (isset($this->complexCoreToNetMapping[$internalId][$internalMeta])) {
			return [$this->complexCoreToNetMapping[$internalId][$internalMeta], 0];
		}
		if (array_key_exists($internalId, $this->simpleCoreToNetMapping)) {
			return [$this->simpleCoreToNetMapping[$internalId], $internalMeta];
		}

		return null;
	}

	/**
	 * @return int[]
	 * @phpstan-return array{int, int}
	 */
	public function toNetworkId(int $internalId, int $internalMeta) : array
	{
		return $this->toNetworkIdQuiet($internalId, $internalMeta) ??
			$this->toNetworkIdQuiet($internalId, 0) ??
			$this->toNetworkIdQuiet(0, 0) ??
			throw new \InvalidArgumentException("Unmapped ID/metadata combination $internalId:$internalMeta");
	}

	/**
	 * @phpstan-param-out bool $isComplexMapping
	 * @return int[]
	 * @phpstan-return array{int, int}
	 * @throws TypeConversionException
	 */
	public function fromNetworkId(int $networkId, int $networkMeta, ?bool &$isComplexMapping = null) : array
	{
		if (isset($this->complexNetToCoreMapping[$networkId])) {
			if ($networkMeta !== 0) {
				throw new TypeConversionException("Unexpected non-zero network meta on complex item mapping");
			}
			$isComplexMapping = true;
			return $this->complexNetToCoreMapping[$networkId];
		}
		$isComplexMapping = false;
		if (isset($this->simpleNetToCoreMapping[$networkId])) {
			return [$this->simpleNetToCoreMapping[$networkId], $networkMeta];
		}
		throw new TypeConversionException("Unmapped network ID/metadata combination $networkId:$networkMeta");
	}

	/**
	 * @return int[]
	 * @phpstan-return array{int, int}
	 * @throws TypeConversionException
	 */
	public function fromNetworkIdWithWildcardHandling(int $networkId, int $networkMeta) : array
	{
		$isComplexMapping = false;
		if ($networkMeta !== 0x7fff) {
			return $this->fromNetworkId($networkId, $networkMeta);
		}
		[$id, $meta] = $this->fromNetworkId($networkId, 0, $isComplexMapping);
		return [$id, $isComplexMapping ? $meta : -1];
	}
}
