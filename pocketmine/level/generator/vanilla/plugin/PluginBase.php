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

use pocketmine\level\generator\vanilla\command\Command;
use pocketmine\level\generator\vanilla\command\CommandSender;
use pocketmine\level\generator\vanilla\command\PluginIdentifiableCommand;
use pocketmine\level\generator\vanilla\event\Listener;
use pocketmine\level\generator\vanilla\scheduler\TaskScheduler;
use pocketmine\Server;
use pocketmine\level\generator\vanilla\utils\AssumptionFailedError;
use pocketmine\level\generator\vanilla\utils\Config;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

use function dirname;
use function fclose;
use function file_exists;
use function fopen;
use function is_dir;
use function mkdir;
use function rtrim;
use function str_replace;
use function stream_copy_to_stream;
use function strlen;
use function strpos;
use function strtolower;
use function substr;
use function trim;

use const DIRECTORY_SEPARATOR;

abstract class PluginBase implements Plugin
{
	private PluginLoader $loader;
	private Server $server;
	private bool $isEnabled = false;
	private PluginDescription $description;
	private string $dataFolder;
	private ?Config $config = null;
	private string $configFile;
	private string $file;
	private PluginLogger $logger;
	private TaskScheduler $scheduler;

	public function __construct(PluginLoader $loader, Server $server, PluginDescription $description, string $dataFolder, string $file)
	{
		$this->loader = $loader;
		$this->server = $server;
		$this->description = $description;
		$this->dataFolder = rtrim($dataFolder, "/" . DIRECTORY_SEPARATOR) . "/";
		$this->file = rtrim($file, "/" . DIRECTORY_SEPARATOR) . "/";
		$this->configFile = $this->dataFolder . "config.yml";
		$prefix = $this->description->getPrefix();
		$this->logger = new PluginLogger($server->getLogger(), $prefix !== "" ? $prefix : $this->getName());
		$this->scheduler = new TaskScheduler($this->getFullName());
	}

	public function onLoad()
	{

	}

	public function onEnable()
	{

	}

	public function onDisable()
	{

	}

	final public function isEnabled() : bool
	{
		return $this->isEnabled;
	}

	/**
	 * Called by the plugin manager when the plugin is enabled or disabled to inform the plugin of its enabled state.
	 *
	 * @internal This is intended for core use only and should not be used by plugins
	 * @see PluginManager::enablePlugin()
	 * @see PluginManager::disablePlugin()
	 */
	final public function setEnabled(bool $enabled = true) : void
	{
		if ($this->isEnabled !== $enabled) {
			$this->isEnabled = $enabled;
			if ($this->isEnabled) {
				$this->onEnable();
			} else {
				$this->onDisable();
			}
		}
	}

	final public function isDisabled() : bool
	{
		return !$this->isEnabled;
	}

	final public function getDataFolder() : string
	{
		return $this->dataFolder;
	}

	final public function getDescription() : PluginDescription
	{
		return $this->description;
	}

	public function getLogger() : PluginLogger
	{
		return $this->logger;
	}

	/**
	 * @return Command|PluginIdentifiableCommand|null
	 */
	public function getCommand(string $name)
	{
		$command = $this->getServer()->getPluginCommand($name);
		if ($command === null || $command->getPlugin() !== $this) {
			$command = $this->getServer()->getPluginCommand(strtolower($this->description->getName()) . ":" . $name);
		}

		if ($command instanceof PluginIdentifiableCommand && $command->getPlugin() === $this) {
			return $command;
		} else {
			return null;
		}
	}

	/**
	 * @param string[] $args
	 */
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool
	{
		return false;
	}

	protected function isPhar() : bool
	{
		return strpos($this->file, "phar://") === 0;
	}

	/**
	 * Gets an embedded resource on the plugin file.
	 * WARNING: You must close the resource given using fclose()
	 *
	 * @return null|resource Resource data, or null
	 */
	public function getResource(string $filename)
	{
		$filename = rtrim(str_replace(DIRECTORY_SEPARATOR, "/", $filename), "/");
		if (file_exists($this->file . "resources/" . $filename)) {
			$resource = fopen($this->file . "resources/" . $filename, "rb");
			if ($resource === false) {
				throw new AssumptionFailedError("fopen() should not fail on a file which exists");
			}
			return $resource;
		}

		return null;
	}

	public function saveResource(string $filename, bool $replace = false) : bool
	{
		if (trim($filename) === "") {
			return false;
		}

		if (($resource = $this->getResource($filename)) === null) {
			return false;
		}

		$out = $this->dataFolder . $filename;
		if (!file_exists(dirname($out))) {
			mkdir(dirname($out), 0755, true);
		}

		if (file_exists($out) && !$replace) {
			return false;
		}

		$fp = fopen($out, "wb");
		if ($fp === false) {
			throw new AssumptionFailedError("fopen() should not fail with wb flags");
		}

		$ret = stream_copy_to_stream($resource, $fp) > 0;
		fclose($fp);
		fclose($resource);
		return $ret;
	}

	/**
	 * Returns all the resources packaged with the plugin in the form ["path/in/resources" => SplFileInfo]
	 *
	 * @return SplFileInfo[]
	 */
	public function getResources() : array
	{
		$resources = [];
		if (is_dir($this->file . "resources/")) {
			foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->file . "resources/")) as $resource) {
				if ($resource->isFile()) {
					$path = str_replace(DIRECTORY_SEPARATOR, "/", substr((string) $resource, strlen($this->file . "resources/")));
					$resources[$path] = $resource;
				}
			}
		}

		return $resources;
	}

	public function getConfig() : Config
	{
		if ($this->config === null) {
			$this->reloadConfig();
		}

		return $this->config;
	}

	public function saveConfig()
	{
		if (!$this->getConfig()->save()) {
			$this->getLogger()->critical("Could not save config to " . $this->configFile);
		}
	}

	public function saveDefaultConfig() : bool
	{
		if (!file_exists($this->configFile)) {
			return $this->saveResource("config.yml", false);
		}
		return false;
	}

	public function reloadConfig()
	{
		$this->saveDefaultConfig();
		$this->config = new Config($this->configFile);
	}

	final public function getServer() : Server
	{
		return $this->server;
	}

	final public function getName() : string
	{
		return $this->description->getName();
	}

	final public function getFullName() : string
	{
		return $this->description->getFullName();
	}

	public function registerEvents(Listener $listener) : void
	{
		$this->server->getPluginManager()->registerEvents($listener, $this);
	}

	protected function getFile() : string
	{
		return $this->file;
	}

	/**
	 * @return PluginLoader
	 */
	public function getPluginLoader()
	{
		return $this->loader;
	}

	public function getScheduler() : TaskScheduler
	{
		return $this->scheduler;
	}
}
