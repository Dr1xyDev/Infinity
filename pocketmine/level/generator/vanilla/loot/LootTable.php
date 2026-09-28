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
use function is_array;

/**
 * Loot table in the vanilla (behaviour pack) JSON format: a list of pools, every pool rolls its weighted entries.
 */
final class LootTable
{
	/** Nested loot_table entries deeper than this are ignored (protection against cycles) */
	public const MAX_DEPTH = 8;

	/**
	 * @param LootPool[] $pools
	 */
	public function __construct(private array $pools)
	{
	}

	/**
	 * @param mixed[] $data decoded JSON
	 *
	 * @throws LootTableException
	 */
	public static function fromArray(array $data) : self
	{
		$pools = [];
		foreach ($data["pools"] ?? [] as $pool) {
			if (!is_array($pool)) {
				throw new LootTableException("Pool must be an object");
			}
			$pools[] = LootPool::fromArray($pool);
		}
		return new self($pools);
	}

	/**
	 * @return LootPool[]
	 */
	public function getPools() : array
	{
		return $this->pools;
	}

	/**
	 * @return Item[]
	 */
	public function generate(LootContext $context, int $depth = 0) : array
	{
		$result = [];
		if ($depth > self::MAX_DEPTH) {
			return $result;
		}
		foreach ($this->pools as $pool) {
			$pool->generate($context, $result, $depth);
		}
		return $result;
	}
}
