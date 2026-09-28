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
use function count;
use function floor;
use function is_array;
use function is_numeric;
use function max;
use function min;

final class LootPool
{
	/**
	 * @param mixed        $rolls      int, float or {min, max}
	 * @param mixed[][]    $conditions
	 * @param LootEntry[]  $entries
	 * @param mixed[]|null $tiers      bedrock tiers: {initial_range, bonus_rolls, bonus_chance}
	 */
	public function __construct(
		private mixed $rolls,
		private float $bonusRolls,
		private array $conditions,
		private array $entries,
		private ?array $tiers = null
	) {
	}

	/**
	 * @param mixed[] $data
	 *
	 * @throws LootTableException
	 */
	public static function fromArray(array $data) : self
	{
		$entries = [];
		foreach ($data["entries"] ?? [] as $entry) {
			if (!is_array($entry)) {
				throw new LootTableException("Entry must be an object");
			}
			$entries[] = LootEntry::fromArray($entry);
		}
		$conditions = $data["conditions"] ?? [];
		if (!is_array($conditions)) {
			throw new LootTableException("Conditions must be a list");
		}
		$tiers = isset($data["tiers"]) && is_array($data["tiers"]) ? $data["tiers"] : null;
		$bonusRolls = $data["bonus_rolls"] ?? 0;
		return new self($data["rolls"] ?? 1, is_numeric($bonusRolls) ? (float) $bonusRolls : 0.0, $conditions, $entries, $tiers);
	}

	/**
	 * @return LootEntry[]
	 */
	public function getEntries() : array
	{
		return $this->entries;
	}

	/**
	 * @param Item[] $result
	 */
	public function generate(LootContext $context, array &$result, int $depth) : void
	{
		if (count($this->entries) === 0 || !LootConditions::testAll($this->conditions, $context)) {
			return;
		}

		if ($this->tiers !== null) {
			$index = $context->randomInt(0, max(1, (int) ($this->tiers["initial_range"] ?? 1)) - 1);
			$bonusChance = (float) ($this->tiers["bonus_chance"] ?? 0);
			for ($i = 0, $bonus = (int) ($this->tiers["bonus_rolls"] ?? 0); $i < $bonus; ++$i) {
				if ($context->getRandom()->nextFloat() < $bonusChance) {
					++$index;
				}
			}
			$this->entries[min($index, count($this->entries) - 1)]->generate($context, $result, $depth);
			return;
		}

		$rolls = LootRange::toInt($this->rolls, $context, 1) + (int) floor($this->bonusRolls * $context->getLuck());
		for ($roll = 0; $roll < $rolls; ++$roll) {
			$candidates = [];
			$totalWeight = 0;
			foreach ($this->entries as $entry) {
				if (!$entry->testConditions($context)) {
					continue;
				}
				$weight = $entry->getEffectiveWeight($context->getLuck());
				if ($weight > 0) {
					$candidates[] = [$entry, $weight];
					$totalWeight += $weight;
				}
			}
			if ($totalWeight <= 0) {
				return;
			}
			$pick = $context->getRandom()->nextBoundedInt($totalWeight);
			foreach ($candidates as [$entry, $weight]) {
				$pick -= $weight;
				if ($pick < 0) {
					$entry->generate($context, $result, $depth);
					break;
				}
			}
		}
	}
}
