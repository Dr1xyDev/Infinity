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

use pocketmine\command\CommandExecutor;

interface Plugin extends CommandExecutor{

	
	public function onLoad();

	
	public function onEnable();

	public function isEnabled();

	
	public function onDisable();

	public function isDisabled();

	
	public function getDataFolder();

	
	public function getDescription();

	
	public function getResource($filename);

	
	public function saveResource($filename, $replace = false);

	
	public function getResources();

	
	public function getConfig();

	public function saveConfig();

	public function saveDefaultConfig();

	public function reloadConfig();

	
	public function getServer();

	public function getName();

	
	public function getLogger();

	
	public function getPluginLoader();

}