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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandEnum;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandOverload;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\level\generator\vanilla\utils\TextFormat;

use function count;

class GamemodeCommand extends VanillaCommand
{
	public function __construct(string $name)
	{
		parent::__construct($name, "%pocketmine.command.gamemode.description", "%commands.gamemode.usage", ["gm"], [
			new CommandOverload(false, [
				CommandParameter::enum("gameMode", new CommandEnum("gameMode", [
					"adventure", "creative", "spectator", "survival",
				]), 0),
				CommandParameter::standard("player", AvailableCommandsPacket::ARG_TYPE_TARGET, 0, true)
			]),
			new CommandOverload(false, [
				CommandParameter::standard("gameMode", AvailableCommandsPacket::ARG_TYPE_INT),
				CommandParameter::standard("player", AvailableCommandsPacket::ARG_TYPE_TARGET, 0, true)
			])
		]);
		$this->setPermission("pocketmine.command.gamemode");
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

		if ($gameMode === -1) {
			$sender->sendMessage("Unknown game mode");

			return true;
		}

		if (isset($args[1])) {
			$target = $sender->getServer()->getPlayer($args[1]);
			if ($target === null) {
				$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.player.notFound"));

				return true;
			}
		} elseif ($sender instanceof Player) {
			$target = $sender;
		} else {
			throw new InvalidCommandSyntaxException();
		}

		$target->setGamemode($gameMode);
		if ($gameMode !== $target->getGamemode()) {
			$sender->sendMessage("Game mode change for " . $target->getName() . " failed!");
		} else {
			if ($target === $sender) {
				Command::broadcastCommandMessage($sender, new TranslationContainer("commands.gamemode.success.self", [Server::getGamemodeString($gameMode)]));
			} else {
				$target->sendMessage(new TranslationContainer("gameMode.changed", [Server::getGamemodeString($gameMode)]));
				Command::broadcastCommandMessage($sender, new TranslationContainer("commands.gamemode.success.other", [Server::getGamemodeString($gameMode), $target->getName()]));
			}
		}

		return true;
	}
}
