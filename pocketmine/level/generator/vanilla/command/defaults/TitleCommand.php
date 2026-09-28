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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandParameter;

use function array_slice;
use function count;
use function implode;

class TitleCommand extends VanillaCommand
{
	public function __construct(string $name)
	{
		parent::__construct($name, "%pocketmine.command.title.description", "%commands.title.usage");
		$this->setPermission("pocketmine.command.title");

		$player = CommandParameter::standard("player", AvailableCommandsPacket::ARG_TYPE_TARGET);

		$this->setParameters([
			$player,
			CommandParameter::enum("clear", new CommandEnum("clear", ["clear"]), 0)
		], 0);
		$this->setParameters([
			$player,
			CommandParameter::enum("reset", new CommandEnum("reset", ["reset"]), 0)
		], 1);
		$this->setParameters([
			$player,
			CommandParameter::enum("titleSet", new CommandEnum("titleSet", [
				"title", "subtitle", "actionbar"
			]), 0),
			CommandParameter::standard("titleText", AvailableCommandsPacket::ARG_TYPE_RAWTEXT)
		], 2);
		$this->setParameters([
			$player,
			CommandParameter::enum("times", new CommandEnum("times", ["times"]), 0),
			CommandParameter::standard("fadeIn", AvailableCommandsPacket::ARG_TYPE_INT, 0, true),
			CommandParameter::standard("stay", AvailableCommandsPacket::ARG_TYPE_INT, 0, true),
			CommandParameter::standard("fadeOut", AvailableCommandsPacket::ARG_TYPE_INT, 0, true)
		], 3);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (!$this->testPermission($sender)) {
			return true;
		}

		if (count($args) < 2) {
			throw new InvalidCommandSyntaxException();
		}

		$player = $sender->getServer()->getPlayer($args[0]);
		if ($player === null) {
			$sender->sendMessage(new TranslationContainer("commands.generic.player.notFound"));
			return true;
		}

		switch ($args[1]) {
			case "clear":
				$player->removeTitles();
				break;
			case "reset":
				$player->resetTitles();
				break;
			case "title":
				if (count($args) < 3) {
					throw new InvalidCommandSyntaxException();
				}

				$player->sendTitle(implode(" ", array_slice($args, 2)));
				break;
			case "subtitle":
				if (count($args) < 3) {
					throw new InvalidCommandSyntaxException();
				}

				$player->sendSubTitle(implode(" ", array_slice($args, 2)));
				break;
			case "actionbar":
				if (count($args) < 3) {
					throw new InvalidCommandSyntaxException();
				}

				$player->sendActionBarMessage(implode(" ", array_slice($args, 2)));
				break;
			case "times":
				if (count($args) < 5) {
					throw new InvalidCommandSyntaxException();
				}

				$player->setTitleDuration($this->getInteger($sender, $args[2]), $this->getInteger($sender, $args[3]), $this->getInteger($sender, $args[4]));
				break;
			default:
				throw new InvalidCommandSyntaxException();
		}

		$sender->sendMessage(new TranslationContainer("commands.title.success"));

		return true;
	}
}
