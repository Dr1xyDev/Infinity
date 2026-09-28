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

namespace pocketmine\level\generator\vanilla\plugin;

use pocketmine\level\generator\vanilla\thread\ThreadSafeClassLoader;

use function file_exists;
use function file_get_contents;
use function is_dir;

class FolderPluginLoader implements PluginLoader
{
	public function __construct(
		private ThreadSafeClassLoader $loader
	) {
	}

	public function canLoadPlugin(string $path) : bool
	{
		return is_dir($path) && file_exists($path . "/plugin.yml") && file_exists($path . "/src/");
	}

	/**
	 * Loads the plugin contained in $file
	 */
	public function loadPlugin(string $file) : void
	{
		$this->loader->addPath("", "$file/src");
	}

	/**
	 * Gets the PluginDescription from the file
	 */
	public function getPluginDescription(string $file) : ?PluginDescription
	{
		if (is_dir($file) && file_exists($file . "/plugin.yml")) {
			$yaml = @file_get_contents($file . "/plugin.yml");
			if ($yaml != "") {
				return new PluginDescription($yaml);
			}
		}

		return null;
	}

	public function getAccessProtocol() : string
	{
		return "";
	}
}
