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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command;

class CommandEnumConstraint
{
	public const REQUIRES_CHEATS_ENABLED = 1 << 0;
	public const REQUIRES_ELEVATED_PERMISSIONS = 1 << 1;
	public const REQUIRES_HOST_PERMISSIONS = 1 << 2;
	public const REQUIRES_ALLOW_ALIASES = 1 << 3;

	/**
	 * @param int[] $constraints
	 */
	public function __construct(
		private CommandEnum $enum,
		private int $valueOffset,
		private array $constraints
	) {
		(static function (int ...$_) : void {})(...$constraints);
		if (!isset($enum->getValues()[$valueOffset])) {
			throw new \InvalidArgumentException("Invalid enum value offset $valueOffset");
		}
	}

	public function getEnum() : CommandEnum
	{
		return $this->enum;
	}

	public function getValueOffset() : int
	{
		return $this->valueOffset;
	}

	public function getAffectedValue() : string
	{
		return $this->enum->getValues()[$this->valueOffset];
	}

	/**
	 * @return int[]
	 */
	public function getConstraints() : array
	{
		return $this->constraints;
	}
}
