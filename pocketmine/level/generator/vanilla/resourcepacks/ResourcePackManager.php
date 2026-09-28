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

namespace pocketmine\level\generator\vanilla\resourcepacks;

use InvalidArgumentException;
use Logger;
use LogicException;
use pocketmine\level\generator\vanilla\utils\Config;

use function array_keys;
use function copy;
use function count;
use function file_exists;
use function file_get_contents;
use function gettype;
use function is_array;
use function is_dir;
use function is_float;
use function is_int;
use function is_string;
use function mkdir;
use function strtolower;

use const DIRECTORY_SEPARATOR;

class ResourcePackManager
{
	private string $path;
	private bool $serverForceResources = false;

	/** @var ResourcePack[] */
	private array $resourcePacks = [];

	/** @var ResourcePack[] */
	private array $uuidList = [];

	/**
	 * @var string[]
	 * @phpstan-var array<string, string>
	 */
	private array $encryptionKeys = [];

	/**
	 * @param string $path Path to resource-packs directory.
	 */
	public function __construct(string $path, Logger $logger)
	{
		$this->path = $path;

		if (!file_exists($this->path)) {
			$logger->debug("Resource packs path $path does not exist, creating directory");
			mkdir($this->path);
		} elseif (!is_dir($this->path)) {
			throw new InvalidArgumentException("Resource packs path $path exists and is not a directory");
		}

		if (!file_exists($this->path . "resource_packs.yml")) {
			copy(\pocketmine\RESOURCE_PATH . "resource_packs.yml", $this->path . "resource_packs.yml");
		}

		$resourcePacksConfig = new Config($this->path . "resource_packs.yml", Config::YAML, []);

		$this->serverForceResources = (bool) $resourcePacksConfig->get("force_resources", false);

		$logger->info("Loading resource packs...");

		$resourceStack = $resourcePacksConfig->get("resource_stack", []);
		if (!is_array($resourceStack)) {
			throw new InvalidArgumentException("\"resource_stack\" key should contain a list of pack names");
		}

		foreach ($resourceStack as $pos => $pack) {
			if (!is_string($pack) && !is_int($pack) && !is_float($pack)) {
				$logger->critical("Found invalid entry in resource pack list at offset $pos of type " . gettype($pack));
				continue;
			}

			$pack = (string) $pack;
			try {
				$packPath = $this->path . DIRECTORY_SEPARATOR . $pack;
				if (!file_exists($packPath)) {
					throw new ResourcePackException("File or directory not found");
				}
				if (is_dir($packPath)) {
					throw new ResourcePackException("Directory resource packs are unsupported");
				}

				$newPack = null;
				//Detect the type of resource pack.
				$info = new \SplFileInfo($packPath);
				switch ($info->getExtension()) {
					case "zip":
					case "mcpack":
						$newPack = new ZippedResourcePack($packPath);
						break;
				}

				if ($newPack instanceof ResourcePack) {
					$this->resourcePacks[] = $newPack;
					$this->uuidList[strtolower($newPack->getPackId())] = $newPack;

					$keyPath = $this->path . DIRECTORY_SEPARATOR . $pack . ".key";
					if (file_exists($keyPath)) {
						try {
							$key = file_get_contents($keyPath);
							if ($key === false) {
								throw new LogicException("Block must not return false when no error occurred. Use trap() if the block may return false.");
							}

							$this->encryptionKeys[strtolower($newPack->getPackId())] = $key;
							$newPack->setEncryptionKey($key);
						} catch (\ErrorException $e) {
							throw new ResourcePackException("Could not read encryption key file: " . $e->getMessage(), 0, $e);
						}
					}
				} else {
					throw new ResourcePackException("Format not recognized");
				}
			} catch (ResourcePackException $e) {
				$logger->critical("Could not load resource pack \"$pack\": " . $e->getMessage());
			}
		}
		$logger->debug("Successfully loaded " . count($this->resourcePacks) . " resource packs");
	}

	/**
	 * Returns the directory which resource packs are loaded from.
	 */
	public function getPath() : string
	{
		return $this->path;
	}

	/**
	 * Returns whether players must accept resource packs in order to join.
	 */
	public function resourcePacksRequired() : bool
	{
		return $this->serverForceResources;
	}

	/**
	 * Sets whether players must accept resource packs in order to join.
	 */
	public function setResourcePacksRequired(bool $value) : void
	{
		$this->serverForceResources = $value;
	}

	/**
	 * Returns an array of resource packs in use, sorted in order of priority.
	 * @return ResourcePack[]
	 */
	public function getResourceStack() : array
	{
		return $this->resourcePacks;
	}

	/**
	 * Returns the resource pack matching the specified UUID string, or null if the ID was not recognized.
	 *
	 * @return ResourcePack|null
	 */
	public function getPackById(string $id)
	{
		return $this->uuidList[strtolower($id)] ?? null;
	}

	/**
	 * Returns an array of pack IDs for packs currently in use.
	 * @return string[]
	 */
	public function getPackIdList() : array
	{
		return array_keys($this->uuidList);
	}

	/**
	 * Returns the key with which the pack was encrypted, or null if the pack has no key.
	 */
	public function getPackEncryptionKey(string $id) : ?string
	{
		return $this->encryptionKeys[strtolower($id)] ?? null;
	}
}
