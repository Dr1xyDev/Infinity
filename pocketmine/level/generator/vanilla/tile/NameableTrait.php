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

namespace pocketmine\level\generator\vanilla\tile;

use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\nbt\tag\StringTag;
use pocketmine\Player;

/**
 * This trait implements most methods in the {@link Nameable} interface. It should only be used by Tiles.
 */
trait NameableTrait
{
	/** @var string|null */
	private $customName;

	abstract public function getDefaultName() : string;

	public function getName() : string
	{
		return $this->customName ?? $this->getDefaultName();
	}

	public function setName(string $name) : void
	{
		if ($name === "") {
			$this->customName = null;
		} else {
			$this->customName = $name;
		}
	}

	public function hasName() : bool
	{
		return $this->customName !== null;
	}

	protected static function createAdditionalNBT(CompoundTag $nbt, Vector3 $pos, ?int $face = null, ?Item $item = null, ?Player $player = null) : void
	{
		if ($item !== null && $item->hasCustomName()) {
			$nbt->setString(Nameable::TAG_CUSTOM_NAME, $item->getCustomName());
		}
	}

	public function addAdditionalSpawnData(CompoundTag $nbt, int $protocolVersion) : void
	{
		if ($this->customName !== null) {
			$nbt->setString(Nameable::TAG_CUSTOM_NAME, $this->customName);
		}
	}

	protected function loadName(CompoundTag $tag) : void
	{
		if ($tag->hasTag(Nameable::TAG_CUSTOM_NAME, StringTag::class)) {
			$this->customName = $tag->getString(Nameable::TAG_CUSTOM_NAME);
		}
	}

	protected function saveName(CompoundTag $tag) : void
	{
		if ($this->customName !== null) {
			$tag->setString(Nameable::TAG_CUSTOM_NAME, $this->customName);
		}
	}
}
