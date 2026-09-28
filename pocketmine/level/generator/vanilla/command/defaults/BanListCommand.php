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

use pocketmine\level\generator\vanilla\command\CommandSender;
use pocketmine\level\generator\vanilla\command\utils\InvalidCommandSyntaxException;
use pocketmine\level\generator\vanilla\lang\TranslationContainer;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandEnum;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandOverload;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\level\generator\vanilla\permission\BanEntry;

use function array_map;
use function count;
use function implode;
use function sort;
use function strtolower;

use const SORT_STRING;

class BanListCommand extends VanillaCommand
{
	public function __construct(string $name)
	{
		parent::__construct($name, "%pocketmine.command.banlist.description", "%commands.banlist.usage", [], [
			new CommandOverload(false, [
				CommandParameter::enum("args", new CommandEnum("banlist", [
					"ip", "players"
				]), 0)
			])
		]);
		$this->setPermission("pocketmine.command.ban.list");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (!$this->testPermission($sender)) {
			return true;
		}

		if (isset($args[0])) {
			$args[0] = strtolower($args[0]);
			if ($args[0] === "ips") {
				$list = $sender->getServer()->getIPBans();
			} elseif ($args[0] === "players") {
				$list = $sender->getServer()->getNameBans();
			} else {
				throw new InvalidCommandSyntaxException();
			}
		} else {
			$list = $sender->getServer()->getNameBans();
			$args[0] = "players";
		}

		$list = array_map(function (BanEntry $entry) : string {
			return $entry->getName();
		}, $list->getEntries());
		sort($list, SORT_STRING);
		$message = implode(", ", $list);

		if ($args[0] === "ips") {
			$sender->sendMessage(new TranslationContainer("commands.banlist.ips", [count($list)]));
		} else {
			$sender->sendMessage(new TranslationContainer("commands.banlist.players", [count($list)]));
		}

		$sender->sendMessage($message);

		return true;
	}
}
