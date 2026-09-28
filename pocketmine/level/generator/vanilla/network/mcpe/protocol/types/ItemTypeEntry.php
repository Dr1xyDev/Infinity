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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types;

use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;

final class ItemTypeEntry
{
	public function __construct(
		private string $stringId,
		private int $numericId,
		private bool $componentBased,
		private int $version,
		private CompoundTag $componentNbt
	) {
	}

	public function getStringId() : string
	{
		return $this->stringId;
	}

	public function getNumericId() : int
	{
		return $this->numericId;
	}

	public function isComponentBased() : bool
	{
		return $this->componentBased;
	}

	public function getVersion() : int
	{
		return $this->version;
	}

	public function getComponentNbt() : CompoundTag
	{
		return $this->componentNbt;
	}
}
