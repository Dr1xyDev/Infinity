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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\StopSoundPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandOverload;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\level\generator\vanilla\utils\TextFormat;

use function strlen;

class StopSoundCommand extends VanillaCommand
{
	public function __construct(string $name)
	{
		parent::__construct($name, "Stops a sound or all sounds", "/stopsound <player: target> [sound: string]", [], [
			new CommandOverload(false, [
				CommandParameter::standard("player", AvailableCommandsPacket::ARG_TYPE_TARGET),
				CommandParameter::standard("sound", AvailableCommandsPacket::ARG_TYPE_STRING, 0, true)
			])
		]);

		$this->setPermission("pocketmine.command.stopsound");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (!$this->testPermission($sender)) {
			return true;
		}

		if (empty($args)) {
			throw new InvalidCommandSyntaxException();
		}

		$player = $sender->getServer()->getPlayer($args[0]);

		if ($player === null) {
			$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.player.notFound"));
			return true;
		}

		$soundName = $args[1] ?? "";
		$stopAll = strlen($soundName) === 0;

		$pk = new StopSoundPacket();
		$pk->soundName = $soundName;
		$pk->stopAll = $stopAll;
		$player->sendDataPacket($pk);

		$message = $stopAll ? new TranslationContainer("commands.stopsound.success.all", [$player->getName()]) : new TranslationContainer("commands.stopsound.success", [
			$soundName, $player->getName()
		]);
		$player->sendMessage($message);

		return true;
	}
}
