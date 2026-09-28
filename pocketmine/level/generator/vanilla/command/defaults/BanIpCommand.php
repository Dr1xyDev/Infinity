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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandOverload;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\Player;

use function array_shift;
use function count;
use function implode;
use function preg_match;

class BanIpCommand extends VanillaCommand
{
	public function __construct(string $name)
	{
		parent::__construct($name, "%pocketmine.command.ban.ip.description", "%commands.banip.usage", [], [
			new CommandOverload(false, [
				CommandParameter::standard("ip", AvailableCommandsPacket::ARG_TYPE_VALUE),
				CommandParameter::standard("reason", AvailableCommandsPacket::ARG_TYPE_RAWTEXT)
			])
		]);
		$this->setPermission("pocketmine.command.ban.ip");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (!$this->testPermission($sender)) {
			return true;
		}

		if (count($args) === 0) {
			throw new InvalidCommandSyntaxException();
		}

		$value = array_shift($args);
		$reason = implode(" ", $args);

		if (preg_match("/^([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])$/", $value)) {
			$this->processIPBan($value, $sender, $reason);

			Command::broadcastCommandMessage($sender, new TranslationContainer("commands.banip.success", [$value]));
		} else {
			if (($player = $sender->getServer()->getPlayer($value)) instanceof Player) {
				$this->processIPBan($player->getAddress(), $sender, $reason);

				Command::broadcastCommandMessage($sender, new TranslationContainer("commands.banip.success.players", [$player->getAddress(), $player->getName()]));
			} else {
				$sender->sendMessage(new TranslationContainer("commands.banip.invalid"));

				return false;
			}
		}

		return true;
	}

	private function processIPBan(string $ip, CommandSender $sender, string $reason) : void
	{
		$sender->getServer()->getIPBans()->addBan($ip, $reason, null, $sender->getName());

		foreach ($sender->getServer()->getOnlinePlayers() as $player) {
			if ($player->getAddress() === $ip) {
				$player->kick($reason !== "" ? $reason : "IP banned.");
			}
		}

		$sender->getServer()->getNetwork()->blockAddress($ip, -1);
	}
}
