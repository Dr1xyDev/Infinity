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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandEnum;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandOverload;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\Server;

use function count;

class DefaultGamemodeCommand extends VanillaCommand
{
	public function __construct(string $name)
	{
		parent::__construct($name, "%pocketmine.command.defaultgamemode.description", "%commands.defaultgamemode.usage", [], [
			new CommandOverload(false, [
				CommandParameter::enum("gameMode", new CommandEnum("defaultGameMode", [
					"creative", "survival", "adventure"
				]), 0),
			]),
			new CommandOverload(false, [
				CommandParameter::standard("gameMode", AvailableCommandsPacket::ARG_TYPE_INT),
			])
		]);
		$this->setPermission("pocketmine.command.defaultgamemode");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (!$this->testPermission($sender)) {
			return true;
		}

		if (count($args) === 0) {
			throw new InvalidCommandSyntaxException();
		}

		$gameMode = Server::getGamemodeFromString($args[0]);

		if ($gameMode !== -1) {
			$sender->getServer()->setConfigInt("gamemode", $gameMode);
			$sender->sendMessage(new TranslationContainer("commands.defaultgamemode.success", [Server::getGamemodeString($gameMode)]));
		} else {
			$sender->sendMessage("Unknown game mode");
		}

		return true;
	}
}
