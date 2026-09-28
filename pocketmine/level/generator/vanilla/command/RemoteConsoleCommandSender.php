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

use pocketmine\level\generator\vanilla\lang\TextContainer;

use function sprintf;
use function trim;

class RemoteConsoleCommandSender extends ConsoleCommandSender
{
	/** @var string */
	private $messages = "";

	public function sendMessage($message)
	{
		if ($message instanceof TextContainer) {
			$message = $this->getServer()->getLanguage()->translate($message);
		} else {
			$message = $this->getServer()->getLanguage()->translateString($message);
		}

		$this->messages .= trim($message, "\r\n") . "\n";
	}

	public function sendMessagef(string $format, mixed ...$args) : void
	{
		$this->sendMessage(sprintf($format, ...$args));
	}

	/**
	 * @return string
	 */
	public function getMessage()
	{
		return $this->messages;
	}

	public function getName() : string
	{
		return "Rcon";
	}
}
