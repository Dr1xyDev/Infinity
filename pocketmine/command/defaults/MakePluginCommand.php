<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\command\defaults;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class MakePluginCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"Empaqueta un plugin de la carpeta plugins/<nombre> en un .phar",
			"/mp <plugin_name>",
			["makeplugin"]
		);
		$this->setPermission("pocketmine.command.makeplugin");
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
		$name = preg_replace('/[\x00-\x1F\x7F]+/', '', $name);
		$name = trim($name);

		if($name === ""){
			$sender->sendMessage(TextFormat::RED . "Nombre de plugin inválido.");
			return true;
		}

		$server = $sender->getServer();
		$pluginPath = rtrim($server->getPluginPath(), "\\/") . DIRECTORY_SEPARATOR;
		$srcDir = null;

		
		
		
		
		$loadedPlugin = $server->getPluginManager()->getPlugin($name);
		if($loadedPlugin !== null){
			$candidate = rtrim($loadedPlugin->getDataFolder(), "\\/");
			if(is_dir($candidate) and file_exists($candidate . DIRECTORY_SEPARATOR . "plugin.yml")){
				$srcDir = $candidate;
			}
		}

		
		if($srcDir === null){
			$candidate = rtrim($pluginPath . $name, "\\/");
			if(is_dir($candidate) and file_exists($candidate . DIRECTORY_SEPARATOR . "plugin.yml")){
				$srcDir = $candidate;
			}
		}

		
		
		if($srcDir === null and is_dir($pluginPath)){
			foreach(scandir($pluginPath) as $entry){
				if($entry === "." or $entry === ".."){
					continue;
				}
				$candidate = rtrim($pluginPath . $entry, "\\/");
				if(is_dir($candidate) and strcasecmp(trim($entry), $name) === 0
					and file_exists($candidate . DIRECTORY_SEPARATOR . "plugin.yml")){
					$srcDir = $candidate;
					break;
				}
			}
		}

		if($srcDir === null){
			$sender->sendMessage(TextFormat::RED . "No se encontró la carpeta del plugin '" . $name . "' con un plugin.yml válido en " . $pluginPath);
			return true;
		}

		$outDir = $pluginPath . "data" . DIRECTORY_SEPARATOR . "phar" . DIRECTORY_SEPARATOR;
		if(!is_dir($outDir)){
			@mkdir($outDir, 0777, true);
		}

		$pharFile = $outDir . $name . ".phar";

		if(!\Phar::canWrite()){
			$sender->sendMessage(TextFormat::RED . "No se puede escribir el .phar: 'phar.readonly' está activado en tu php.ini. Ponlo en 'phar.readonly = Off' y reinicia el servidor.");
			return true;
		}

		try{
			if(file_exists($pharFile)){
				@unlink($pharFile);
			}

			$phar = new \Phar($pharFile);
			$phar->startBuffering();
			$phar->buildFromDirectory($srcDir);
			$phar->setStub("<?php __HALT_COMPILER();");

			$phar->stopBuffering();

			$sender->sendMessage(TextFormat::GREEN . "Plugin '" . $name . "' empaquetado en plugins/data/phar/" . $name . ".phar");
			$server->getLogger()->info(TextFormat::AQUA . "§e" . TextFormat::WHITE . $name . TextFormat::GRAY . " empaquetado como PHAR por " . $sender->getName());
		}catch(\Throwable $e){
			$sender->sendMessage(TextFormat::RED . "Error al empaquetar el plugin: " . $e->getMessage());
			$server->getLogger()->logException($e);
		}

		return true;
	}
}
