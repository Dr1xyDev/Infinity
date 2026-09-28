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
use pocketmine\level\generator\vanilla\command\utils\CommandSelector;
use pocketmine\level\generator\vanilla\command\utils\InvalidCommandSyntaxException;
use pocketmine\level\generator\vanilla\entity\Living;
use pocketmine\level\generator\vanilla\event\entity\EntityDamageEvent;
use pocketmine\level\generator\vanilla\lang\TranslationContainer;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandOverload;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\utils\TextFormat;

use function basename;
use function count;
use function get_class;
use function implode;

class KillCommand extends VanillaCommand
{
	public function __construct(string $name)
	{
		parent::__construct($name, "%pocketmine.command.kill.description", "%pocketmine.command.kill.usage", [], [
			new CommandOverload(false, [
				CommandParameter::standard("target", AvailableCommandsPacket::ARG_TYPE_TARGET)
			])
		]);

		$this->setPermission("pocketmine.command.kill.self;pocketmine.command.kill.other");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (!$this->testPermission($sender)) {
			return true;
		}

		if (count($args) >= 2) {
			throw new InvalidCommandSyntaxException();
		}

		if (count($args) === 1) {
			if (!$sender->hasPermission("pocketmine.command.kill.other")) {
				$sender->sendMessage($sender->getServer()->getLanguage()->translateString(TextFormat::RED . "%commands.generic.permission"));

				return true;
			}

			$targets = CommandSelector::findTargets($sender, $args[0]);
			$names = [];

			foreach ($targets as $target) {
				$target->attack(new EntityDamageEvent($target, EntityDamageEvent::CAUSE_SUICIDE, 1000));
				$names[] = $target instanceof Living ? $target->getName() : basename(get_class($target), ".php");
			}

			Command::broadcastCommandMessage($sender, new TranslationContainer("commands.kill.successful", [implode(", ", $names)]));

			return true;
		}

		if ($sender instanceof Player) {
			if (!$sender->hasPermission("pocketmine.command.kill.self")) {
				$sender->sendMessage($sender->getServer()->getLanguage()->translateString(TextFormat::RED . "%commands.generic.permission"));

				return true;
			}

			$sender->attack(new EntityDamageEvent($sender, EntityDamageEvent::CAUSE_SUICIDE, 1000));
			$sender->sendMessage(new TranslationContainer("commands.kill.successful", [$sender->getName()]));
		} else {
			throw new InvalidCommandSyntaxException();
		}

		return true;
	}
}
