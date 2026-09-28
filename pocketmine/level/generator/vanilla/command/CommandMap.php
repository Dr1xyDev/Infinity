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

namespace pocketmine\level\generator\vanilla\command;

interface CommandMap
{
	/**
	 * @param Command[] $commands
	 *
	 * @return void
	 */
	public function registerAll(string $fallbackPrefix, array $commands);

	public function register(string $fallbackPrefix, Command $command, ?string $label = null) : bool;

	public function dispatch(CommandSender $sender, string $cmdLine) : bool;

	/**
	 * @return void
	 */
	public function clearCommands();

	/**
	 * @return Command|null
	 */
	public function getCommand(string $name);

}
