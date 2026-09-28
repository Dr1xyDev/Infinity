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

use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\inventory\AnvilResult;
use pocketmine\level\generator\vanilla\item\Durable;
use pocketmine\level\generator\vanilla\item\EnchantedBook;
use pocketmine\level\generator\vanilla\item\enchantment\Enchantment;
use pocketmine\level\generator\vanilla\item\enchantment\EnchantmentInstance;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\item\ItemIds;
use pocketmine\level\generator\vanilla\item\TieredTool;
use pocketmine\level\generator\vanilla\nbt\tag\ListTag;
use function count;
use function intval;
use function max;
use function min;

class AnvilHelper {

	private function __construct(){
		//NOOP
	}

	public static function calculateResult(Item $input, Item $material, ?string $customName, bool $isCreative) : ?AnvilResult {
		$output = clone $input;

		$totalRepairCost = $input->getRepairCost() + $material->getRepairCost();
		$levelCostBonus = 0;
		$renamed = false;

		static $tierIds = [
			TieredTool::TIER_WOODEN => BlockIds::WOODEN_PLANKS,
			TieredTool::TIER_STONE => BlockIds::COBBLESTONE,
			TieredTool::TIER_IRON => ItemIds::IRON_INGOT,
			TieredTool::TIER_GOLD => ItemIds::GOLD_INGOT,
			TieredTool::TIER_DIAMOND => ItemIds::DIAMOND,
			TieredTool::TIER_NETHERITE => ItemIds::NETHERITE_INGOT
		];

		if ($output->isNull()) {
			return null;
		}

		if ($customName !== null) {
			$renamed = true;
			$levelCostBonus++;
		}

		if (!$material->isNull()) {
			$enchantedBook = $material instanceof EnchantedBook && count($material->getEnchantments()) > 0;

			if ($output instanceof TieredTool && isset($tierIds[$output->getTier()])) {
				$targetMaterial = ItemFactory::get($tierIds[$output->getTier()]);
				if ($material->equals($targetMaterial)) {
					$d = min($input->getDamage(), (int) ($output->getMaxDurability() / 4));

					for ($m2 = 0; $d > 0 && $m2 < $material->getCount(); $m2++) {
						$output->setDamage($output->getDamage() - $d);
						$levelCostBonus++;
						$d = min($output->getDamage(), (int) ($output->getMaxDurability() / 4));
					}
				} else {
					goto material_is_tool;
				}
			} else {
				material_is_tool:

				if (!$enchantedBook && (!$output->equals($material, false, false) || !($output instanceof Durable))) {
					return null;
				}

				if ($output instanceof Durable && !$enchantedBook && $material instanceof Durable) {
					$f = ($output->getMaxDurability() - $output->getDamage()) + ($material->getMaxDurability() - $material->getDamage()) + intval(($output->getMaxDurability() * 12) / 100);
					$f2 = max(0, $output->getMaxDurability() - $f);

					if ($f2 < $output->getDamage()) {
						$output->setDamage($f2);
						$levelCostBonus += 2;
					}
				}

				foreach ($material->getEnchantments() as $enchantmentInstance) {
					$enchantment = $enchantmentInstance->getType();

					$l1 = $enchantmentInstance->getLevel();
					$cel = $output->getEnchantmentLevel($enchantmentInstance->getId());

					if ($l1 === $cel) {
						$cel++;
					} else {
						$cel = max($cel, $l1);
					}

					$canApply = ($enchantment->canApply($output) || $isCreative || $output instanceof EnchantedBook);

					foreach ($output->getEnchantments() as $enchantmentInstance2) {
						if ($enchantment->getId() !== $enchantmentInstance2->getId() && !$enchantment->canApplyTogether($enchantmentInstance2->getType())) {
							$canApply = false;
							$levelCostBonus++;
						}
					}

					if ($canApply) {
						$cel = min($cel, $enchantment->getMaxLevel());

						$output->addEnchantment(new EnchantmentInstance($enchantment, $cel));
						$rarityBonus = match ($enchantment->getRarity()) {
							Enchantment::RARITY_MYTHIC => 8,
							Enchantment::RARITY_RARE => 4,
							Enchantment::RARITY_UNCOMMON => 2,
							Enchantment::RARITY_COMMON => 1,
							default => 0
						};

						if ($enchantedBook) {
							$rarityBonus = max(1, intval($rarityBonus / 2));
						}

						$levelCostBonus += $rarityBonus * $cel;
					}
				}
			}
		}

		$onlyRenamed = $renamed && $levelCostBonus === 1;
		$levelCost = $totalRepairCost + $levelCostBonus;

		if ($onlyRenamed && $levelCost > 39) {
			$levelCost = 39;
		}

		if ($levelCost > 39 && !$isCreative) {
			return null;
		}

		if ((!$onlyRenamed && (!$isCreative && $input->getRepairCost() >= 63)) || $input->getRepairCost() >= 2147483647) {
			return null;
		}

		$repairCost = $output->getRepairCost();

		if (!$material->isNull() && $repairCost < $material->getRepairCost()) {
			$repairCost = $material->getRepairCost();
		}

		$output->setRepairCost($repairCost * 2 + 1);

		if ($renamed) {
			$output->setCustomName($customName);
		}

		return new AnvilResult($output, $levelCost);
	}

	public static function sortEnchantments(Item $result, Item $output) : void{
		$map1 = [];
		$map2 = [];

		foreach ($result->getEnchantments() as $e) {
			$map1[$e->getId()] = $e->getLevel();
		}

		foreach ($output->getEnchantments() as $e) {
			$map2[$e->getId()] = $e->getLevel();
		}

		$same = true;
		foreach ($map1 as $id => $level) {
			if (isset($map2[$id])) {
				if ($map2[$id] !== $level) {
					$same = false;
					break;
				}
			} else {
				break;
			}
		}

		if ($same && count($map1) !== 0 && count($map2) !== 0) {
			$output->setNamedTagEntry($result->getNamedTagEntry(Item::TAG_ENCH) ?? new ListTag(Item::TAG_ENCH, []));
		}
	}
}
