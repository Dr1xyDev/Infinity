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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandOverload;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\Player;

use function count;

class TransferServerCommand extends VanillaCommand
{
	public function __construct(string $name)
	{
		parent::__construct($name, "%pocketmine.command.transferserver.description", "%pocketmine.command.transferserver.usage", [], [
			new CommandOverload(false, [
				CommandParameter::standard("ip", AvailableCommandsPacket::ARG_TYPE_VALUE),
				CommandParameter::standard("port", AvailableCommandsPacket::ARG_TYPE_INT, 0, true)
			])
		]);
		$this->setPermission("pocketmine.command.transferserver");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (!$this->testPermission($sender)) {
			return true;
		}

		if (count($args) < 1) {
			throw new InvalidCommandSyntaxException();
		} elseif (!($sender instanceof Player)) {
			$sender->sendMessage("This command must be executed as a player");

			return false;
		}

		$sender->transfer($args[0], (int) ($args[1] ?? 19132));

		return true;
	}
}
