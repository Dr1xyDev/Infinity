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
use pocketmine\utils\TextFormat;

class ExtractPharCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"Extrae un plugin .phar a una carpeta en plugins/data/extract/<nombre>",
			"/ep <plugin_name>",
			["extractphar"]
		);
		$this->setPermission("pocketmine.command.extractphar");
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) < 1){
			$sender->sendMessage(TextFormat::RED . "Uso: " . $this->usageMessage);
			return true;
		}

		$name = trim(array_shift($args));
		$name = str_replace(["/", "\\", ".."], "", $name);
		$name = preg_replace("/\\.phar$/i", "", $name);

		if($name === ""){
			$sender->sendMessage(TextFormat::RED . "Nombre de plugin inválido.");
			return true;
		}

		$server = $sender->getServer();
		$pluginPath = rtrim($server->getPluginPath(), "\\/") . DIRECTORY_SEPARATOR;

		$candidates = [
			$pluginPath . "data" . DIRECTORY_SEPARATOR . "phar" . DIRECTORY_SEPARATOR . $name . ".phar",
			$pluginPath . $name . ".phar"
		];

		$pharFile = null;
		foreach($candidates as $candidate){
			if(file_exists($candidate)){
				$pharFile = $candidate;
				break;
			}
		}

		if($pharFile === null){
			$sender->sendMessage(TextFormat::RED . "No se encontró '" . $name . ".phar' en plugins/data/phar/ ni en plugins/.");
			return true;
		}

		$extractDir = $pluginPath . "data" . DIRECTORY_SEPARATOR . "extract" . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR;
		if(!is_dir($extractDir)){
			@mkdir($extractDir, 0777, true);
		}

		try{
			$phar = new \Phar($pharFile);
			$phar->extractTo($extractDir, null, true);

			$sender->sendMessage(TextFormat::GREEN . "Plugin '" . $name . "' extraído correctamente en plugins/data/extract/" . $name . "/");
			$server->getLogger()->info(TextFormat::AQUA . "[ExtractPhar] " . TextFormat::WHITE . $name . TextFormat::GRAY . " extraído de PHAR por " . $sender->getName());
		}catch(\Throwable $e){
			$sender->sendMessage(TextFormat::RED . "Error al extraer el plugin: " . $e->getMessage());
			$server->getLogger()->logException($e);
		}

		return true;
	}
}
