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
use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandEnum;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\utils\TextFormat;

use function count;

class TimeCommand extends VanillaCommand
{
	public function __construct(string $name)
	{
		parent::__construct($name, "%pocketmine.command.time.description", "%pocketmine.command.time.usage");
		$this->setPermission("pocketmine.command.time.add;pocketmine.command.time.set;pocketmine.command.time.start;pocketmine.command.time.stop");

		$amount = CommandParameter::standard("amount", AvailableCommandsPacket::ARG_TYPE_INT, 0, true);
		$set = CommandParameter::enum("set", new CommandEnum("set", ["set"]), 0);

		$this->setParameters([
			CommandParameter::enum("add", new CommandEnum("add", ["add"]), 0),
			$amount
		], 0);
		$this->setParameters([
			$set, $amount
		], 1);
		$this->setParameters([
			$set,
			CommandParameter::enum("time", new CommandEnum("time", [
				"day", "sunrise", "noon", "sunset", "night", "midnight"
			]), 0)
		], 2);
		$this->setParameters([
			CommandParameter::enum("querySE", new CommandEnum("query", ["query"]), 0),
			CommandParameter::enum("query", new CommandEnum("queryTime", [
				"day", "sunrise", "noon", "sunset", "night", "midnight"
			]), 0)
		], 3);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (count($args) < 1) {
			throw new InvalidCommandSyntaxException();
		}

		if ($args[0] === "start") {
			if (!$sender->hasPermission("pocketmine.command.time.start")) {
				$sender->sendMessage($sender->getServer()->getLanguage()->translateString(TextFormat::RED . "%commands.generic.permission"));

				return true;
			}
			foreach ($sender->getServer()->getLevels() as $level) {
				$level->startTime();
			}
			Command::broadcastCommandMessage($sender, "Restarted the time");
			return true;
		} elseif ($args[0] === "stop") {
			if (!$sender->hasPermission("pocketmine.command.time.stop")) {
				$sender->sendMessage($sender->getServer()->getLanguage()->translateString(TextFormat::RED . "%commands.generic.permission"));

				return true;
			}
			foreach ($sender->getServer()->getLevels() as $level) {
				$level->stopTime();
			}
			Command::broadcastCommandMessage($sender, "Stopped the time");
			return true;
		} elseif ($args[0] === "query") {
			if (!$sender->hasPermission("pocketmine.command.time.query")) {
				$sender->sendMessage($sender->getServer()->getLanguage()->translateString(TextFormat::RED . "%commands.generic.permission"));

				return true;
			}
			if ($sender instanceof Player) {
				$level = $sender->getLevel();
			} else {
				$level = $sender->getServer()->getDefaultLevel();
			}
			$sender->sendMessage($sender->getServer()->getLanguage()->translateString("commands.time.query", [$level->getTime()]));
			return true;
		}

		if (count($args) < 2) {
			throw new InvalidCommandSyntaxException();
		}

		if ($args[0] === "set") {
			if (!$sender->hasPermission("pocketmine.command.time.set")) {
				$sender->sendMessage($sender->getServer()->getLanguage()->translateString(TextFormat::RED . "%commands.generic.permission"));

				return true;
			}

			switch ($args[1]) {
				case "day":
					$value = Level::TIME_DAY;
					break;
				case "noon":
					$value = Level::TIME_NOON;
					break;
				case "sunset":
					$value = Level::TIME_SUNSET;
					break;
				case "night":
					$value = Level::TIME_NIGHT;
					break;
				case "midnight":
					$value = Level::TIME_MIDNIGHT;
					break;
				case "sunrise":
					$value = Level::TIME_SUNRISE;
					break;
				default:
					$value = $this->getInteger($sender, $args[1], 0);
					break;
			}

			foreach ($sender->getServer()->getLevels() as $level) {
				$level->setTime($value);
			}
			Command::broadcastCommandMessage($sender, new TranslationContainer("commands.time.set", [$value]));
		} elseif ($args[0] === "add") {
			if (!$sender->hasPermission("pocketmine.command.time.add")) {
				$sender->sendMessage($sender->getServer()->getLanguage()->translateString(TextFormat::RED . "%commands.generic.permission"));

				return true;
			}

			$value = $this->getInteger($sender, $args[1], 0);
			foreach ($sender->getServer()->getLevels() as $level) {
				$level->setTime($level->getTime() + $value);
			}
			Command::broadcastCommandMessage($sender, new TranslationContainer("commands.time.added", [$value]));
		} else {
			throw new InvalidCommandSyntaxException();
		}

		return true;
	}
}
