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
use pocketmine\level\generator\vanilla\permission\Permissible;
use pocketmine\Server;

interface CommandSender extends Permissible
{
	/**
	 * @param TextContainer|string $message
	 *
	 * @return void
	 */
	public function sendMessage($message);

	public function sendMessagef(string $format, mixed ...$args) : void;

	/**
	 * @return Server
	 */
	public function getServer();

	public function getName() : string;

	/**
	 * Returns the line height of the command-sender's screen. Used for determining sizes for command output pagination
	 * such as in the /help command.
	 */
	public function getScreenLineHeight() : int;

	/**
	 * Sets the line height used for command output pagination for this command sender. `null` will reset it to default.
	 *
	 * @return void
	 */
	public function setScreenLineHeight(?int $height = null);
}
