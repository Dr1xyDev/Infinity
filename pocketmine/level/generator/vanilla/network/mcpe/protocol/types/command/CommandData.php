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

class CommandData
{
	/**
	 * @param CommandOverload[]       $overloads
	 * @param ChainedSubCommandData[] $chainedSubCommandData
	 */
	public function __construct(
		public string $name,
		public string $description,
		public int $flags,
		public CommandPermissions $permission,
		public ?CommandEnum $aliases,
		public array $overloads,
		public array $chainedSubCommandData
	) {
		(function (CommandOverload ...$overloads) : void {})(...$overloads);
		(function (ChainedSubCommandData ...$chainedSubCommandData) : void {})(...$chainedSubCommandData);
	}

	public function getName() : string
	{
		return $this->name;
	}

	public function getDescription() : string
	{
		return $this->description;
	}

	public function getFlags() : int
	{
		return $this->flags;
	}

	public function getPermission() : CommandPermissions
	{
		return $this->permission;
	}

	public function getAliases() : ?CommandEnum
	{
		return $this->aliases;
	}

	/**
	 * @return CommandOverload[]
	 */
	public function getOverloads() : array
	{
		return $this->overloads;
	}

	/**
	 * @return ChainedSubCommandData[]
	 */
	public function getChainedSubCommandData() : array
	{
		return $this->chainedSubCommandData;
	}
}
