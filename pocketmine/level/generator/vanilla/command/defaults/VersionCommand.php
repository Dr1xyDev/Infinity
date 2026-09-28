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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandOverload;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\level\generator\vanilla\plugin\Plugin;
use pocketmine\level\generator\vanilla\utils\TextFormat;

use function count;
use function end;
use function implode;
use function phpversion;
use function stripos;
use function strtolower;

class VersionCommand extends VanillaCommand
{
	public function __construct(string $name)
	{
		parent::__construct($name, "%pocketmine.command.version.description", "%pocketmine.command.version.usage", [
			"ver", "about"
		], [
			new CommandOverload(false, [
				CommandParameter::standard("plugin", AvailableCommandsPacket::ARG_TYPE_STRING, 0, true)
			])
		]);
		$this->setPermission("pocketmine.command.version");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (!$this->testPermission($sender)) {
			return true;
		}

		if (count($args) === 0) {
			$server = $sender->getServer();

			$protocols = ProtocolInfo::ACCEPTED_PROTOCOLS;

			$messages = [
				new TranslationContainer('pocketmine.server.info.extended.title', []),
				new TranslationContainer('pocketmine.server.info.extended.main', [TextFormat::GREEN . $server->getName(), TextFormat::GREEN . $server->getSubmarineVersion(), TextFormat::GREEN]),
				new TranslationContainer('pocketmine.server.info.extended.php', [TextFormat::GREEN . phpversion(), TextFormat::GREEN . phpversion('pmmpthread')]),
				new TranslationContainer('pocketmine.server.info.extended.api', [TextFormat::GREEN . $server->getApiVersion()]),
				new TranslationContainer('pocketmine.server.info.extended.version', [TextFormat::GREEN . $server->getVersion()]),
				new TranslationContainer('pocketmine.server.info.extended.protocols', [TextFormat::GREEN . $protocols[0] . " - " . end($protocols)]),
			];

			foreach ($messages as $message) {
				$sender->sendMessage($message);
			}
		} else {
			$pluginName = implode(" ", $args);
			$exactPlugin = $sender->getServer()->getPluginManager()->getPlugin($pluginName);

			if ($exactPlugin instanceof Plugin) {
				$this->describeToSender($exactPlugin, $sender);

				return true;
			}

			$found = false;
			$pluginName = strtolower($pluginName);
			foreach ($sender->getServer()->getPluginManager()->getPlugins() as $plugin) {
				if (stripos($plugin->getName(), $pluginName) !== false) {
					$this->describeToSender($plugin, $sender);
					$found = true;
				}
			}

			if (!$found) {
				$sender->sendMessage(new TranslationContainer("pocketmine.command.version.noSuchPlugin"));
			}
		}

		return true;
	}

	private function describeToSender(Plugin $plugin, CommandSender $sender) : void
	{
		$desc = $plugin->getDescription();
		$sender->sendMessage(TextFormat::DARK_GREEN . $desc->getName() . TextFormat::WHITE . " version " . TextFormat::DARK_GREEN . $desc->getVersion());

		if ($desc->getDescription() !== "") {
			$sender->sendMessage($desc->getDescription());
		}

		if ($desc->getWebsite() !== "") {
			$sender->sendMessage("Website: " . $desc->getWebsite());
		}

		if (count($authors = $desc->getAuthors()) > 0) {
			if (count($authors) === 1) {
				$sender->sendMessage("Author: " . implode(", ", $authors));
			} else {
				$sender->sendMessage("Authors: " . implode(", ", $authors));
			}
		}
	}
}
