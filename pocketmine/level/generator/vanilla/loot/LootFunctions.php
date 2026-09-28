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

use pocketmine\level\generator\vanilla\item\Book;
use pocketmine\level\generator\vanilla\item\Durable;
use pocketmine\level\generator\vanilla\item\enchantment\Enchantment;
use pocketmine\level\generator\vanilla\item\enchantment\EnchantmentHelper;
use pocketmine\level\generator\vanilla\item\enchantment\EnchantmentInstance;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\item\ItemIds;
use function array_map;
use function count;
use function floor;
use function is_array;
use function is_string;
use function max;
use function min;
use function str_replace;

/**
 * Vanilla loot functions. Functions this server can't apply are skipped.
 */
final class LootFunctions
{
	private function __construct()
	{
		//NOOP
	}

	/**
	 * @param mixed[] $functions
	 */
	public static function applyAll(array $functions, Item $item, LootContext $context) : Item
	{
		$item = clone $item;
		foreach ($functions as $function) {
			if (!is_array($function) || !LootConditions::testAll(is_array($function["conditions"] ?? null) ? $function["conditions"] : [], $context)) {
				continue;
			}
			$item = self::apply($function, $item, $context);
		}
		return $item;
	}

	/**
	 * @param mixed[] $function
	 */
	public static function apply(array $function, Item $item, LootContext $context) : Item
	{
		$name = is_string($function["function"] ?? null) ? str_replace("minecraft:", "", $function["function"]) : "";
		switch ($name) {
			case "set_count":
				return $item->setCount(max(0, LootRange::toInt($function["count"] ?? 1, $context, 1)));
			case "looting_enchant":
				return $item->setCount($item->getCount() + $context->getLootingLevel() * max(0, LootRange::toInt($function["count"] ?? 0, $context, 0)));
			case "set_data":
			case "random_aux_value":
				return self::withMeta($item, LootRange::toInt($function["data"] ?? $function["values"] ?? 0, $context, 0));
			case "set_damage":
				if ($item instanceof Durable) {
					$remaining = min(1.0, max(0.0, LootRange::toFloat($function["damage"] ?? 1, $context, 1.0)));
					$item->setDamage((int) floor($item->getMaxDurability() * (1 - $remaining)));
				}
				return $item;
			case "enchant_randomly":
				return self::enchantRandomly($item, $context, (bool) ($function["treasure"] ?? false));
			case "enchant_with_levels":
				return EnchantmentHelper::addRandomEnchantment($context->getRandom(), $item, max(1, LootRange::toInt($function["levels"] ?? 1, $context, 1)));
			case "enchant_random_gear":
				return $context->getRandom()->nextFloat() < (float) ($function["chance"] ?? 0.25) ? EnchantmentHelper::addRandomEnchantment($context->getRandom(), $item, 5 + $context->randomInt(0, 18)) : $item;
			case "specific_enchants":
				return self::specificEnchants($item, is_array($function["enchants"] ?? null) ? $function["enchants"] : [], $context);
			case "set_name":
				return is_string($function["name"] ?? null) ? $item->setCustomName($function["name"]) : $item;
			case "set_lore":
				return is_array($function["lore"] ?? null) ? $item->setLore(array_map("strval", $function["lore"])) : $item;
			default:
				//exploration_map, furnace_smelt, set_book_contents, random_dye, set_banner_details... are not supported
				return $item;
		}
	}

	private static function withMeta(Item $item, int $meta) : Item
	{
		if ($item instanceof Durable) {
			return $item->setDamage(max(0, $meta));
		}
		$new = ItemFactory::get($item->getId(), max(0, $meta), $item->getCount());
		if ($item->hasNamedTag()) {
			$new->setNamedTag($item->getNamedTag());
		}
		return $new;
	}

	private static function enchantRandomly(Item $item, LootContext $context, bool $treasure) : Item
	{
		$isBook = $item instanceof Book || $item->getId() === ItemIds::BOOK;
		$candidates = [];
		foreach (Enchantment::getAllEnchantments() as $enchantment) {
			if ($enchantment === null || $enchantment->getId() === Enchantment::INVALID) {
				continue;
			}
			if (!$treasure && ($enchantment->getId() === Enchantment::MENDING || $enchantment->getId() === Enchantment::FROST_WALKER || $enchantment->getId() === Enchantment::BINDING || $enchantment->getId() === Enchantment::VANISHING || $enchantment->getId() === Enchantment::SOUL_SPEED)) {
				continue;
			}
			if ($isBook || $enchantment->canApply($item)) {
				$candidates[] = $enchantment;
			}
		}
		if (count($candidates) === 0) {
			return $item;
		}
		$enchantment = $candidates[$context->randomInt(0, count($candidates) - 1)];
		if ($isBook) {
			$item = ItemFactory::get(ItemIds::ENCHANTED_BOOK, 0, $item->getCount());
		}
		$item->addEnchantment(new EnchantmentInstance($enchantment, $context->randomInt(1, max(1, $enchantment->getMaxLevel()))));
		return $item;
	}

	/**
	 * @param mixed[] $enchants
	 */
	private static function specificEnchants(Item $item, array $enchants, LootContext $context) : Item
	{
		foreach ($enchants as $entry) {
			$id = is_array($entry) ? ($entry["id"] ?? null) : $entry;
			if (!is_string($id)) {
				continue;
			}
			$enchantment = Enchantment::getEnchantmentByName(str_replace("minecraft:", "", $id));
			if ($enchantment === null || $enchantment->getId() === Enchantment::INVALID) {
				continue;
			}
			$level = is_array($entry) ? LootRange::toInt($entry["level"] ?? 1, $context, 1) : 1;
			if ($item->getId() === ItemIds::BOOK) {
				$item = ItemFactory::get(ItemIds::ENCHANTED_BOOK, 0, $item->getCount());
			}
			$item->addEnchantment(new EnchantmentInstance($enchantment, max(1, min($level, $enchantment->getMaxLevel()))));
		}
		return $item;
	}
}
