<?php
/*    
 * ░▀█▀░█▀█░█▀▀░▀█▀░█▀█░▀█▀░▀█▀░█░█    
 * ░░█░░█░█░█▀▀░░█░░█░█░░█░░░█░░░█░    
 * ░▀▀▀░▀░▀░▀░░░▀▀▀░▀░▀░▀▀▀░░▀░░░▀░v1.1
 *               InfinityProject By @Dr1xyDev    
 *   YT:         @Dr1xyDev    
 *   GitHub:     github.com/Dr1xyDev/Infinity    
*/

namespace pocketmine\plugin;

use pocketmine\event\plugin\PluginDisableEvent;
use pocketmine\event\plugin\PluginEnableEvent;
use pocketmine\Server;
use pocketmine\utils\PluginException;
use pocketmine\utils\TextFormat;

class FolderPluginLoader implements PluginLoader{

	
	private $server;

	
	public function __construct(Server $server){
		$this->server = $server;
	}

	
	public function loadPlugin($file){
		if(($description = $this->getPluginDescription($file)) instanceof PluginDescription){
			$this->server->getLogger()->info($this->server->getLanguage()->translateString("pocketmine.plugin.load", [$description->getFullName()]));
			$this->server->getLogger()->info(TextFormat::BLUE . "<" . $description->getName() . "> " . TextFormat::GRAY . "( " . TextFormat::AQUA . "Folder" . TextFormat::GRAY . " ) " . TextFormat::YELLOW . "cargando...");

			$dataFolder = rtrim($file, "\\/");

			$file = rtrim($file, "\\/");
			$className = $description->getMain();
			$this->server->getLoader()->addPath("$file/src");

			if(class_exists($className, true)){
				$plugin = new $className();
				$this->initPlugin($plugin, $description, $dataFolder, $file);

				return $plugin;
			}else{
				throw new PluginException("Couldn't load plugin " . $description->getName() . ": main class not found");
			}
		}

		return null;
	}

	
	public function getPluginDescription($file){
		if(is_dir($file)){
			$pluginYml = rtrim($file, "\\/") . "/plugin.yml";
			if(file_exists($pluginYml)){
				$yaml = file_get_contents($pluginYml);
				if($yaml !== false){
					return new PluginDescription($yaml);
				}
			}
		}

		return null;
	}

	
	public function getPluginFilters(){
		return "/^[^.]+$/";
	}

	
	private function initPlugin(PluginBase $plugin, PluginDescription $description, $dataFolder, $file){
		$plugin->init($this, $this->server, $description, $dataFolder, $file);
		$plugin->onLoad();
	}

	
	public function enablePlugin(Plugin $plugin){
		if($plugin instanceof PluginBase and !$plugin->isEnabled()){
			$this->server->getLogger()->info($this->server->getLanguage()->translateString("pocketmine.plugin.enable", [$plugin->getDescription()->getFullName()]));

			$plugin->setEnabled(true);

			$this->server->getPluginManager()->callEvent(new PluginEnableEvent($plugin));
		}
	}

	
	public function disablePlugin(Plugin $plugin){
		if($plugin instanceof PluginBase and $plugin->isEnabled()){
			$this->server->getLogger()->info($this->server->getLanguage()->translateString("pocketmine.plugin.disable", [$plugin->getDescription()->getFullName()]));

			$this->server->getPluginManager()->callEvent(new PluginDisableEvent($plugin));

			$plugin->setEnabled(false);
		}
	}
}
