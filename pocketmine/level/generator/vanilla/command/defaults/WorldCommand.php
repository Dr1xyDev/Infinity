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
use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandOverload;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\Player;
use pocketmine\Server;

use function count;

class WorldCommand extends VanillaCommand
{
	public function __construct(string $name)
	{
		parent::__construct($name, "%pocketmine.command.world.description", "%commands.world.usage", [], [
			new CommandOverload(false, [
				CommandParameter::standard("world", AvailableCommandsPacket::ARG_TYPE_RAWTEXT),
				CommandParameter::standard("player", AvailableCommandsPacket::ARG_TYPE_TARGET, 0, true)
			])
		]);
		$this->setPermission("pocketmine.command.world");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (!$this->testPermission($sender)) {
			return true;
		}

		$countArgs = count($args);
		if ($countArgs <= 0 || $countArgs > 2) {
			throw new InvalidCommandSyntaxException();
		}

		$level = $this->getLevelByName($sender->getServer(), $args[0]);
		if ($countArgs === 1) {
			if ($sender instanceof Player) {
				if ($level !== null) {
					$sender->teleport($level->getSpawnLocation());
					$sender->sendMessage(new TranslationContainer("commands.world.teleport.self", [$level->getFolderName()]));
				} else {
					$sender->sendMessage(new TranslationContainer("commands.world.level.notFound"));
				}
			}
		} elseif (count($args) === 2) {
			if (($target = $sender->getServer()->getPlayer($args[1])) instanceof Player) {
				if ($level !== null) {
					$target->teleport($level->getSpawnLocation());
					$target->sendMessage(new TranslationContainer("commands.world.teleport.self", [$level->getFolderName()]));
					$sender->sendMessage(new TranslationContainer("commands.word.teleport.other", [$level->getFolderName()]));
				} else {
					$sender->sendMessage(new TranslationContainer("commands.world.level.notFound"));
				}
			} else {
				$sender->sendMessage(new TranslationContainer("commands.generic.player.notFound"));
			}
		}

		return true;
	}

	private function getLevelByName(Server $manager, string $name) : ?Level
	{
		if ($manager->isLevelGenerated($name)) {
			if (($level = $manager->getLevelByName($name)) !== null) {
				return $level;
			} else {
				$manager->loadLevel($name);
				return $manager->getLevelByName($name);
			}
		}

		return null;
	}
}
