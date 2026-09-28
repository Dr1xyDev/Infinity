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

final class CommandOverload
{
	/**
	 * @param CommandParameter[] $parameters
	 */
	public function __construct(
		private bool $chaining,
		private array $parameters
	) {
		(function (CommandParameter ...$parameters) : void {})(...$parameters);
	}

	public function isChaining() : bool
	{
		return $this->chaining;
	}

	/**
	 * @return CommandParameter[]
	 */
	public function getParameters() : array
	{
		return $this->parameters;
	}

}
