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
use pocketmine\level\generator\vanilla\plugin\Plugin;
use pocketmine\level\generator\vanilla\utils\TextFormat;

use function array_map;
use function count;
use function implode;
use function sort;

use const SORT_STRING;

class PluginsCommand extends VanillaCommand
{
	public function __construct(string $name)
	{
		parent::__construct(
			$name,
			"%pocketmine.command.plugins.description",
			"%pocketmine.command.plugins.usage",
			["pl"]
		);
		$this->setPermission("pocketmine.command.plugins");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (!$this->testPermission($sender)) {
			return true;
		}

		$list = array_map(function (Plugin $plugin) : string {
			return ($plugin->isEnabled() ? TextFormat::GREEN : TextFormat::RED) . $plugin->getDescription()->getFullName();
		}, $sender->getServer()->getPluginManager()->getPlugins());
		sort($list, SORT_STRING);

		$sender->sendMessage(new TranslationContainer("pocketmine.command.plugins.success", [count($list), implode(TextFormat::WHITE . ", ", $list)]));
		return true;
	}
}
