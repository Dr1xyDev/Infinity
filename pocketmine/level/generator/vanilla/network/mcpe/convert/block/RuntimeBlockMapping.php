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

namespace pocketmine\level\generator\vanilla\network\mcpe\convert\block;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\nbt\NetworkLittleEndianNBTStream;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\nbt\tag\ListTag;
use pocketmine\level\generator\vanilla\nbt\tag\NamedTag;
use pocketmine\level\generator\vanilla\network\mcpe\convert\ProtocolConvertor;
use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\utils\AssumptionFailedError;
use function array_key_exists;
use function array_keys;
use function count;
use function file_get_contents;
use function is_bool;
use function implode;
use function getmypid;
use function ksort;
use function mt_rand;
use function mt_srand;
use function shuffle;
use const pocketmine\BEDROCK_DATA_PATH;

/**
 * @internal
 */
final class RuntimeBlockMapping
{

	/** @var int[] */
	private array $legacyToRuntimeMap = [];
	/** @var int[] */
	private array $runtimeToLegacyMap = [];

	/** @var string[] */
	private array $runtimeToNameMap = [];
	/** @var int[] vanilla name => runtime ID of its first state */
	private array $nameToDefaultRuntimeMap = [];

	/** @var NamedTag[][] */
	private array $runtimeToStatesMap = [];

	/** @var int[] */
	private array $runtimeToVersionMap = [];

	/** @var int[] */
	private array $nbtBlockToRuntimeMap = [];

	/**
	 * Since 1.26.50, some block states contain properties which are calculated from the neighbouring blocks (stair
	 * corners, fence/pane/bars connections). These can't be represented by legacy id:meta, so the palette maps every
	 * such state onto the legacy id:meta of its default state.
	 * This maps a state key with those properties stripped to the runtime ID of the default state, which allows
	 * resolving block states both with and without these properties (e.g. from worlds saved by older versions).
	 *
	 * @var int[]
	 * @phpstan-var array<string, int>
	 */
	private array $strippedNbtBlockToRuntimeMap = [];

	/**
	 * Runtime IDs of the neighbour-dependent variants of a state, keyed by the runtime ID of its default state and
	 * then by dynamicStateKey()
	 *
	 * @var int[][]
	 * @phpstan-var array<int, array<string, int>>
	 */
	private array $dynamicStateVariants = [];

	/** Neighbour-dependent properties added in 1.26.50, and the values they have in the default state */
	public const DYNAMIC_STATE_PROPERTIES = [
		"minecraft:corner" => "none",
		"minecraft:connection_east" => 0,
		"minecraft:connection_north" => 0,
		"minecraft:connection_south" => 0,
		"minecraft:connection_west" => 0,
	];

	/** @var CompoundTag[]|array[]|null */
	private ?array $bedrockKnownStates = null;
	private ?string $bedrockEncodeBedrockKnownStates = null;

	/** @var self[] */
	private static array $instance = [];

	public static function getInstance(int $protocolVersion) : self
	{
		$protocolVersion = ProtocolConvertor::getInstance()->getBlockPaletteProtocol($protocolVersion);
		if (!isset(self::$instance[$protocolVersion])) {
			self::$instance[$protocolVersion] = new self($protocolVersion);
		}
		return self::$instance[$protocolVersion];
	}

	/**
	 * Randomizes the order of the runtimeID table to prevent plugins relying on them.
	 * Plugins shouldn't use this stuff anyway, but plugin devs have an irritating habit of ignoring what they
	 * aren't supposed to do, so we have to deliberately break it to make them stop.
	 */
	private static function randomizeTable(array $table) : array
	{
		$postSeed = mt_rand(); //save a seed to set afterwards, to avoid poor quality randoms
		mt_srand(getmypid()); //Use a seed which is the same on all threads. This isn't a secure seed, but we don't care.
		shuffle($table);
		mt_srand($postSeed); //restore a good quality seed that isn't dependent on PID
		return $table;
	}

	private function __construct(
		private readonly int $protocolVersion
	){
		$list = $this->loadRequiredStates();

		if ($protocolVersion >= ProtocolInfo::PROTOCOL_419) {
			//the full list is only sent to clients older than 1.16.100, don't keep ~40 MB of tags around for nothing
			//(getBedrockKnownStates() loads it again if something asks for it)
			foreach ($list as $state) {
				self::registerMapping(
					$state->getInt("runtime_id"),
					$state->getString("name"),
					$state->getCompoundTag("states"),
					$state->getInt("version"),
					$state->getInt("legacy_id"),
					$state->getShort("data"),
					$state->getByte("variant", 0) !== 0
				);
			}
		} else {
			$list = self::randomizeTable($list);
			$this->bedrockKnownStates = $list;
			foreach ($list as $runtimeId => $tag) {
				/** @var CompoundTag $tag */
				$block = $tag->getCompoundTag("block");
				$name = $block->getString("name");
				$states = $block->getCompoundTag("states");
				$version = $block->getInt("version");
				$legacyStates = $tag->getListTag("LegacyStates")->getValue();
				foreach ($legacyStates as $legacyState) {
					/** @var CompoundTag $legacyState */
					$id = $legacyState->getInt("id");
					$meta = $legacyState->getShort("val");

					self::registerMapping($runtimeId, $name, $states, $version, $id, $meta);
				}
			}
		}

		if (count($this->strippedNbtBlockToRuntimeMap) > 0) {
			foreach ($this->runtimeToStatesMap as $runtimeId => $states) {
				$key = self::dynamicStateKey($states);
				if ($key === "") {
					continue;
				}
				$defaultRuntimeId = $this->strippedNbtBlockToRuntimeMap[self::strippedStateKey($this->runtimeToNameMap[$runtimeId], $states)] ?? null;
				if ($defaultRuntimeId !== null) {
					$this->dynamicStateVariants[$defaultRuntimeId][$key] = $runtimeId;
				}
			}
		}
	}

	/**
	 * @param mixed[] $states state name => NamedTag or scalar value
	 */
	private static function dynamicStateKey(array $states) : string
	{
		$parts = [];
		foreach (self::DYNAMIC_STATE_PROPERTIES as $stateName => $_) {
			if (isset($states[$stateName])) {
				$value = $states[$stateName];
				$parts[] = $stateName . "=" . ($value instanceof NamedTag ? $value->getValue() : $value);
			}
		}
		return implode(",", $parts);
	}

	/**
	 * Returns the runtime ID of the given state with its neighbour-dependent properties (1.26.50+) set, or null if
	 * there's no such variant (e.g. older palettes).
	 *
	 * @param mixed[] $dynamicStates property name => value, e.g. ["minecraft:corner" => "outer_left"]
	 */
	public function getDynamicStateVariant(int $defaultRuntimeId, array $dynamicStates) : ?int
	{
		if (!isset($this->dynamicStateVariants[$defaultRuntimeId])) {
			return null;
		}
		return $this->dynamicStateVariants[$defaultRuntimeId][self::dynamicStateKey($dynamicStates)] ?? null;
	}

	/**
	 * @return CompoundTag[]
	 */
	private function loadRequiredStates() : array
	{
		$requiredBlockListFile = \pocketmine\level\generator\vanilla\utils\Filesystem::resourceGetContents(BEDROCK_DATA_PATH . "block/" . $this->protocolVersion . "/required_block_states.nbt");
		$stream = new NetworkBinaryStream($requiredBlockListFile);
		$list = [];
		while (!$stream->feof()) {
			$list[] = $stream->getNbtCompoundRoot();
		}
		return $list;
	}

	private function loadEncodeBedrockKnownStates() : string
	{
		return (new NetworkLittleEndianNBTStream())->write(new ListTag("", $this->getBedrockKnownStates()));
	}

	/**
	 * Compact lookup key of a state (the stringified NBT used before took several times more memory)
	 *
	 * @param NamedTag[] $sortedStates
	 */
	private static function stateKey(string $name, array $sortedStates) : string
	{
		$parts = [];
		foreach ($sortedStates as $stateName => $tag) {
			$parts[] = $stateName . "=" . $tag->getType() . ":" . $tag->getValue();
		}
		return $name . "[" . implode(",", $parts) . "]";
	}

	public function toRuntimeId(int $internalStateId) : int
	{
		return
			$this->legacyToRuntimeMap[$internalStateId] ??
			$this->legacyToRuntimeMap[BlockIds::INFO_UPDATE << Block::INTERNAL_METADATA_BITS] ??
			0;
	}

	public function fromRuntimeId(int $runtimeId) : int{
		return $this->runtimeToLegacyMap[$runtimeId] ?? 0;
	}

	public function toName(int $runtimeId) : string {
		return $this->runtimeToNameMap[$runtimeId] ?? "minecraft:unknown";
	}

	public function toStates(int $internalStateId) : array {
		return $this->runtimeToStatesMap[$internalStateId] ?? [];
	}

	public function toVersion(int $internalStateId) : int {
		return $this->runtimeToVersionMap[$internalStateId] ?? 0;
	}

	public function toNbtBlock(int $internalStateId, bool $useVersion = false) : CompoundTag {
		$nbt = new CompoundTag();
		$nbt->setString("name", $this->toName($internalStateId));
		if ($useVersion) {
			$nbt->setInt("version", $this->toVersion($internalStateId));
		}

		$nbt->setTag(new CompoundTag("states", $this->toStates($internalStateId)));
		return $nbt;
	}

	/**
	 * @param string[] $notDeleteTags
	 */
	public function fromNbtBlock(CompoundTag $nbt, array $notDeleteTags = []) : int {
		foreach ($nbt->getValue() as $tag) {
			$name = $tag->getName();
			if ($name !== "name" && $name !== "states") {
				foreach ($notDeleteTags as $notDeleteTag) {
					if ($name === $notDeleteTag) {
						continue 2;
					}
				}

				$nbt->removeTag($name);
			}
		}

		$states = $nbt->getCompoundTag("states")->getValue();
		ksort($states);
		$nbt->setTag(new CompoundTag("states", $states));

		$runtimeId = $this->nbtBlockToRuntimeMap[self::stateKey($nbt->getString("name"), $states)] ?? null;
		if ($runtimeId === null && count($this->strippedNbtBlockToRuntimeMap) > 0) {
			$runtimeId = $this->strippedNbtBlockToRuntimeMap[self::strippedStateKey($nbt->getString("name"), $states)] ?? null;
		}

		return $runtimeId ?? 0;
	}

	/**
	 * @param NamedTag[] $sortedStates
	 */
	private static function strippedStateKey(string $name, array $sortedStates) : string
	{
		$parts = [];
		foreach ($sortedStates as $stateName => $tag) {
			if (!isset(self::DYNAMIC_STATE_PROPERTIES[$stateName])) {
				$parts[] = $stateName . "=" . $tag->getType() . ":" . $tag->getValue();
			}
		}
		return $name . "[" . implode(",", $parts) . "]";
	}

	/**
	 * @param NamedTag[] $states
	 */
	private static function hasNonDefaultDynamicState(array $states) : bool
	{
		foreach (self::DYNAMIC_STATE_PROPERTIES as $stateName => $defaultValue) {
			if (isset($states[$stateName]) && $states[$stateName]->getValue() !== $defaultValue) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @param bool $variant whether this is a neighbour-dependent variant of a state, which the server never sends by
	 *                      itself; such states are only mapped from network to legacy, not the other way around
	 */
	private function registerMapping(int $staticRuntimeId, string $name, CompoundTag $states, int $version, int $legacyId, int $legacyMeta, bool $variant = false) : void
	{
		if ($legacyMeta >= (1 << Block::INTERNAL_METADATA_BITS)) {
			return;
		}

		if (!$variant) {
			$this->legacyToRuntimeMap[($legacyId << Block::INTERNAL_METADATA_BITS) | $legacyMeta] = $staticRuntimeId;
		}
		$this->runtimeToLegacyMap[$staticRuntimeId] = ($legacyId << Block::INTERNAL_METADATA_BITS) | $legacyMeta;

		$this->runtimeToNameMap[$staticRuntimeId] = $name;
		if (!$variant) {
			$this->nameToDefaultRuntimeMap[$name] ??= $staticRuntimeId;
		}

		$sortStates = $states->getValue();
		ksort($sortStates);
		$this->runtimeToStatesMap[$staticRuntimeId] = $sortStates;

		$this->runtimeToVersionMap[$staticRuntimeId] = $version;

		$this->nbtBlockToRuntimeMap[self::stateKey($name, $sortStates)] = $staticRuntimeId;

		if (!$variant && !self::hasNonDefaultDynamicState($sortStates)) {
			foreach (self::DYNAMIC_STATE_PROPERTIES as $stateName => $_) {
				if (isset($sortStates[$stateName])) {
					$this->strippedNbtBlockToRuntimeMap[self::strippedStateKey($name, $sortStates)] = $staticRuntimeId;
					break;
				}
			}
		}
	}

	/**
	 * WARNING: This method may load the palette from disk, which is a slow operation.
	 * Afterwards, it will cache the palette in memory, which requires (in some cases) tens of MB of memory.
	 * Avoid using this where possible.
	 *
	 * @return CompoundTag[]|array[]
	 */
	/**
	 * Finds a state by the vanilla block name. States which are not given keep the values of the first state of the
	 * block in the palette.
	 *
	 * @param mixed[] $states state name => value (bool, int or string)
	 */
	public function lookupState(string $name, array $states = []) : ?int
	{
		$defaultRuntimeId = $this->nameToDefaultRuntimeMap[$name] ?? null;
		if ($defaultRuntimeId === null) {
			return null;
		}
		if (count($states) === 0) {
			return $defaultRuntimeId;
		}
		$parts = [];
		foreach ($this->runtimeToStatesMap[$defaultRuntimeId] as $stateName => $tag) {
			$value = $tag->getValue();
			if (array_key_exists($stateName, $states)) {
				$value = is_bool($states[$stateName]) ? (int) $states[$stateName] : $states[$stateName];
				unset($states[$stateName]);
			}
			$parts[] = $stateName . "=" . $tag->getType() . ":" . $value;
		}
		if (count($states) > 0) {
			return null; //unknown state names
		}
		return $this->nbtBlockToRuntimeMap[$name . "[" . implode(",", $parts) . "]"] ?? null;
	}

	/**
	 * @return string[] vanilla names of all blocks of this palette
	 */
	public function getBlockNames() : array
	{
		return array_keys($this->nameToDefaultRuntimeMap);
	}

	public function getBedrockKnownStates() : array
	{
		return $this->bedrockKnownStates ??= $this->loadRequiredStates();
	}

	public function getEncodeBedrockKnownStates() : string
	{
		return $this->bedrockEncodeBedrockKnownStates ??= $this->loadEncodeBedrockKnownStates();
	}

	public function getProtocolVersion() : int
	{
		return $this->protocolVersion;
	}
}
