<?php

/*
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.0
 *               InfinityProject By @Dr1xyDev
 *   YT:         @Dr1xyDev
 *   GitHub:     github.com/Dr1xyDev/Infinity
*/

namespace pocketmine\command\defaults;

use pocketmine\command\CommandSender;
use pocketmine\network\protocol\Info;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;

class VersionCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"%pocketmine.command.version.description",
			"%pocketmine.command.version.usage",
			["ver", "about"]
		);
		$this->setPermission("pocketmine.command.version");
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) === 0){
			$sender->sendMessage("§9§lInfinity §ev1.0§r");
			$sender->sendMessage("§7- §eVersion: §f0.15.10");
			$sender->sendMessage("§7- §eApi:§f " . $sender->getServer()->getApiVersion());
			$sender->sendMessage("§7- §ePHP: §f" . PHP_VERSION);
			return true;
		}

		$pluginName = implode(" ", $args);
		$exactPlugin = $sender->getServer()->getPluginManager()->getPlugin($pluginName);

		if($exactPlugin instanceof Plugin){
			$this->describeToSender($exactPlugin, $sender);
			return true;
		}

		$found = false;
		$pluginName = strtolower($pluginName);

		foreach($sender->getServer()->getPluginManager()->getPlugins() as $plugin){
			if(stripos($plugin->getName(), $pluginName) !== false){
				$this->describeToSender($plugin, $sender);
				$found = true;
			}
		}

		if(!$found){
			$sender->sendMessage("§cNo such plugin.");
		}

		return true;
	}

	private function describeToSender(Plugin $plugin, CommandSender $sender){
		$desc = $plugin->getDescription();
		$sender->sendMessage(TextFormat::DARK_GREEN . $desc->getName() . TextFormat::WHITE . " version " . TextFormat::DARK_GREEN . $desc->getVersion());

		if($desc->getDescription() !== null){
			$sender->sendMessage($desc->getDescription());
		}

		if($desc->getWebsite() !== null){
			$sender->sendMessage("Website: " . $desc->getWebsite());
		}

		$authors = $desc->getAuthors();
		if(count($authors) > 0){
			if(count($authors) === 1){
				$sender->sendMessage("Author: " . implode(", ", $authors));
			}else{
				$sender->sendMessage("Authors: " . implode(", ", $authors));
			}
		}
	}
}