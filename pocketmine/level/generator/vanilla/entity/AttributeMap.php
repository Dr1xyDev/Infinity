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

use ArrayAccess;
use InvalidArgumentException;
use RuntimeException;

use function array_filter;

/**
 * @phpstan-implements ArrayAccess<int, float>
 */
class AttributeMap implements ArrayAccess
{
	/** @var Attribute[] */
	private $attributes = [];

	public function addAttribute(Attribute $attribute) : void
	{
		$this->attributes[$attribute->getId()] = $attribute;
	}

	public function getAttribute(int $id) : ?Attribute
	{
		return $this->attributes[$id] ?? null;
	}

	/**
	 * @return Attribute[]
	 */
	public function getAll() : array
	{
		return $this->attributes;
	}

	/**
	 * @return Attribute[]
	 */
	public function needSend() : array
	{
		return array_filter($this->attributes, function (Attribute $attribute) : bool {
			return $attribute->isSyncable() && $attribute->isDesynchronized();
		});
	}

	/**
	 * @param int $offset
	 */
	public function offsetExists($offset) : bool
	{
		return isset($this->attributes[$offset]);
	}

	/**
	 * @param int $offset
	 */
	public function offsetGet($offset) : float
	{
		return $this->attributes[$offset]->getValue();
	}

	/**
	 * @param int|null $offset
	 * @param float    $value
	 */
	public function offsetSet($offset, $value) : void
	{
		if ($offset === null) {
			throw new InvalidArgumentException("Array push syntax is not supported");
		}
		$this->attributes[$offset]->setValue($value);
	}

	/**
	 * @param int $offset
	 */
	public function offsetUnset($offset) : void
	{
		throw new RuntimeException("Could not unset an attribute from an attribute map");
	}
}
