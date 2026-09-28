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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandOverload;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\utils\TextFormat;

use function array_shift;
use function count;
use function implode;

class TellCommand extends VanillaCommand
{
	public function __construct(string $name)
	{
		parent::__construct($name, "%pocketmine.command.tell.description", "%commands.message.usage", ["w", "msg"], [
			new CommandOverload(false, [
				CommandParameter::standard("player", AvailableCommandsPacket::ARG_TYPE_TARGET),
				CommandParameter::standard("message", AvailableCommandsPacket::ARG_TYPE_MESSAGE)
			])
		]);
		$this->setPermission("pocketmine.command.tell");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (!$this->testPermission($sender)) {
			return true;
		}

		if (count($args) < 2) {
			throw new InvalidCommandSyntaxException();
		}

		$player = $sender->getServer()->getPlayer(array_shift($args));

		if ($player === $sender) {
			$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.message.sameTarget"));
			return true;
		}

		if ($player instanceof Player) {
			$sender->sendMessage("[{$sender->getName()} -> {$player->getDisplayName()}] " . implode(" ", $args));
			$name = $sender instanceof Player ? $sender->getDisplayName() : $sender->getName();
			$player->sendMessage("[$name -> {$player->getName()}] " . implode(" ", $args));
		} else {
			$sender->sendMessage(new TranslationContainer("commands.generic.player.notFound"));
		}

		return true;
	}
}
