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

use function array_chunk;
use function array_pop;
use function count;
use function explode;
use function implode;
use function is_numeric;
use function ksort;
use function min;
use function strtolower;

use const SORT_FLAG_CASE;
use const SORT_NATURAL;

class HelpCommand extends VanillaCommand
{
	public function __construct(string $name)
	{
		parent::__construct(
			$name,
			"%pocketmine.command.help.description",
			"%commands.help.usage",
			["?"]
		);
		$this->setPermission("pocketmine.command.help");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (!$this->testPermission($sender)) {
			return true;
		}

		if (count($args) === 0) {
			$commandName = "";
			$pageNumber = 1;
		} elseif (is_numeric($args[count($args) - 1])) {
			$pageNumber = (int) array_pop($args);
			if ($pageNumber <= 0) {
				$pageNumber = 1;
			}
			$commandName = implode(" ", $args);
		} else {
			$commandName = implode(" ", $args);
			$pageNumber = 1;
		}

		$pageHeight = $sender->getScreenLineHeight();

		if ($commandName === "") {
			/** @var Command[][] $commands */
			$commands = [];
			foreach ($sender->getServer()->getCommandMap()->getCommands() as $command) {
				if ($command->testPermissionSilent($sender)) {
					$commands[$command->getName()] = $command;
				}
			}
			ksort($commands, SORT_NATURAL | SORT_FLAG_CASE);
			$commands = array_chunk($commands, $pageHeight);
			$pageNumber = min(count($commands), $pageNumber);
			if ($pageNumber < 1) {
				$pageNumber = 1;
			}
			$sender->sendMessage(new TranslationContainer("commands.help.header", [$pageNumber, count($commands)]));
			if (isset($commands[$pageNumber - 1])) {
				foreach ($commands[$pageNumber - 1] as $command) {
					$sender->sendMessage(TextFormat::DARK_GREEN . "/" . $command->getName() . ": " . TextFormat::WHITE . $command->getDescription());
				}
			}

			return true;
		} else {
			if (($cmd = $sender->getServer()->getCommandMap()->getCommand(strtolower($commandName))) instanceof Command) {
				if ($cmd->testPermissionSilent($sender)) {
					$message = TextFormat::YELLOW . "--------- " . TextFormat::WHITE . " Help: /" . $cmd->getName() . TextFormat::YELLOW . " ---------\n";
					$message .= TextFormat::GOLD . "Description: " . TextFormat::WHITE . $cmd->getDescription() . "\n";
					$message .= TextFormat::GOLD . "Usage: " . TextFormat::WHITE . implode("\n" . TextFormat::WHITE, explode("\n", $cmd->getUsage())) . "\n";
					$sender->sendMessage($message);

					return true;
				}
			}
			$sender->sendMessage(TextFormat::RED . "No help for " . strtolower($commandName));

			return true;
		}
	}
}
