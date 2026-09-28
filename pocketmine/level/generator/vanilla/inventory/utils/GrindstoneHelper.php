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

namespace pocketmine\level\generator\vanilla\inventory\utils;

use pocketmine\level\generator\vanilla\inventory\GrindstoneResult;
use pocketmine\level\generator\vanilla\item\Durable;
use pocketmine\level\generator\vanilla\item\EnchantedBook;
use pocketmine\level\generator\vanilla\item\enchantment\Enchantment;
use pocketmine\level\generator\vanilla\item\enchantment\EnchantmentInstance;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use function ceil;
use function count;
use function intdiv;
use function max;
use function mt_rand;

/**
 * Server-side grindstone rules (vanilla): the result is always computed here, never taken from the client.
 */
final class GrindstoneHelper {

	private function __construct(){
		//NOOP
	}

	private static function isCurse(int $enchantmentId) : bool{
		return $enchantmentId === Enchantment::BINDING || $enchantmentId === Enchantment::VANISHING;
	}

	/**
	 * - two items: both must be the same damageable item; durability is combined with a 5% bonus
	 * - one item: it must carry at least one enchantment that isn't a curse
	 * In both cases every enchantment but curses is removed and the repair cost is reset.
	 */
	public static function calculateResult(Item $input, Item $additional) : ?GrindstoneResult{
		if ($input->isNull() && $additional->isNull()) {
			return null;
		}

		if (!$input->isNull() && !$additional->isNull()) {
			if (
				!($input instanceof Durable) || !($additional instanceof Durable) ||
				$input->getId() !== $additional->getId() ||
				$input->getCount() !== 1 || $additional->getCount() !== 1
			) {
				return null;
			}
			$output = clone $input;
			$maxDurability = $input->getMaxDurability();
			$remaining = ($maxDurability - $input->getDamage()) + ($maxDurability - $additional->getDamage()) + intdiv($maxDurability * 5, 100);
			$output->setDamage(max(0, $maxDurability - $remaining));
			$sources = [$input, $additional];
		} else {
			$item = $input->isNull() ? $additional : $input;
			if ($item->getCount() !== 1) {
				return null;
			}
			$output = clone $item;
			$sources = [$item];
		}

		$enchantmentValue = 0;
		$removedAny = false;
		/** @var EnchantmentInstance[] $curses */
		$curses = [];
		foreach ($sources as $source) {
			foreach ($source->getEnchantments() as $enchantment) {
				if (self::isCurse($enchantment->getId())) {
					$existing = $curses[$enchantment->getId()] ?? null;
					if ($existing === null || $existing->getLevel() < $enchantment->getLevel()) {
						$curses[$enchantment->getId()] = $enchantment;
					}
				} else {
					$removedAny = true;
					$enchantmentValue += $enchantment->getType()->getMinEnchantAbility($enchantment->getLevel());
				}
			}
		}

		if (count($sources) === 1 && !$removedAny) {
			//nothing to grind off
			return null;
		}

		$output->removeEnchantments();
		foreach ($curses as $curse) {
			$output->addEnchantment($curse);
		}
		$output->removeNamedTagEntry(Item::TAG_REPAIR_COST);

		if ($output instanceof EnchantedBook && count($curses) === 0) {
			$book = ItemFactory::get(Item::BOOK);
			if ($output->hasCustomName()) {
				$book->setCustomName($output->getCustomName());
			}
			$output = $book;
		}

		return new GrindstoneResult($output, $enchantmentValue);
	}

	/**
	 * Experience dropped for the given enchantment value: between half and all of it, like vanilla.
	 */
	public static function rollExperience(int $enchantmentValue) : int{
		if ($enchantmentValue <= 0) {
			return 0;
		}
		$half = (int) ceil($enchantmentValue / 2);
		return $half + mt_rand(0, $half - 1);
	}
}
