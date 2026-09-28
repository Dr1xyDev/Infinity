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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandOverload;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandParameter;

use function count;

class DifficultyCommand extends VanillaCommand
{
	public function __construct(string $name)
	{
		parent::__construct($name, "%pocketmine.command.difficulty.description", "%commands.difficulty.usage", [], [
			new CommandOverload(false, [
				CommandParameter::enum("difficulty", new CommandEnum("difficulty", [
					"normal", "peaceful", "easy", "hard"
				]), 0)
			]),
			new CommandOverload(false, [
				CommandParameter::standard("difficulty", AvailableCommandsPacket::ARG_TYPE_INT)
			])
		]);
		$this->setPermission("pocketmine.command.difficulty");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (!$this->testPermission($sender)) {
			return true;
		}

		if (count($args) !== 1) {
			throw new InvalidCommandSyntaxException();
		}

		$difficulty = Level::getDifficultyFromString($args[0]);

		if ($sender->getServer()->isHardcore()) {
			$difficulty = Level::DIFFICULTY_HARD;
		}

		if ($difficulty !== -1) {
			$sender->getServer()->setConfigInt("difficulty", $difficulty);

			//TODO: add per-world support
			foreach ($sender->getServer()->getLevels() as $level) {
				$level->setDifficulty($difficulty);
			}

			Command::broadcastCommandMessage($sender, new TranslationContainer("commands.difficulty.success", [$difficulty]));
		} else {
			throw new InvalidCommandSyntaxException();
		}

		return true;
	}
}
