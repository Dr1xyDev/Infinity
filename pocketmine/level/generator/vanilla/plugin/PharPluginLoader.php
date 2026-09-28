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

use Phar;
use pocketmine\level\generator\vanilla\thread\ThreadSafeClassLoader;

use function is_file;
use function strlen;
use function substr;

/**
 * Handles different types of plugins
 */
class PharPluginLoader implements PluginLoader
{
	public function __construct(
		private ThreadSafeClassLoader $loader
	) {
	}

	public function canLoadPlugin(string $path) : bool
	{
		$ext = ".phar";
		return is_file($path) && substr($path, -strlen($ext)) === $ext;
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
		$phar = new Phar($file);
		if (isset($phar["plugin.yml"])) {
			return new PluginDescription($phar["plugin.yml"]->getContent());
		}

		return null;
	}

	public function getAccessProtocol() : string
	{
		return "phar://";
	}
}
