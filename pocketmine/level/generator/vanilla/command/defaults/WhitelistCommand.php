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
use pocketmine\level\generator\vanilla\command\utils\InvalidCommandSyntaxException;
use pocketmine\level\generator\vanilla\lang\TranslationContainer;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\utils\TextFormat;

use function count;
use function implode;
use function sort;
use function strtolower;

use const SORT_STRING;

class WhitelistCommand extends VanillaCommand
{
	public function __construct(string $name)
	{
		parent::__construct(
			$name,
			"%pocketmine.command.whitelist.description",
			"%commands.whitelist.usage"
		);
		$this->setPermission("pocketmine.command.whitelist.reload;pocketmine.command.whitelist.enable;pocketmine.command.whitelist.disable;pocketmine.command.whitelist.list;pocketmine.command.whitelist.add;pocketmine.command.whitelist.remove");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (!$this->testPermission($sender)) {
			return true;
		}

		if (count($args) === 1) {
			if ($this->badPerm($sender, strtolower($args[0]))) {
				return false;
			}
			switch (strtolower($args[0])) {
				case "reload":
					$sender->getServer()->reloadWhitelist();
					Command::broadcastCommandMessage($sender, new TranslationContainer("commands.whitelist.reloaded"));

					return true;
				case "on":
					$sender->getServer()->setConfigBool("white-list", true);
					Command::broadcastCommandMessage($sender, new TranslationContainer("commands.whitelist.enabled"));

					return true;
				case "off":
					$sender->getServer()->setConfigBool("white-list", false);
					Command::broadcastCommandMessage($sender, new TranslationContainer("commands.whitelist.disabled"));

					return true;
				case "list":
					$entries = $sender->getServer()->getWhitelisted()->getAll(true);
					sort($entries, SORT_STRING);
					$result = implode(", ", $entries);
					$count = count($entries);

					$sender->sendMessage(new TranslationContainer("commands.whitelist.list", [$count, $count]));
					$sender->sendMessage($result);

					return true;

				case "add":
					$sender->sendMessage(new TranslationContainer("commands.generic.usage", ["%commands.whitelist.add.usage"]));
					return true;

				case "remove":
					$sender->sendMessage(new TranslationContainer("commands.generic.usage", ["%commands.whitelist.remove.usage"]));
					return true;
			}
		} elseif (count($args) === 2) {
			if ($this->badPerm($sender, strtolower($args[0]))) {
				return false;
			}
			if (!Player::isValidUserName($args[1])) {
				throw new InvalidCommandSyntaxException();
			}
			switch (strtolower($args[0])) {
				case "add":
					$sender->getServer()->getOfflinePlayer($args[1])->setWhitelisted(true);
					Command::broadcastCommandMessage($sender, new TranslationContainer("commands.whitelist.add.success", [$args[1]]));

					return true;
				case "remove":
					$sender->getServer()->getOfflinePlayer($args[1])->setWhitelisted(false);
					Command::broadcastCommandMessage($sender, new TranslationContainer("commands.whitelist.remove.success", [$args[1]]));

					return true;
			}
		}

		throw new InvalidCommandSyntaxException();
	}

	private function badPerm(CommandSender $sender, string $subcommand) : bool
	{
		static $map = [
			"on" => "enable",
			"off" => "disable"
		];
		if (!$sender->hasPermission("pocketmine.command.whitelist." . ($map[$subcommand] ?? $subcommand))) {
			$sender->sendMessage($sender->getServer()->getLanguage()->translateString(TextFormat::RED . "%commands.generic.permission"));

			return true;
		}

		return false;
	}
}
