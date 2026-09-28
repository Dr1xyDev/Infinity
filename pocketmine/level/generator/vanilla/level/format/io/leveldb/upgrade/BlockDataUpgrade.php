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

namespace pocketmine\level\generator\vanilla\level\format\io\leveldb\upgrade;

use pocketmine\level\generator\vanilla\level\format\io\leveldb\states\BlockStateData;
use pocketmine\level\generator\vanilla\nbt\tag\ByteTag;
use pocketmine\level\generator\vanilla\nbt\tag\IntTag;
use pocketmine\level\generator\vanilla\nbt\tag\NamedTag;
use pocketmine\level\generator\vanilla\nbt\tag\StringTag;
use pocketmine\level\generator\vanilla\utils\AssumptionFailedError;
use pocketmine\level\generator\vanilla\utils\Filesystem;
use pocketmine\level\generator\vanilla\utils\SingletonTrait;
use pocketmine\level\generator\vanilla\utils\Utils;
use function array_diff;
use function count;
use function get_class;
use function is_string;
use function json_decode;
use function ksort;
use function max;
use function scandir;
use function sprintf;
use const SORT_NUMERIC;

class BlockDataUpgrade
{
	use SingletonTrait;

	public static int $schemaId = 0;

	/**
	 * @var BlockStateUpgradeSchema[][] versionId => [schemaId => schema]
	 * @phpstan-var array<int, array<int, BlockStateUpgradeSchema>>
	 */
	private array $upgradeSchemas = [];

	private int $outputVersion = 0;

	public function __construct()
	{
		$results = [];
		foreach (array_diff(scandir(\pocketmine\BEDROCK_BLOCK_UPGRADE_SCHEMA_PATH . "nbt_upgrade_schema/"), ["..", "."]) as $jsonVersion) {
			$model = json_decode(Filesystem::fileGetContents(\pocketmine\BEDROCK_BLOCK_UPGRADE_SCHEMA_PATH . "nbt_upgrade_schema/" . $jsonVersion), true);
			$result = new BlockStateUpgradeSchema(
				$model["maxVersionMajor"],
				$model["maxVersionMinor"],
				$model["maxVersionPatch"],
				$model["maxVersionRevision"],
				$schemaId = self::$schemaId++,
			);

			$result->renamedIds = $model["renamedIds"] ?? [];
			$result->renamedProperties = $model["renamedProperties"] ?? [];
			$result->removedProperties = $model["removedProperties"] ?? [];

			foreach (Utils::stringifyKeys($model["addedProperties"] ?? []) as $blockName => $properties) {
				foreach (Utils::stringifyKeys($properties) as $propertyName => $propertyValue) {
					$result->addedProperties[$blockName][$propertyName] = $this->jsonModelToTag($propertyName, $propertyValue);
				}
			}

			$convertedRemappedValuesIndex = [];
			foreach (Utils::stringifyKeys($model["remappedPropertyValuesIndex"] ?? []) as $mappingKey => $mappingValues) {
				foreach ($mappingValues as $k => $oldNew) {
					$convertedRemappedValuesIndex[$mappingKey][$k] = new BlockStateUpgradeSchemaValueRemap(
						$this->jsonModelToTag("", $oldNew["old"]),
						$this->jsonModelToTag("", $oldNew["new"])
					);
				}
			}

			foreach (Utils::stringifyKeys($model["remappedPropertyValues"] ?? []) as $blockName => $properties) {
				foreach (Utils::stringifyKeys($properties) as $property => $mappedValuesKey) {
					if (!isset($convertedRemappedValuesIndex[$mappedValuesKey])) {
						throw new \UnexpectedValueException("Missing key from schema values index $mappedValuesKey");
					}

					foreach ($convertedRemappedValuesIndex[$mappedValuesKey] as $k => $oldNew) {
						$oldNew->old->setName($property);
						$oldNew->new->setName($property);
					}

					$result->remappedPropertyValues[$blockName][$property] = $convertedRemappedValuesIndex[$mappedValuesKey];
				}
			}

			foreach (Utils::stringifyKeys($model["flattenedProperties"] ?? []) as $blockName => $flattenRule) {
				$result->flattenedProperties[$blockName] = self::jsonModelToFlattenRule($flattenRule);
			}

			foreach (Utils::stringifyKeys($model["remappedStates"] ?? []) as $oldBlockName => $remaps) {
				foreach ($remaps as $remap) {
					if (isset($remap["newName"])) {
						$remapName = $remap["newName"];
					} elseif (isset($remap["newFlattenedName"])) {
						$flattenRule = $remap["newFlattenedName"];
						$remapName = self::jsonModelToFlattenRule($flattenRule);
					} else {
						throw new \UnexpectedValueException("Expected exactly one of 'newName' or 'newFlattenedName' properties to be set");
					}

					$oldState = [];
					foreach (($remap["oldState"] ?? []) as $property => $tags) {
						$oldState[$property] = $this->jsonModelToTag($property, $tags);
					}

					$newState = [];
					foreach (($remap["newState"] ?? []) as $property => $tags) {
						$newState[$property] = $this->jsonModelToTag($property, $tags);
					}

					$result->remappedStates[$oldBlockName][] = new BlockStateUpgradeSchemaBlockRemap(
						$oldState,
						$remapName,
						$newState,
						$remap["copiedState"] ?? []
					);
				}
			}

			$results[$schemaId] = $result;
		}

		ksort($results, SORT_NUMERIC);

		foreach ($results as $schema) {
			$this->addSchema($schema);
		}
	}

	private function jsonModelToFlattenRule(array $flattenRule) : BlockStateUpgradeSchemaFlattenInfo
	{
		return new BlockStateUpgradeSchemaFlattenInfo(
			$flattenRule["prefix"],
			$flattenRule["flattenedProperty"],
			$flattenRule["suffix"],
			$flattenRule["flattenedValueRemaps"] ?? [],
			match ($flattenRule["flattenedPropertyType"] ?? null) {
				"string", null => StringTag::class,
				"int" => IntTag::class,
				"byte" => ByteTag::class,
				default => throw new \UnexpectedValueException("Unexpected flattened property type " . $flattenRule['flattenedPropertyType'] . ", expected 'string', 'int' or 'byte'")
			}
		);
	}

	private function jsonModelToTag(string $name, array $model) : NamedTag
	{
		return match(true) {
			isset($model["byte"]) && !isset($model["int"]) && !isset($model["string"]) => new ByteTag($name, $model["byte"]),
			!isset($model["byte"]) && isset($model["int"]) && !isset($model["string"]) => new IntTag($name, $model["int"]),
			!isset($model["byte"]) && !isset($model["int"]) && isset($model["string"]) => new StringTag($name, $model["string"]),
			default => throw new \UnexpectedValueException("Malformed JSON model tag, expected exactly one of 'byte', 'int' or 'string' properties")
		};
	}

	public function addSchema(BlockStateUpgradeSchema $schema) : void
	{
		$schemaId = $schema->getSchemaId();
		$versionId = $schema->getVersionId();
		if (isset($this->upgradeSchemas[$versionId][$schemaId])) {
			throw new \InvalidArgumentException("Cannot add two schemas with the same schema ID and version ID");
		}

		//schema ID tells us the order when multiple schemas use the same version ID
		$this->upgradeSchemas[$versionId][$schemaId] = $schema;

		ksort($this->upgradeSchemas, SORT_NUMERIC);
		ksort($this->upgradeSchemas[$versionId], SORT_NUMERIC);

		$this->outputVersion = max($this->outputVersion, $schema->getVersionId());
	}

	public function upgrade(BlockStateData $blockStateData) : BlockStateData
	{
		$version = $blockStateData->getVersion();
		foreach ($this->upgradeSchemas as $resultVersion => $schemaList) {
			/*
			 * Sometimes Mojang made changes without bumping the version ID.
			 * A notable example is 0131_1.18.20.27_beta_to_1.18.30.json, which renamed a bunch of blockIDs.
			 * When this happens, all the schemas must be applied even if the version is the same, because the input
			 * version doesn't tell us which of the schemas have already been applied.
			 * If there's only one schema for a version (the norm), we can safely assume it's already been applied if
			 * the version is the same, and skip over it.
			 * TODO: this causes issues when testing isolated schemas since there will only be one schema for a version.
			 * The second check should be disabled for that case.
			 */
			if ($version > $resultVersion || (count($schemaList) === 1 && $version === $resultVersion)) {
				continue;
			}

			foreach ($schemaList as $schema) {
				$blockStateData = $this->applySchema($schema, $blockStateData);
			}
		}

		if ($this->outputVersion > $version) {
			//always update the version number of the blockstate, even if it didn't change - this is needed for
			//external tools
			$blockStateData = new BlockStateData($blockStateData->getName(), $blockStateData->getStates(), $this->outputVersion);
		}

		return $blockStateData;
	}

	public function applySchema(BlockStateUpgradeSchema $schema, BlockStateData $blockStateData) : BlockStateData
	{
		$newStateData = $this->applyStateRemapped($schema, $blockStateData);
		if ($newStateData !== null) {
			return $newStateData;
		}

		$oldName = $blockStateData->getName();
		$states = $blockStateData->getStates();
		if (isset($schema->renamedIds[$oldName]) && isset($schema->flattenedProperties[$oldName])) {
			//TODO: this probably ought to be validated when the schema is constructed
			throw new AssumptionFailedError("Both renamedIds and flattenedProperties are set for the same block ID \"$oldName\" - don't know what to do");
		}
		if (isset($schema->renamedIds[$oldName])) {
			$newName = $schema->renamedIds[$oldName] ?? null;
		} elseif (isset($schema->flattenedProperties[$oldName])) {
			[$newName, $states] = $this->applyPropertyFlattened($schema->flattenedProperties[$oldName], $oldName, $states);
		} else {
			$newName = null;
		}

		$stateChanges = 0;

		$states = $this->applyPropertyAdded($schema, $oldName, $states, $stateChanges);
		$states = $this->applyPropertyRemoved($schema, $oldName, $states, $stateChanges);
		$states = $this->applyPropertyRenamedOrValueChanged($schema, $oldName, $states, $stateChanges);
		$states = $this->applyPropertyValueChanged($schema, $oldName, $states, $stateChanges);

		if ($newName !== null || $stateChanges > 0) {
			return new BlockStateData($newName ?? $oldName, $states, $schema->getVersionId());
		}

		return $blockStateData;
	}

	private function applyStateRemapped(BlockStateUpgradeSchema $schema, BlockStateData $blockStateData) : ?BlockStateData
	{
		$oldName = $blockStateData->getName();
		$oldState = $blockStateData->getStates();

		if (isset($schema->remappedStates[$oldName])) {
			foreach ($schema->remappedStates[$oldName] as $remap) {
				if (count($remap->oldState) > count($oldState)) {
					//match criteria has more requirements than we have state properties
					continue; //try next state
				}
				foreach (Utils::stringifyKeys($remap->oldState) as $k => $v) {
					if (!isset($oldState[$k]) || !$oldState[$k]->equals($v)) {
						continue 2; //try next state
					}
				}

				if (is_string($remap->newName)) {
					$newName = $remap->newName;
				} else {
					//discard flatten modifications to state - the remap newState and copiedState will take care of it
					[$newName, ] = $this->applyPropertyFlattened($remap->newName, $oldName, $oldState);
				}

				$newState = $remap->newState;
				foreach ($remap->copiedState as $stateName) {
					if (isset($oldState[$stateName])) {
						$newState[$stateName] = $oldState[$stateName];
						$newState[$stateName]->setName($stateName);
					}
				}

				return new BlockStateData($newName, $newState, $schema->getVersionId());
			}
		}

		return null;
	}

	/**
	 * @param NamedTag[] $states
	 * @phpstan-param array<string, NamedTag> $states
	 *
	 * @return NamedTag[]
	 * @phpstan-return array<string, NamedTag>
	 */
	private function applyPropertyAdded(BlockStateUpgradeSchema $schema, string $oldName, array $states, int &$stateChanges) : array
	{
		if (isset($schema->addedProperties[$oldName])) {
			foreach (Utils::stringifyKeys($schema->addedProperties[$oldName]) as $propertyName => $value) {
				if (!isset($states[$propertyName])) {
					$stateChanges++;
					$states[$propertyName] = $value;
					$states[$propertyName]->setName($propertyName);
				}
			}
		}

		return $states;
	}

	/**
	 * @param NamedTag[] $states
	 * @phpstan-param array<string, NamedTag> $states
	 *
	 * @return NamedTag[]
	 * @phpstan-return array<string, NamedTag>
	 */
	private function applyPropertyRemoved(BlockStateUpgradeSchema $schema, string $oldName, array $states, int &$stateChanges) : array
	{
		if (isset($schema->removedProperties[$oldName])) {
			foreach ($schema->removedProperties[$oldName] as $propertyName) {
				if (isset($states[$propertyName])) {
					$stateChanges++;
					unset($states[$propertyName]);
				}
			}
		}

		return $states;
	}

	private function locateNewPropertyValue(BlockStateUpgradeSchema $schema, string $oldName, string $newPropertyName, string $oldPropertyName, NamedTag $oldValue) : NamedTag
	{
		if (isset($schema->remappedPropertyValues[$oldName][$oldPropertyName])) {
			foreach ($schema->remappedPropertyValues[$oldName][$oldPropertyName] as $mappedPair) {
				if ($mappedPair->old->equals($oldValue)) {
					$mappedPair->new->setName($newPropertyName);
					return $mappedPair->new;
				}
			}
		}

		return $oldValue;
	}

	/**
	 * @param NamedTag[] $states
	 * @phpstan-param array<string, NamedTag> $states
	 *
	 * @return NamedTag[]
	 * @phpstan-return array<string, NamedTag>
	 */
	private function applyPropertyRenamedOrValueChanged(BlockStateUpgradeSchema $schema, string $oldName, array $states, int &$stateChanges) : array
	{
		if (isset($schema->renamedProperties[$oldName])) {
			foreach (Utils::stringifyKeys($schema->renamedProperties[$oldName]) as $oldPropertyName => $newPropertyName) {
				$oldValue = $states[$oldPropertyName] ?? null;
				if ($oldValue !== null) {
					$stateChanges++;
					unset($states[$oldPropertyName]);

					//If a value remap is needed, we need to do it here, since we won't be able to locate the property
					//after it's been renamed - value remaps are always indexed by old property name for the sake of
					//being able to do changes in any order.
					$states[$newPropertyName] = $this->locateNewPropertyValue($schema, $oldName, $newPropertyName, $oldPropertyName, $oldValue);
					$states[$newPropertyName]->setName($newPropertyName);
				}
			}
		}

		return $states;
	}

	/**
	 * @param NamedTag[] $states
	 * @phpstan-param array<string, NamedTag> $states
	 *
	 * @return NamedTag[]
	 * @phpstan-return array<string, NamedTag>
	 */
	private function applyPropertyValueChanged(BlockStateUpgradeSchema $schema, string $oldName, array $states, int &$stateChanges) : array
	{
		if (isset($schema->remappedPropertyValues[$oldName])) {
			foreach (Utils::stringifyKeys($schema->remappedPropertyValues[$oldName]) as $oldPropertyName => $remappedValues) {
				$oldValue = $states[$oldPropertyName] ?? null;
				if ($oldValue !== null) {
					$newValue = $this->locateNewPropertyValue($schema, $oldName, $oldPropertyName, $oldPropertyName, $oldValue);
					if ($newValue !== $oldValue) {
						$stateChanges++;
						$states[$oldPropertyName] = $newValue;
						$states[$oldPropertyName]->setName($oldPropertyName);
					}
				}
			}
		}

		return $states;
	}

	/**
	 * @param NamedTag[] $states
	 * @phpstan-param array<string, NamedTag> $states
	 *
	 * @return (string|NamedTag[])[]
	 * @phpstan-return array{0: string, 1: array<string, NamedTag>}
	 */
	private function applyPropertyFlattened(BlockStateUpgradeSchemaFlattenInfo $flattenInfo, string $oldName, array $states) : array
	{
		$flattenedValue = $states[$flattenInfo->flattenedProperty] ?? null;
		$expectedType = $flattenInfo->flattenedPropertyType;
		if (!$flattenedValue instanceof $expectedType) {
			//flattened property is not of the expected type, so this transformation is not applicable
			return [$oldName, $states];
		}
		$embedKey = match(get_class($flattenedValue)) {
			StringTag::class => $flattenedValue->getValue(),
			ByteTag::class => (string) $flattenedValue->getValue(),
			IntTag::class => (string) $flattenedValue->getValue(),
			//flattenedPropertyType is always one of these three types, but PHPStan doesn't know that
			default => throw new AssumptionFailedError("flattenedPropertyType should be one of these three types, but have " . get_class($flattenedValue)),
		};
		$embedValue = $flattenInfo->flattenedValueRemaps[$embedKey] ?? $embedKey;
		$newName = sprintf("%s%s%s", $flattenInfo->prefix, $embedValue, $flattenInfo->suffix);
		unset($states[$flattenInfo->flattenedProperty]);

		return [$newName, $states];
	}
}
