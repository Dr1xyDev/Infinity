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

namespace pocketmine\level\generator\vanilla\event\server;

use pocketmine\level\generator\vanilla\command\CommandSender;
use pocketmine\level\generator\vanilla\event\Cancellable;

/**
 * Called when any CommandSender runs a command, early in the process
 *
 * You don't want to use this except for a few cases like logging commands,
 * blocking commands on certain places, or applying modifiers.
 *
 * The message DOES NOT contain a slash at the start
 */
class CommandEvent extends ServerEvent implements Cancellable
{
	/** @var string */
	protected $command;

	/** @var CommandSender */
	protected $sender;

	public function __construct(CommandSender $sender, string $command)
	{
		$this->sender = $sender;
		$this->command = $command;
	}

	public function getSender() : CommandSender
	{
		return $this->sender;
	}

	public function getCommand() : string
	{
		return $this->command;
	}

	public function setCommand(string $command) : void
	{
		$this->command = $command;
	}
}
