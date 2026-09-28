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

use function count;

class PingCommand extends VanillaCommand
{
	public function __construct(string $name)
	{
		parent::__construct($name, "%pocketmine.command.ping.description", "%commands.ping.usage", [], [
			new CommandOverload(false, [
				CommandParameter::standard("player", AvailableCommandsPacket::ARG_TYPE_TARGET, 0, true)
			])
		]);
		$this->setPermission("pocketmine.command.ping");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (!$this->testPermission($sender)) {
			return true;
		}

		if (count($args) >= 2) {
			throw new InvalidCommandSyntaxException();
		}

		$target = null;

		if (count($args) === 1) {
			$target = $sender->getServer()->getPlayer($args[0]);
		} else {
			if ($sender instanceof Player) {
				$target = $sender;
			} else {
				throw new InvalidCommandSyntaxException();
			}
		}

		if ($target === null) {
			$sender->sendMessage(new TranslationContainer("command.generic.player.notFound"));
			return true;
		}

		$ping = $target->getPing();
		$color = ($ping < 150 ? TextFormat::GREEN : ($ping < 250 ? TextFormat::GOLD : TextFormat::RED));
		$sender->sendMessage($target->getName() . "'s Ping: " . $color . $ping . "ms");

		return true;
	}
}
