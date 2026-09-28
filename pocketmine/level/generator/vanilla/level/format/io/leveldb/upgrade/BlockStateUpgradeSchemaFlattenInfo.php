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

use pocketmine\level\generator\vanilla\nbt\tag\ByteTag;
use pocketmine\level\generator\vanilla\nbt\tag\IntTag;
use pocketmine\level\generator\vanilla\nbt\tag\StringTag;
use function ksort;
use const SORT_STRING;

final class BlockStateUpgradeSchemaFlattenInfo
{
	/**
	 * @param string[] $flattenedValueRemaps
	 * @phpstan-param array<string, string> $flattenedValueRemaps
	 * @phpstan-param ?class-string<ByteTag|IntTag|StringTag> $flattenedPropertyType
	 */
	public function __construct(
		public string $prefix,
		public string $flattenedProperty,
		public string $suffix,
		public array $flattenedValueRemaps,
		public ?string $flattenedPropertyType = null
	) {
		ksort($this->flattenedValueRemaps, SORT_STRING);
	}

	public function equals(self $that) : bool
	{
		return $this->prefix === $that->prefix &&
			$this->flattenedProperty === $that->flattenedProperty &&
			$this->suffix === $that->suffix &&
			$this->flattenedValueRemaps === $that->flattenedValueRemaps &&
			$this->flattenedPropertyType === $that->flattenedPropertyType;
	}
}
