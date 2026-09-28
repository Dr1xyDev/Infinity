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
use pocketmine\level\generator\vanilla\utils\Utils;
use function array_diff;
use function count;

final class BlockStateUpgradeSchemaBlockRemap
{
	/**
	 * @param NamedTag[] $oldState
	 * @param NamedTag[] $newState
	 * @param string[]   $copiedState
	 *
	 * @phpstan-param array<string, NamedTag> $oldState
	 * @phpstan-param array<string, NamedTag> $newState
	 * @phpstan-param list<string>       $copiedState
	 */
	public function __construct(
		public array $oldState,
		public string|BlockStateUpgradeSchemaFlattenInfo $newName,
		public array $newState,
		public array $copiedState
	) {
	}

	public function equals(self $that) : bool
	{
		$sameName = $this->newName === $that->newName ||
			(
				$this->newName instanceof BlockStateUpgradeSchemaFlattenInfo &&
				$that->newName instanceof BlockStateUpgradeSchemaFlattenInfo &&
				$this->newName->equals($that->newName)
			);
		if (!$sameName) {
			return false;
		}

		if (
			count($this->oldState) !== count($that->oldState) ||
			count($this->newState) !== count($that->newState) ||
			count($this->copiedState) !== count($that->copiedState) ||
			count(array_diff($this->copiedState, $that->copiedState)) !== 0
		) {
			return false;
		}
		foreach (Utils::stringifyKeys($this->oldState) as $propertyName => $propertyValue) {
			if (!isset($that->oldState[$propertyName]) || !$that->oldState[$propertyName]->equals($propertyValue)) {
				//different filter value
				return false;
			}
		}
		foreach (Utils::stringifyKeys($this->newState) as $propertyName => $propertyValue) {
			if (!isset($that->newState[$propertyName]) || !$that->newState[$propertyName]->equals($propertyValue)) {
				//different replacement value
				return false;
			}
		}

		return true;
	}
}
