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

namespace pocketmine\level\generator\vanilla\entity;

use pocketmine\level\generator\vanilla\event\entity\EntityDataPropertyChangeEvent;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\math\Vector3;
use RuntimeException;

use function assert;
use function is_float;
use function is_int;
use function is_string;

class DataPropertyManager
{
	/**
	 * @var mixed[][]
	 * @phpstan-var array<int, array{0: int, 1: mixed}>
	 */
	private $properties = [];

	/**
	 * @var mixed[][]
	 * @phpstan-var array<int, array{0: int, 1: mixed}>
	 */
	private $dirtyProperties = [];

	public function __construct(
		private readonly Entity $entity,
	) {
	}

	public function getByte(int $key) : ?int
	{
		$value = $this->getPropertyValue($key, Entity::DATA_TYPE_BYTE);
		assert(is_int($value) || $value === null);
		return $value;
	}

	public function setByte(int $key, int $value, bool $force = false) : void
	{
		$this->setPropertyValue($key, Entity::DATA_TYPE_BYTE, $value, $force);
	}

	public function getShort(int $key) : ?int
	{
		$value = $this->getPropertyValue($key, Entity::DATA_TYPE_SHORT);
		assert(is_int($value) || $value === null);
		return $value;
	}

	public function setShort(int $key, int $value, bool $force = false) : void
	{
		$this->setPropertyValue($key, Entity::DATA_TYPE_SHORT, $value, $force);
	}

	public function getInt(int $key) : ?int
	{
		$value = $this->getPropertyValue($key, Entity::DATA_TYPE_INT);
		assert(is_int($value) || $value === null);
		return $value;
	}

	public function setInt(int $key, int $value, bool $force = false) : void
	{
		$this->setPropertyValue($key, Entity::DATA_TYPE_INT, $value, $force);
	}

	public function getFloat(int $key) : ?float
	{
		$value = $this->getPropertyValue($key, Entity::DATA_TYPE_FLOAT);
		assert(is_float($value) || $value === null);
		return $value;
	}

	public function setFloat(int $key, float $value, bool $force = false) : void
	{
		$this->setPropertyValue($key, Entity::DATA_TYPE_FLOAT, $value, $force);
	}

	public function getString(int $key) : ?string
	{
		$value = $this->getPropertyValue($key, Entity::DATA_TYPE_STRING);
		assert(is_string($value) || $value === null);
		return $value;
	}

	public function setString(int $key, string $value, bool $force = false) : void
	{
		$this->setPropertyValue($key, Entity::DATA_TYPE_STRING, $value, $force);
	}

	public function getItem(int $key) : ?Item
	{
		$value = $this->getPropertyValue($key, Entity::DATA_TYPE_SLOT);
		assert($value instanceof Item || $value === null);

		return $value;
	}

	public function setItem(int $key, Item $value, bool $force = false) : void
	{
		$this->setPropertyValue($key, Entity::DATA_TYPE_SLOT, $value, $force);
	}

	public function getBlockPos(int $key) : ?Vector3
	{
		$value = $this->getPropertyValue($key, Entity::DATA_TYPE_POS);
		assert($value instanceof Vector3 || $value === null);
		return $value;
	}

	public function setBlockPos(int $key, ?Vector3 $value, bool $force = false) : void
	{
		$this->setPropertyValue($key, Entity::DATA_TYPE_POS, $value ? $value->floor() : null, $force);
	}

	public function getLong(int $key) : ?int
	{
		$value = $this->getPropertyValue($key, Entity::DATA_TYPE_LONG);
		assert(is_int($value) || $value === null);
		return $value;
	}

	public function setLong(int $key, int $value, bool $force = false) : void
	{
		$this->setPropertyValue($key, Entity::DATA_TYPE_LONG, $value, $force);
	}

	public function getVector3(int $key) : ?Vector3
	{
		$value = $this->getPropertyValue($key, Entity::DATA_TYPE_VECTOR3F);
		assert($value instanceof Vector3 || $value === null);
		return $value;
	}

	public function setVector3(int $key, ?Vector3 $value, bool $force = false) : void
	{
		$this->setPropertyValue($key, Entity::DATA_TYPE_VECTOR3F, $value ? $value->asVector3() : null, $force);
	}

	public function removeProperty(int $key) : void
	{
		unset($this->properties[$key]);
	}

	public function hasProperty(int $key) : bool
	{
		return isset($this->properties[$key]);
	}

	public function getPropertyType(int $key) : int
	{
		if (isset($this->properties[$key])) {
			return $this->properties[$key][0];
		}

		return -1;
	}

	private function checkType(int $key, int $type) : void
	{
		if (isset($this->properties[$key]) && $this->properties[$key][0] !== $type) {
			throw new RuntimeException("Expected type $type, but have " . $this->properties[$key][0]);
		}
	}

	/**
	 * @return mixed
	 */
	public function getPropertyValue(int $key, int $type)
	{
		if ($type !== -1) {
			$this->checkType($key, $type);
		}
		return isset($this->properties[$key]) ? $this->properties[$key][1] : null;
	}

	/**
	 * @param mixed $value
	 */
	public function setPropertyValue(int $key, int $type, $value, bool $force = false) : void
	{
		($ev = new EntityDataPropertyChangeEvent($this->entity, $key, $type, $value, $force))->call();

		if ($ev->isCancelled()) {
			return;
		}

		if (!$force) {
			$this->checkType($key, $type);
		}
		$this->properties[$key] = $this->dirtyProperties[$key] = [$type, $value];
	}

	/**
	 * Returns all properties.
	 *
	 * @return mixed[][]
	 * @phpstan-return array<int, array{0: int, 1: mixed}>
	 */
	public function getAll() : array
	{
		return $this->properties;
	}

	/**
	 * Returns properties that have changed and need to be broadcasted.
	 *
	 * @return mixed[][]
	 * @phpstan-return array<int, array{0: int, 1: mixed}>
	 */
	public function getDirty() : array
	{
		return $this->dirtyProperties;
	}

	/**
	 * Clears records of dirty properties.
	 */
	public function clearDirtyProperties() : void
	{
		$this->dirtyProperties = [];
	}
}
