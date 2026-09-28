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

class CommandEnum
{
	/**
	 * @param string[] $enumValues
	 * @param bool     $isSoft     Whether the enum is dynamic, i.e. can be updated during the game session
	 *
	 * @phpstan-param list<string> $enumValues
	 */
	public function __construct(
		private string $enumName,
		private array $enumValues,
		private bool $isSoft = false
	) {
	}

	public function getName() : string
	{
		return $this->enumName;
	}

	/**
	 * @return string[]
	 * @phpstan-return list<string>
	 */
	public function getValues() : array
	{
		return $this->enumValues;
	}

	/**
	 * @return bool Whether the enum is dynamic, i.e. can be updated during the game session
	 */
	public function isSoft() : bool
	{
		return $this->isSoft;
	}
}
