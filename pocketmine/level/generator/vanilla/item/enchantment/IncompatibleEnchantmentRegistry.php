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

use pocketmine\level\generator\vanilla\item\enchantment\IncompatibleEnchantmentGroups as Groups;
use pocketmine\level\generator\vanilla\utils\SingletonTrait;
use function array_intersect_key;
use function count;
use function spl_object_id;

/**
 * Manages which enchantments are incompatible with each other.
 * Enchantments can be added to groups to make them incompatible with all other enchantments already in that group.
 */
final class IncompatibleEnchantmentRegistry{
	use SingletonTrait;

	/**
	 * @phpstan-var array<int, array<string, true>>
	 * @var true[][]
	 */
	private array $incompatibilityMap = [];

	private function __construct(){
		$this->register(Groups::PROTECTION, [
			Enchantment::getEnchantment(Enchantment::PROTECTION),
			Enchantment::getEnchantment(Enchantment::FIRE_PROTECTION),
			Enchantment::getEnchantment(Enchantment::BLAST_PROTECTION),
			Enchantment::getEnchantment(Enchantment::PROJECTILE_PROTECTION)
		]);
		$this->register(Groups::BOW_INFINITE, [
			Enchantment::getEnchantment(Enchantment::INFINITY),
			Enchantment::getEnchantment(Enchantment::MENDING)
		]);
		$this->register(Groups::BLOCK_DROPS, [
			Enchantment::getEnchantment(Enchantment::FORTUNE),
			Enchantment::getEnchantment(Enchantment::SILK_TOUCH)
		]);
	}

	/**
	 * Register incompatibility for an enchantment group.
	 *
	 * All enchantments belonging to the same group are incompatible with each other,
	 * i.e. they cannot be added together on the same item.
	 *
	 * @param Enchantment[] $enchantments
	 */
	public function register(string $tag, array $enchantments) : void{
		foreach($enchantments as $enchantment){
			$this->incompatibilityMap[spl_object_id($enchantment)][$tag] = true;
		}
	}

	/**
	 * Unregister incompatibility for some enchantments of a particular group.
	 *
	 * @param Enchantment[] $enchantments
	 */
	public function unregister(string $tag, array $enchantments) : void{
		foreach($enchantments as $enchantment){
			unset($this->incompatibilityMap[spl_object_id($enchantment)][$tag]);
		}
	}

	/**
	 * Unregister incompatibility for all enchantments of a particular group.
	 */
	public function unregisterAll(string $tag) : void{
		foreach($this->incompatibilityMap as $id => $tags){
			unset($this->incompatibilityMap[$id][$tag]);
		}
	}

	/**
	 * Returns whether two enchantments can be applied to the same item.
	 */
	public function areCompatible(Enchantment $first, Enchantment $second) : bool{
		$firstIncompatibilities = $this->incompatibilityMap[spl_object_id($first)] ?? [];
		$secondIncompatibilities = $this->incompatibilityMap[spl_object_id($second)] ?? [];
		return count(array_intersect_key($firstIncompatibilities, $secondIncompatibilities)) === 0;
	}
}
