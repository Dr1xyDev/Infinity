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

use pocketmine\level\generator\vanilla\nbt\tag\NamedTag;
use function count;

class BlockStateUpgradeSchema
{
	/**
	 * @var string[]
	 * @phpstan-var array<string, string>
	 */
	public array $renamedIds = [];

	/**
	 * @var NamedTag[][]
	 * @phpstan-var array<string, array<NamedTag>>
	 */
	public array $addedProperties = [];

	/**
	 * @var string[][]
	 * @phpstan-var array<string, list<string>>
	 */
	public array $removedProperties = [];

	/**
	 * @var string[][]
	 * @phpstan-var array<string, array<string, string>>
	 */
	public array $renamedProperties = [];

	/**
	 * @var BlockStateUpgradeSchemaValueRemap[][][]
	 * @phpstan-var array<string, array<string, list<BlockStateUpgradeSchemaValueRemap>>>
	 */
	public array $remappedPropertyValues = [];

	/**
	 * @var BlockStateUpgradeSchemaFlattenInfo[]
	 * @phpstan-var array<string, BlockStateUpgradeSchemaFlattenInfo>
	 */
	public array $flattenedProperties = [];

	/**
	 * @var BlockStateUpgradeSchemaBlockRemap[][]
	 * @phpstan-var array<string, list<BlockStateUpgradeSchemaBlockRemap>>
	 */
	public array $remappedStates = [];

	public readonly int $versionId;

	public function __construct(
		public readonly int $maxVersionMajor,
		public readonly int $maxVersionMinor,
		public readonly int $maxVersionPatch,
		public readonly int $maxVersionRevision,
		private int $schemaId
	) {
		$this->versionId = ($this->maxVersionMajor << 24) | ($this->maxVersionMinor << 16) | ($this->maxVersionPatch << 8) | $this->maxVersionRevision;
	}

	/**
	 * @deprecated This is defined by Mojang, and therefore cannot be relied on. Use getSchemaId() instead for
	 * internal version management.
	 */
	public function getVersionId() : int
	{
		return $this->versionId;
	}

	public function getSchemaId() : int
	{
		return $this->schemaId;
	}

	public function isEmpty() : bool
	{
		foreach ([
			$this->renamedIds,
			$this->addedProperties,
			$this->removedProperties,
			$this->renamedProperties,
			$this->remappedPropertyValues,
			$this->flattenedProperties,
			$this->remappedStates,
		] as $list) {
			if (count($list) !== 0) {
				return false;
			}
		}

		return true;
	}
}
