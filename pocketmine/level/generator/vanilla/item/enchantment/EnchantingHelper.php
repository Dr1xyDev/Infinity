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

namespace pocketmine\level\generator\vanilla\item\enchantment;

use pocketmine\level\generator\vanilla\block\BlockIds;
use pocketmine\level\generator\vanilla\item\Armor;
use pocketmine\level\generator\vanilla\item\ArmorSlot;
use pocketmine\level\generator\vanilla\item\Axe;
use pocketmine\level\generator\vanilla\item\Bow;
use pocketmine\level\generator\vanilla\item\Elytra;
use pocketmine\level\generator\vanilla\item\FishingRod;
use pocketmine\level\generator\vanilla\item\FlintSteel;
use pocketmine\level\generator\vanilla\item\Hoe;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\item\ItemIds;
use pocketmine\level\generator\vanilla\item\Pickaxe;
use pocketmine\level\generator\vanilla\item\Shears;
use pocketmine\level\generator\vanilla\item\Shovel;
use pocketmine\level\generator\vanilla\item\Sword;
use pocketmine\level\generator\vanilla\level\Position;
use pocketmine\level\generator\vanilla\utils\Random;
use function abs;
use function array_filter;
use function chr;
use function count;
use function floor;
use function max;
use function min;
use function mt_rand;
use function ord;
use function round;

/**
 * Helper methods used for enchanting using the enchanting table.
 */
final class EnchantingHelper{
	private const MAX_BOOKSHELF_COUNT = 15;

	private function __construct(){
		//NOOP
	}

	/**
	 * Generates a new random seed for enchant option randomization.
	 */
	public static function generateSeed() : int{
		return mt_rand(INT32_MIN, INT32_MAX);
	}

	/**
	 * @param EnchantmentInstance[] $enchantments
	 */
	public static function enchantItem(Item $item, array $enchantments) : Item{
		$resultItem = $item->getId() === ItemIds::BOOK ? ItemFactory::get(ItemIds::ENCHANTED_BOOK) : clone $item;

		foreach($enchantments as $enchantment){
			$resultItem->addEnchantment($enchantment);
		}

		return $resultItem;
	}

	/**
	 * @return EnchantingOption[]
	 */
	public static function generateOptions(Position $tablePos, Item $input, int $seed) : array{
		if($input->isNull() || $input->hasEnchantments()){
			return [];
		}

		$random = new Random($seed);

		$bookshelfCount = self::countBookshelves($tablePos);
		$baseRequiredLevel = $random->nextRange(1, 8) + ($bookshelfCount >> 1) + $random->nextRange(0, $bookshelfCount);
		$topRequiredLevel = (int) floor(max($baseRequiredLevel / 3, 1));
		$middleRequiredLevel = (int) floor($baseRequiredLevel * 2 / 3 + 1);
		$bottomRequiredLevel = max($baseRequiredLevel, $bookshelfCount * 2);

		return [
			self::createOption($random, $input, $topRequiredLevel),
			self::createOption($random, $input, $middleRequiredLevel),
			self::createOption($random, $input, $bottomRequiredLevel),
		];
	}

	private static function countBookshelves(Position $tablePos) : int{
		$bookshelfCount = 0;
		$level = $tablePos->getLevel();

		for($x = -2; $x <= 2; $x++){
			for($z = -2; $z <= 2; $z++){
				// We only check blocks at a distance of 2 blocks from the enchanting table
				if(abs($x) !== 2 && abs($z) !== 2){
					continue;
				}

				// Ensure the space between the bookshelf stack at this X/Z and the enchanting table is empty
				for($y = 0; $y <= 1; $y++){
					// Calculate the coordinates of the space between the bookshelf and the enchanting table
					$spaceX = max(min($x, 1), -1);
					$spaceZ = max(min($z, 1), -1);
					$spaceBlock = $level->getBlock($tablePos->add($spaceX, $y, $spaceZ));
					if($spaceBlock->getId() !== BlockIds::AIR){
						continue 2;
					}
				}

				// Finally, check the number of bookshelves at the current position
				for($y = 0; $y <= 1; $y++){
					$block = $level->getBlock($tablePos->add($x, $y, $z));
					if($block->getId() === BlockIds::BOOKSHELF){
						$bookshelfCount++;
						if($bookshelfCount === self::MAX_BOOKSHELF_COUNT){
							return $bookshelfCount;
						}
					}
				}
			}
		}

		return $bookshelfCount;
	}

	private static function createOption(Random $random, Item $inputItem, int $requiredXpLevel) : EnchantingOption{
		$enchantingPower = $requiredXpLevel;

		$enchantability = $inputItem->getEnchantability();
		$enchantingPower = $enchantingPower + $random->nextRange(0, $enchantability >> 2) + $random->nextRange(0, $enchantability >> 2) + 1;
		// Random bonus for enchanting power between 0.85 and 1.15
		$bonus = 1 + ($random->nextFloat() + $random->nextFloat() - 1) * 0.15;
		$enchantingPower = (int) round($enchantingPower * $bonus);

		$resultEnchantments = [];
		$availableEnchantments = self::getAvailableEnchantments($enchantingPower, $inputItem);

		$lastEnchantment = self::getRandomWeightedEnchantment($random, $availableEnchantments);
		if($lastEnchantment !== null){
			$resultEnchantments[] = $lastEnchantment;

			// With probability (power + 1) / 50, continue adding enchantments
			while($random->nextFloat() <= ($enchantingPower + 1) / 50){
				// Remove from the list of available enchantments anything that conflicts
				// with previously-chosen enchantments
				$availableEnchantments = array_filter(
					$availableEnchantments,
					function(EnchantmentInstance $e) use ($lastEnchantment){
						return $e->getType() !== $lastEnchantment->getType() &&
							$e->getType()->isCompatibleWith($lastEnchantment->getType());
					}
				);

				$lastEnchantment = self::getRandomWeightedEnchantment($random, $availableEnchantments);
				if($lastEnchantment === null){
					break;
				}

				$resultEnchantments[] = $lastEnchantment;
				$enchantingPower >>= 1;
			}
		}

		return new EnchantingOption($requiredXpLevel, self::getRandomOptionName($random), $resultEnchantments);
	}

	/**
	 * @return EnchantmentInstance[]
	 */
	private static function getAvailableEnchantments(int $enchantingPower, Item $item) : array{
		$list = [];

		foreach(self::getPrimaryEnchantmentsForItem($item) as $enchantment){
			for($lvl = $enchantment->getMaxLevel(); $lvl > 0; $lvl--){
				if($enchantingPower >= $enchantment->getMinEnchantAbility($lvl) &&
					$enchantingPower <= $enchantment->getMaxEnchantAbility($lvl)
				){
					$list[] = new EnchantmentInstance($enchantment, $lvl);
					break;
				}
			}
		}

		return $list;
	}

	/**
	 * @param EnchantmentInstance[] $enchantments
	 */
	private static function getRandomWeightedEnchantment(Random $random, array $enchantments) : ?EnchantmentInstance{
		if(count($enchantments) === 0){
			return null;
		}

		$totalWeight = 0;
		foreach($enchantments as $enchantment){
			$totalWeight += $enchantment->getType()->getRarity();
		}

		$result = null;
		$randomWeight = $random->nextRange(1, $totalWeight);

		foreach($enchantments as $enchantment){
			$randomWeight -= $enchantment->getType()->getRarity();

			if($randomWeight <= 0){
				$result = $enchantment;
				break;
			}
		}

		return $result;
	}

	private static function getRandomOptionName(Random $random) : string{
		$name = "";
		for($i = $random->nextRange(5, 15); $i > 0; $i--){
			$name .= chr($random->nextRange(ord("a"), ord("z")) & 0xff);
		}

		return $name;
	}

	/**
	 * @return Enchantment[]
	 */
	public static function getPrimaryEnchantmentsForItem(Item $item) : array{
		$slot = self::getItemSlot($item);
		$result = [];
		foreach(Enchantment::getAllEnchantments() as $enchantment){
			if($enchantment !== null && ($item->getId() === ItemIds::BOOK || $enchantment->hasPrimaryItemType($slot))){
				$result[] = $enchantment;
			}
		}

		return $result;
	}

	public static function getItemSlot(Item $item) : int{
		if(($item instanceof Shears || $item instanceof FlintSteel || $item instanceof Hoe) && $item->getMaxDurability() >= 0){
			return Enchantment::SLOT_TOOL;
		}elseif($item instanceof Armor){
			switch($item->getArmorSlot()){
				case ArmorSlot::SLOT_HELMET: return Enchantment::SLOT_HEAD;
				case ArmorSlot::SLOT_CHESTPLATE: return Enchantment::SLOT_TORSO;
				case ArmorSlot::SLOT_LEGGINGS: return Enchantment::SLOT_LEGS;
				case ArmorSlot::SLOT_BOOTS: return Enchantment::SLOT_FEET;
			}
		}elseif($item instanceof Sword){
			return Enchantment::SLOT_SWORD;
		}elseif($item instanceof Pickaxe || $item instanceof Shovel || $item instanceof Axe){
			return Enchantment::SLOT_DIG;
		}elseif($item instanceof Bow){
			return Enchantment::SLOT_BOW;
		}elseif($item instanceof FishingRod){
			return Enchantment::SLOT_FISHING_ROD;
		}elseif($item instanceof Elytra){
			return Enchantment::SLOT_ELYTRA;
		}elseif($item->getId() === ItemIds::SKULL || $item->getId() === ItemIds::PUMPKIN){
			return Enchantment::SLOT_WEARABLE;
		}elseif($item->getId() === ItemIds::SHIELD){
			return Enchantment::SLOT_SHIELD;
		}elseif($item->getId() === ItemIds::TRIDENT){
			return Enchantment::SLOT_TRIDENT;
		}

		return Enchantment::SLOT_NONE;
	}
}
