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
use pocketmine\level\generator\vanilla\lang\TranslationContainer;
use pocketmine\Player;

use function array_filter;
use function array_map;
use function count;
use function implode;
use function sort;

use const SORT_STRING;

class ListCommand extends VanillaCommand
{
	public function __construct(string $name)
	{
		parent::__construct(
			$name,
			"%pocketmine.command.list.description",
			"%command.players.usage"
		);
		$this->setPermission("pocketmine.command.list");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (!$this->testPermission($sender)) {
			return true;
		}

		$playerNames = array_map(function (Player $player) : string {
			return $player->getName();
		}, array_filter($sender->getServer()->getOnlinePlayers(), function (Player $player) use ($sender) : bool {
			return $player->isOnline() && (!($sender instanceof Player) || $sender->canSee($player));
		}));
		sort($playerNames, SORT_STRING);

		$sender->sendMessage(new TranslationContainer("commands.players.list", [count($playerNames), $sender->getServer()->getMaxPlayers()]));
		$sender->sendMessage(implode(", ", $playerNames));

		return true;
	}
}
