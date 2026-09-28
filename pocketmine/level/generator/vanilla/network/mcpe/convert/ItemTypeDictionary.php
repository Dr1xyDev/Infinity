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

use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\ItemTypeEntry;
use function array_key_exists;

final class ItemTypeDictionary
{
	/**
	 * @var string[]
	 * @phpstan-var array<int, string>
	 */
	private array $intToStringIdMap = [];
	/**
	 * @var int[]
	 * @phpstan-var array<string, int>
	 */
	private array $stringToIntMap = [];

	/**
	 * @param ItemTypeEntry[] $itemTypes
	 */
	public function __construct(private array $itemTypes)
	{
		foreach ($this->itemTypes as $type) {
			$this->stringToIntMap[$type->getStringId()] = $type->getNumericId();
			$this->intToStringIdMap[$type->getNumericId()] = $type->getStringId();
		}
	}

	/**
	 * @return ItemTypeEntry[]
	 */
	public function getEntries() : array
	{
		return $this->itemTypes;
	}

	public function fromStringId(string $stringId) : int
	{
		if (!array_key_exists($stringId, $this->stringToIntMap)) {
			throw new \InvalidArgumentException("Unmapped string ID \"$stringId\"");
		}
		return $this->stringToIntMap[$stringId];
	}

	public function fromIntId(int $intId) : string
	{
		if (!array_key_exists($intId, $this->intToStringIdMap)) {
			throw new \InvalidArgumentException("Unmapped int ID $intId");
		}
		return $this->intToStringIdMap[$intId];
	}
}
