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

namespace pocketmine\level\generator\vanilla\level\format\io\leveldb\states;

use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\nbt\tag\NamedTag;
use pocketmine\level\generator\vanilla\utils\Utils;
use pocketmine\VersionInfo;
use function array_keys;
use function count;
use function implode;

/**
 * Contains the common information found in a serialized blockstate.
 */
final class BlockStateData
{
	/**
	 * Bedrock version of the most recent backwards-incompatible change to blockstates.
	 *
	 * This is *not* the same as current game version. It should match the numbers in the
	 * newest blockstate upgrade schema used in BedrockBlockUpgradeSchema.
	 */
	public const CURRENT_VERSION =
		(1 << 24) | //major
		(21 << 16) | //minor
		(60 << 8) | //patch
		(33); //revision

	public const TAG_NAME = "name";
	public const TAG_STATES = "states";
	public const TAG_VERSION = "version";

	/**
	 * @param NamedTag[] $states
	 * @phpstan-param array<NamedTag> $states
	 */
	public function __construct(
		private string $name,
		private array $states,
		private int $version
	) {
	}

	/**
	 * @param NamedTag[] $states
	 * @phpstan-param array<NamedTag> $states
	 */
	public static function current(string $name, array $states) : self
	{
		return new self($name, $states, self::CURRENT_VERSION);
	}

	public function getName() : string
	{
		return $this->name;
	}

	/**
	 * @return NamedTag[]
	 * @phpstan-return array<NamedTag>
	 */
	public function getStates() : array
	{
		return $this->states;
	}

	public function getState(string $name) : ?NamedTag
	{
		return $this->states[$name] ?? null;
	}

	public function getVersion() : int
	{
		return $this->version;
	}

	public function getVersionAsString() : string
	{
		$major = ($this->version >> 24) & 0xff;
		$minor = ($this->version >> 16) & 0xff;
		$patch = ($this->version >> 8) & 0xff;
		$revision = $this->version & 0xff;
		return "$major.$minor.$patch.$revision";
	}

	/**
	 * @throws BlockStateDeserializeException
	 */
	public static function fromNbt(CompoundTag $nbt) : self
	{
		try {
			$name = $nbt->getString(self::TAG_NAME);
			$states = $nbt->getCompoundTag(self::TAG_STATES) ?? throw new BlockStateDeserializeException("Missing tag \"" . self::TAG_STATES . "\"");
			$version = $nbt->getInt(self::TAG_VERSION, 0);
			//TODO: read version from VersionInfo::TAG_WORLD_DATA_VERSION - we may need it to fix up old blockstates
		} catch (\Exception $e) {
			throw new BlockStateDeserializeException($e->getMessage(), 0, $e);
		}

		$allKeys = $nbt->getValue();
		unset($allKeys[self::TAG_NAME], $allKeys[self::TAG_STATES], $allKeys[self::TAG_VERSION], $allKeys[VersionInfo::TAG_WORLD_DATA_VERSION]);
		if (count($allKeys) !== 0) {
			throw new BlockStateDeserializeException("Unexpected extra keys: " . implode(", ", array_keys($allKeys)));
		}

		return new self($name, $states->getValue(), $version);
	}

	/**
	 * Encodes the blockstate as a TAG_Compound, exactly as it would be in vanilla Bedrock.
	 */
	public function toVanillaNbt() : CompoundTag
	{
		$statesTag = new CompoundTag();
		foreach (Utils::stringifyKeys($this->states) as $key => $value) {
			$value->setName($key);
			$statesTag->setTag($value);
		}

		$nbt = new CompoundTag();
		$nbt->setString(self::TAG_NAME, $this->name);
		$nbt->setInt(self::TAG_VERSION, $this->version);
		$statesTag->setName(self::TAG_STATES);
		$nbt->setTag($statesTag);

		return $nbt;
	}

	/**
	 * Encodes the blockstate as a TAG_Compound, but with extra PM-specific metadata, used for fixing bugs in old saved
	 * data. This should be used for anything saved to disk.
	 */
	public function toNbt() : CompoundTag
	{
		$vanillaNbt = $this->toVanillaNbt();
		$vanillaNbt->setLong(VersionInfo::TAG_WORLD_DATA_VERSION, VersionInfo::WORLD_DATA_VERSION);

		return $vanillaNbt;
	}

	public function equals(self $that) : bool
	{
		if ($this->name !== $that->name || count($this->states) !== count($that->states)) {
			return false;
		}
		foreach (Utils::stringifyKeys($this->states) as $k => $v) {
			if (!isset($that->states[$k]) || !$that->states[$k]->equals($v)) {
				return false;
			}
		}

		return true;
	}
}
