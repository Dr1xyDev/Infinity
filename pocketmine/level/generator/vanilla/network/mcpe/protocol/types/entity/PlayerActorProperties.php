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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\entity;

use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\nbt\tag\FloatTag;
use pocketmine\level\generator\vanilla\nbt\tag\IntTag;
use pocketmine\level\generator\vanilla\nbt\tag\ListTag;
use pocketmine\level\generator\vanilla\nbt\tag\StringTag;

class PlayerActorProperties
{
	/** @var array<string, int> */
	private array $ints = [];

	/** @var array<string, float> */
	private array $floats = [];

	public function setInt(string $name, int $value) : void
	{
		$this->ints[$name] = $value;
	}

	public function getInt(string $name, int $default = 0) : int
	{
		return $this->ints[$name] ?? $default;
	}

	public function setFloat(string $name, float $value) : void
	{
		$this->floats[$name] = $value;
	}

	public function getFloat(string $name, float $default = 0.0) : float
	{
		return $this->floats[$name] ?? $default;
	}

	public function setBool(string $name, bool $value) : void
	{
		$this->setInt($name, $value ? 1 : 0);
	}

	public function getBool(string $name, bool $default = false) : bool
	{
		return $this->getInt($name, $default ? 1 : 0) !== 0;
	}

	/**
	 * @return array<string, int>
	 */
	public function getAllInts() : array
	{
		return $this->ints;
	}

	/**
	 * @return array<string, float>
	 */
	public function getAllFloats() : array
	{
		return $this->floats;
	}

	public function toNbt() : CompoundTag
	{
		$intsList = [];
		foreach ($this->ints as $name => $value) {
			$intsList[] = new CompoundTag("", [
				new StringTag("name", $name),
				new IntTag("value", $value)
			]);
		}

		$floatsList = [];
		foreach ($this->floats as $name => $value) {
			$floatsList[] = new CompoundTag("", [
				new StringTag("name", $name),
				new FloatTag("value", $value)
			]);
		}

		return new CompoundTag("", [
			new ListTag("ints", $intsList),
			new ListTag("floats", $floatsList)
		]);
	}

	public static function fromNbt(CompoundTag $tag) : self
	{
		$properties = new self();

		$intsList = $tag->getListTag("ints");
		if ($intsList !== null) {
			foreach ($intsList as $intTag) {
				if ($intTag instanceof CompoundTag) {
					$name = $intTag->getString("name", "");
					if ($name !== "") {
						$properties->setInt($name, $intTag->getInt("value", 0));
					}
				}
			}
		}

		$floatsList = $tag->getListTag("floats");
		if ($floatsList !== null) {
			foreach ($floatsList as $floatTag) {
				if ($floatTag instanceof CompoundTag) {
					$name = $floatTag->getString("name", "");
					if ($name !== "") {
						$properties->setFloat($name, $floatTag->getFloat("value", 0.0));
					}
				}
			}
		}

		return $properties;
	}
}
