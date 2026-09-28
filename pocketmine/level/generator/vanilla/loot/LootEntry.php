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

use pocketmine\level\generator\vanilla\item\Item;
use function floor;
use function is_array;
use function is_numeric;
use function is_string;
use function max;
use function min;
use function str_replace;

final class LootEntry
{
	public const TYPE_ITEM = "item";
	public const TYPE_LOOT_TABLE = "loot_table";
	public const TYPE_EMPTY = "empty";

	/**
	 * @param mixed[][] $conditions
	 * @param mixed[][] $functions
	 * @param LootPool[] $pools inline pools of a loot_table entry
	 */
	public function __construct(
		private string $type,
		private string $name,
		private int $weight,
		private int $quality,
		private array $conditions,
		private array $functions,
		private array $pools = []
	) {
	}

	/**
	 * @param mixed[] $data
	 *
	 * @throws LootTableException
	 */
	public static function fromArray(array $data) : self
	{
		$type = is_string($data["type"] ?? null) ? str_replace("minecraft:", "", $data["type"]) : self::TYPE_ITEM;
		if ($type !== self::TYPE_ITEM && $type !== self::TYPE_LOOT_TABLE && $type !== self::TYPE_EMPTY) {
			throw new LootTableException("Unknown entry type \"$type\"");
		}
		$name = $data["name"] ?? "";
		if (!is_string($name) || ($name === "" && $type === self::TYPE_ITEM)) {
			throw new LootTableException("Entry of type $type needs a name");
		}
		$conditions = is_array($data["conditions"] ?? null) ? $data["conditions"] : [];
		$functions = is_array($data["functions"] ?? null) ? $data["functions"] : [];

		$pools = [];
		if ($type === self::TYPE_LOOT_TABLE && is_array($data["pools"] ?? null)) {
			foreach ($data["pools"] as $pool) {
				if (is_array($pool)) {
					$pools[] = LootPool::fromArray($pool);
				}
			}
		}

		return new self(
			$type,
			$name,
			is_numeric($data["weight"] ?? null) ? (int) $data["weight"] : 1,
			is_numeric($data["quality"] ?? null) ? (int) $data["quality"] : 0,
			$conditions,
			$functions,
			$pools
		);
	}

	public function getType() : string
	{
		return $this->type;
	}

	public function getName() : string
	{
		return $this->name;
	}

	public function getEffectiveWeight(float $luck) : int
	{
		return max(0, (int) floor($this->weight + $this->quality * $luck));
	}

	public function testConditions(LootContext $context) : bool
	{
		return LootConditions::testAll($this->conditions, $context);
	}

	/**
	 * @param Item[] $result
	 */
	public function generate(LootContext $context, array &$result, int $depth) : void
	{
		switch ($this->type) {
			case self::TYPE_ITEM:
				$item = LootTableManager::getInstance()->resolveItem($this->name);
				if ($item === null) {
					return;
				}
				$item = LootFunctions::applyAll($this->functions, $item, $context);
				self::addStacks($item, $result);
				return;
			case self::TYPE_LOOT_TABLE:
				$items = [];
				if ($this->name !== "") {
					$table = LootTableManager::getInstance()->getTable($this->name);
					if ($table !== null) {
						$items = $table->generate($context, $depth + 1);
					}
				} else {
					foreach ($this->pools as $pool) {
						$pool->generate($context, $items, $depth + 1);
					}
				}
				foreach ($items as $item) {
					self::addStacks(LootFunctions::applyAll($this->functions, $item, $context), $result);
				}
				return;
			default:
				return;
		}
	}

	/**
	 * Adds the item, split into stacks no bigger than its max stack size
	 *
	 * @param Item[] $result
	 */
	private static function addStacks(Item $item, array &$result) : void
	{
		$count = $item->getCount();
		if ($item->isNull() || $count <= 0) {
			return;
		}
		$maxStack = max(1, $item->getMaxStackSize());
		while ($count > 0) {
			$stack = clone $item;
			$stack->setCount(min($count, $maxStack));
			$count -= $stack->getCount();
			$result[] = $stack;
		}
	}
}
