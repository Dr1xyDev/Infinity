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

namespace pocketmine\level\generator\vanilla\level\format\io;

use pocketmine\level\generator\vanilla\level\format\io\leveldb\LevelDB;
use pocketmine\level\generator\vanilla\level\format\io\region\Anvil;
use pocketmine\level\generator\vanilla\level\format\io\region\McRegion;
use pocketmine\level\generator\vanilla\level\format\io\region\PMAnvil;
use pocketmine\level\generator\vanilla\utils\Utils;
use function strtolower;
use function trim;

abstract class LevelProviderManager
{
	/**
	 * @var LevelProviderManagerEntry[]
	 * @phpstan-var array<string, LevelProviderManagerEntry>
	 */
	protected static $providers = [];

	private static WritableLevelProviderManagerEntry $default;

	public static function init() : void
	{
		$leveldb = new WritableLevelProviderManagerEntry(\Closure::fromCallable([LevelDB::class, 'isValid']), fn (string $path) => new LevelDB($path), \Closure::fromCallable([LevelDB::class, 'generate']));
		self::$default = $leveldb;
		self::addProvider($leveldb, "leveldb");

		self::addProvider(new ReadOnlyLevelProviderManagerEntry(\Closure::fromCallable([Anvil::class, 'isValid']), fn (string $path) => new Anvil($path)), "anvil");
		self::addProvider(new ReadOnlyLevelProviderManagerEntry(\Closure::fromCallable([McRegion::class, 'isValid']), fn (string $path) => new McRegion($path)), "mcregion");
		self::addProvider(new ReadOnlyLevelProviderManagerEntry(\Closure::fromCallable([PMAnvil::class, 'isValid']), fn (string $path) => new PMAnvil($path)), "pmanvil");
	}

	/**
	 * Returns the default format used to generate new worlds.
	 */
	public static function getDefault() : WritableLevelProviderManagerEntry
	{
		return self::$default;
	}

	public static function setDefault(WritableLevelProviderManagerEntry $class) : void
	{
		self::$default = $class;
	}

	public static function addProvider(LevelProviderManagerEntry $providerEntry, string $name, bool $overwrite = false) : void
	{
		$name = strtolower($name);
		if (!$overwrite && isset(self::$providers[$name])) {
			throw new \InvalidArgumentException("Alias \"$name\" is already assigned");
		}

		self::$providers[$name] = $providerEntry;
	}

	/**
	 * Returns a WorldProvider class for this path, or null
	 *
	 * @return LevelProviderManagerEntry[]
	 * @phpstan-return array<string, LevelProviderManagerEntry>
	 */
	public static function getMatchingProviders(string $path) : array
	{
		$result = [];
		foreach (Utils::stringifyKeys(self::$providers) as $alias => $providerEntry) {
			if ($providerEntry->isValid($path)) {
				$result[$alias] = $providerEntry;
			}
		}
		return $result;
	}

	/**
	 * @return LevelProviderManagerEntry[]
	 * @phpstan-return array<string, LevelProviderManagerEntry>
	 */
	public static function getAvailableProviders() : array
	{
		return self::$providers;
	}

	/**
	 * Returns a WorldProvider by name, or null if not found
	 */
	public static function getProviderByName(string $name) : ?LevelProviderManagerEntry
	{
		return self::$providers[trim(strtolower($name))] ?? null;
	}
}
