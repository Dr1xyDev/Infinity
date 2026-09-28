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

namespace pocketmine\level\generator\vanilla\command\defaults;

use pocketmine\level\generator\vanilla\command\Command;
use pocketmine\level\generator\vanilla\command\CommandSender;
use pocketmine\level\generator\vanilla\lang\TranslationContainer;
use pocketmine\level\generator\vanilla\utils\TextFormat;

class ReloadCommand extends VanillaCommand
{
	public function __construct(string $name)
	{
		parent::__construct(
			$name,
			"%pocketmine.command.reload.description",
			"%pocketmine.command.reload.usage"
		);
		$this->setPermission("pocketmine.command.reload");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (!$this->testPermission($sender)) {
			return true;
		}

		Command::broadcastCommandMessage($sender, new TranslationContainer(TextFormat::YELLOW . "%pocketmine.command.reload.reloading"));

		$sender->getServer()->reload();
		Command::broadcastCommandMessage($sender, new TranslationContainer(TextFormat::YELLOW . "%pocketmine.command.reload.reloaded"));

		return true;
	}
}
