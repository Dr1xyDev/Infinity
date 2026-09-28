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
use pocketmine\level\generator\vanilla\level\Position;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandOverload;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\utils\TextFormat;

use function count;
use function round;

class SpawnpointCommand extends VanillaCommand
{
	public function __construct(string $name)
	{
		parent::__construct($name, "%pocketmine.command.spawnpoint.description", "%commands.spawnpoint.usage", [], [
			new CommandOverload(false, [
				CommandParameter::standard("player", AvailableCommandsPacket::ARG_TYPE_TARGET, 0, true),
				CommandParameter::standard("spawnPoint", AvailableCommandsPacket::ARG_TYPE_POSITION, 0, true)
			])
		]);
		$this->setPermission("pocketmine.command.spawnpoint");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (!$this->testPermission($sender)) {
			return true;
		}

		$target = null;

		if (count($args) === 0) {
			if ($sender instanceof Player) {
				$target = $sender;
			} else {
				$sender->sendMessage(TextFormat::RED . "Please provide a player!");

				return true;
			}
		} else {
			$target = $sender->getServer()->getPlayer($args[0]);
			if ($target === null) {
				$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.player.notFound"));

				return true;
			}
		}

		if (count($args) === 4) {
			if ($target->isValid()) {
				$level = $target->getLevel();
				$pos = $sender instanceof Player ? $sender->getPosition() : $level->getSpawnLocation();
				$x = $this->getRelativeDouble($pos->x, $sender, $args[1]);
				$y = $this->getRelativeDouble($pos->y, $sender, $args[2], Level::Y_MIN, Level::Y_MAX);
				$z = $this->getRelativeDouble($pos->z, $sender, $args[3]);
				$target->setSpawn(new Position($x, $y, $z, $level));

				Command::broadcastCommandMessage($sender, new TranslationContainer("commands.spawnpoint.success", [$target->getName(), round($x, 2), round($y, 2), round($z, 2)]));

				return true;
			}
		} elseif (count($args) <= 1) {
			if ($sender instanceof Player) {
				$pos = new Position($sender->getFloorX(), $sender->getFloorY(), $sender->getFloorZ(), $sender->getLevel());
				$target->setSpawn($pos);

				Command::broadcastCommandMessage($sender, new TranslationContainer("commands.spawnpoint.success", [$target->getName(), round($pos->x, 2), round($pos->y, 2), round($pos->z, 2)]));
				return true;
			} else {
				$sender->sendMessage(TextFormat::RED . "Please provide a player!");

				return true;
			}
		}

		throw new InvalidCommandSyntaxException();
	}
}
