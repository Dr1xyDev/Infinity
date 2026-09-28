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

namespace pocketmine\level\generator\vanilla\utils;

use InvalidArgumentException;

use function getmypid;

/**
 * This trait allows a class to simulate a Java-style enum. Members are exposed as static methods and handled via
 * __callStatic().
 *
 * Classes using this trait need to include \@method tags in their class docblock for every enum member.
 * Alternatively, just put \@generate-registry-docblock in the docblock and run tools/generate-registry-annotations.php
 */
trait EnumTrait
{
	use RegistryTrait;
	use NotCloneable;
	use NotSerializable;

	/**
	 * Registers the given object as an enum member.
	 *
	 * @throws InvalidArgumentException
	 */
	protected static function register(self $member) : void
	{
		self::_registryRegister($member->name(), $member);
	}

	protected static function registerAll(self ...$members) : void
	{
		foreach ($members as $member) {
			self::register($member);
		}
	}

	/**
	 * Returns all members of the enum.
	 * This is overridden to change the return typehint.
	 *
	 * @return self[]
	 * @phpstan-return array<string, self>
	 */
	public static function getAll() : array
	{
		//phpstan doesn't support generic traits yet :(
		/** @var self[] $result */
		$result = self::_registryGetAll();
		return $result;
	}

	public static function fromString(string $enumName) : self
	{
		return self::_registryFromString($enumName);
	}

	/** @var int|null */
	private static $nextId = null;

	/** @var string */
	private $enumName;
	/** @var int */
	private $runtimeId;

	/**
	 * @throws InvalidArgumentException
	 */
	private function __construct(string $enumName)
	{
		self::verifyName($enumName);
		$this->enumName = $enumName;
		if (self::$nextId === null) {
			self::$nextId = getmypid(); //this provides enough base entropy to prevent hardcoding
		}
		$this->runtimeId = self::$nextId++;
	}

	public function name() : string
	{
		return $this->enumName;
	}

	/**
	 * Returns a runtime-only identifier for this enum member. This will be different with each run, so don't try to
	 * hardcode it.
	 * This can be useful for switches or array indexing.
	 */
	public function id() : int
	{
		return $this->runtimeId;
	}

	/**
	 * Returns whether the two objects are equivalent.
	 */
	public function equals(self $other) : bool
	{
		return $this->enumName === $other->enumName;
	}
}
