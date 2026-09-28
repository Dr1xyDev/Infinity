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

namespace pocketmine\level\generator\vanilla\loot;

use pocketmine\level\generator\vanilla\inventory\Inventory;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\convert\GlobalItemTypeDictionary;
use pocketmine\level\generator\vanilla\network\mcpe\convert\ItemTranslator;
use pocketmine\level\generator\vanilla\network\mcpe\convert\LegacyItemIdToStringIdMap;
use pocketmine\level\generator\vanilla\network\mcpe\convert\TypeConversionException;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\Server;
use pocketmine\level\generator\vanilla\utils\Filesystem;
use pocketmine\level\generator\vanilla\utils\Random;
use function array_key_exists;
use function array_pop;
use function array_splice;
use function count;
use function intdiv;
use function is_array;
use function json_decode;
use function ltrim;
use function preg_match;
use function rtrim;
use function str_contains;
use function str_ends_with;
use function str_replace;
use function str_starts_with;
use const JSON_THROW_ON_ERROR;
use const pocketmine\BEDROCK_DATA_PATH;

/**
 * Loads loot tables in the vanilla JSON format by their path (as used in the "LootTable" tag of chests), e.g.
 * "loot_tables/chests/abandoned_mineshaft.json". Tables are first looked up in the server folder, so the complete
 * loot_tables folder of the vanilla behaviour pack can be copied there, then in the resources of the server.
 */
final class LootTableManager
{
	private static ?self $instance = null;

	/** @var (LootTable|null)[] path => table, null if it doesn't exist or is broken */
	private array $tables = [];
	/** @var (Item|null)[] */
	private array $items = [];
	/** @var string[] */
	private array $searchPaths = [];

	public static function getInstance() : self
	{
		if (self::$instance === null) {
			$dataPath = null;
			try {
				$dataPath = Server::getInstance()->getDataPath();
			} catch (\RuntimeException) {
				//not on the server thread (tests, tools)
			}
			self::$instance = new self($dataPath);
		}
		return self::$instance;
	}

	public function __construct(?string $dataPath = null)
	{
		if ($dataPath !== null) {
			$this->searchPaths[] = rtrim($dataPath, "/\\") . "/";
		}
		$this->searchPaths[] = BEDROCK_DATA_PATH;
	}

	/**
	 * Normalizes a loot table path, returns null for paths which could leave the loot table folders
	 */
	public static function normalizePath(string $path) : ?string
	{
		$path = ltrim(str_replace("\\", "/", $path), "/");
		if ($path === "" || str_contains($path, "..") || preg_match('/^[A-Za-z0-9_\/.\-]+$/', $path) !== 1) {
			return null;
		}
		if (!str_starts_with($path, "loot_tables/")) {
			$path = "loot_tables/" . $path;
		}
		if (!str_ends_with($path, ".json")) {
			$path .= ".json";
		}
		return $path;
	}

	/**
	 * Registers a table at runtime (plugins), overriding the files
	 */
	public function registerTable(string $path, LootTable $table) : void
	{
		$normalized = self::normalizePath($path);
		if ($normalized === null) {
			throw new \InvalidArgumentException("Invalid loot table path \"$path\"");
		}
		$this->tables[$normalized] = $table;
	}

	public function getTable(string $path) : ?LootTable
	{
		$normalized = self::normalizePath($path);
		if ($normalized === null) {
			return null;
		}
		if (!array_key_exists($normalized, $this->tables)) {
			$this->tables[$normalized] = $this->loadTable($normalized);
		}
		return $this->tables[$normalized];
	}

	private function loadTable(string $path) : ?LootTable
	{
		foreach ($this->searchPaths as $base) {
			if (!Filesystem::resourceExists($base . $path)) {
				continue;
			}
			try {
				$data = json_decode(Filesystem::resourceGetContents($base . $path), true, 64, JSON_THROW_ON_ERROR);
				if (!is_array($data)) {
					throw new LootTableException("Root must be an object");
				}
				return LootTable::fromArray($data);
			} catch (\JsonException | LootTableException | \RuntimeException $e) {
				\GlobalLogger::get()->warning("Broken loot table $base$path: " . $e->getMessage());
				return null;
			}
		}
		\GlobalLogger::get()->debug("Loot table $path not found");
		return null;
	}

	/**
	 * Resolves a vanilla item name (minecraft:iron_ingot, minecraft:appleEnchanted...) to a server item
	 */
	public function resolveItem(string $name) : ?Item
	{
		if (!array_key_exists($name, $this->items)) {
			$this->items[$name] = $this->lookupItem($name);
		}
		$item = $this->items[$name];
		return $item === null ? null : clone $item;
	}

	private function lookupItem(string $name) : ?Item
	{
		$stringId = str_contains($name, ":") ? $name : "minecraft:" . $name;
		$protocol = ProtocolInfo::CURRENT_PROTOCOL;
		try {
			$legacyId = LegacyItemIdToStringIdMap::getInstance($protocol)->stringToLegacy($stringId);
			if ($legacyId !== null) {
				return ItemFactory::get($legacyId);
			}
			$networkId = GlobalItemTypeDictionary::getInstance($protocol)->getDictionary()->fromStringId($stringId);
			[$id, $meta] = ItemTranslator::getInstance($protocol)->fromNetworkId($networkId, 0);
			return ItemFactory::get($id, $meta);
		} catch (\InvalidArgumentException | TypeConversionException) {
			//not a network item name
		}
		try {
			return ItemFactory::fromStringSingle($name);
		} catch (\InvalidArgumentException) {
			\GlobalLogger::get()->debug("Unknown item \"$name\" in a loot table");
			return null;
		}
	}

	/**
	 * @return Item[]
	 */
	public function generate(string $path, LootContext $context) : array
	{
		return $this->getTable($path)?->generate($context) ?? [];
	}

	/**
	 * Fills an inventory like vanilla does: items go into random free slots and stacks are split
	 * over the free slots. Items which don't fit are dropped at $dropPos if given.
	 */
	public function fillInventory(string $path, Inventory $inventory, LootContext $context, ?Level $level = null, ?Vector3 $dropPos = null) : void
	{
		$items = $this->generate($path, $context);
		if (count($items) === 0) {
			return;
		}
		$random = $context->getRandom();

		$freeSlots = [];
		for ($slot = 0, $size = $inventory->getSize(); $slot < $size; ++$slot) {
			if ($inventory->getItem($slot)->isNull()) {
				$freeSlots[] = $slot;
			}
		}
		self::shuffle($freeSlots, $random);

		//split stacks while there are more free slots than items
		$splittable = [];
		$rest = [];
		foreach ($items as $item) {
			if ($item->getCount() > 1) {
				$splittable[] = $item;
			} else {
				$rest[] = $item;
			}
		}
		while (count($splittable) > 0 && count($rest) + count($splittable) < count($freeSlots)) {
			$index = $random->nextBoundedInt(count($splittable));
			$item = array_splice($splittable, $index, 1)[0];
			$half = 1 + $random->nextBoundedInt(intdiv($item->getCount(), 2));
			$split = (clone $item)->setCount($half);
			$item->setCount($item->getCount() - $half);
			foreach ([$item, $split] as $part) {
				if ($part->getCount() > 1 && $random->nextBoolean()) {
					$splittable[] = $part;
				} else {
					$rest[] = $part;
				}
			}
		}
		foreach ($splittable as $item) {
			$rest[] = $item;
		}
		self::shuffle($rest, $random);

		foreach ($rest as $item) {
			$slot = array_pop($freeSlots);
			if ($slot === null) {
				if ($level !== null && $dropPos !== null) {
					$level->dropItem($dropPos, $item);
				}
				continue;
			}
			$inventory->setItem($slot, $item);
		}
	}

	/**
	 * @param mixed[] $list
	 */
	private static function shuffle(array &$list, Random $random) : void
	{
		for ($i = count($list) - 1; $i > 0; --$i) {
			$j = $random->nextBoundedInt($i + 1);
			[$list[$i], $list[$j]] = [$list[$j], $list[$i]];
		}
	}
}
