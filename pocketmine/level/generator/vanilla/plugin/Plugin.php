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

/**
 * Plugin related classes
 */

namespace pocketmine\level\generator\vanilla\plugin;

use pocketmine\level\generator\vanilla\command\CommandExecutor;
use pocketmine\level\generator\vanilla\scheduler\TaskScheduler;
use pocketmine\Server;
use pocketmine\level\generator\vanilla\utils\Config;
use SplFileInfo;

/**
 * It is recommended to use PluginBase for the actual plugin
 */
interface Plugin extends CommandExecutor
{
	public function __construct(PluginLoader $loader, Server $server, PluginDescription $description, string $dataFolder, string $file);

	/**
	 * Called when the plugin is loaded, before calling onEnable()
	 *
	 * @return void
	 */
	public function onLoad();

	/**
	 * Called when the plugin is enabled
	 *
	 * @return void
	 */
	public function onEnable();

	public function isEnabled() : bool;

	/**
	 * Called by the plugin manager when the plugin is enabled or disabled to inform the plugin of its enabled state.
	 *
	 * @internal This is intended for core use only and should not be used by plugins
	 * @see PluginManager::enablePlugin()
	 * @see PluginManager::disablePlugin()
	 */
	public function setEnabled(bool $enabled = true) : void;

	/**
	 * Called when the plugin is disabled
	 * Use this to free open things and finish actions
	 *
	 * @return void
	 */
	public function onDisable();

	public function isDisabled() : bool;

	/**
	 * Gets the plugin's data folder to save files and configuration.
	 * This directory name has a trailing slash.
	 */
	public function getDataFolder() : string;

	public function getDescription() : PluginDescription;

	/**
	 * Gets an embedded resource in the plugin file.
	 *
	 * @return null|resource Resource data, or null
	 */
	public function getResource(string $filename);

	/**
	 * Saves an embedded resource to its relative location in the data folder
	 */
	public function saveResource(string $filename, bool $replace = false) : bool;

	/**
	 * Returns all the resources packaged with the plugin
	 *
	 * @return SplFileInfo[]
	 */
	public function getResources() : array;

	public function getConfig() : Config;

	/**
	 * @return void
	 */
	public function saveConfig();

	public function saveDefaultConfig() : bool;

	/**
	 * @return void
	 */
	public function reloadConfig();

	public function getServer() : Server;

	public function getName() : string;

	public function getLogger() : PluginLogger;

	/**
	 * @return PluginLoader
	 */
	public function getPluginLoader();

	public function getScheduler() : TaskScheduler;

}
