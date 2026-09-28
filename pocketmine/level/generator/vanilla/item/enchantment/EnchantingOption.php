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

/**
 * Represents an option on the enchanting table menu.
 * If selected, all the enchantments in the option will be applied to the item.
 */
class EnchantingOption{

	/**
	 * @param EnchantmentInstance[] $enchantments
	 */
	public function __construct(
		private int $requiredXpLevel,
		private string $displayName,
		private array $enchantments
	){}

	/**
	 * Returns the minimum amount of XP levels required to select this enchantment option.
	 * It's NOT the number of XP levels that will be subtracted after enchanting.
	 */
	public function getRequiredXpLevel() : int{
		return $this->requiredXpLevel;
	}

	/**
	 * Returns the name that will be translated to the 'Standard Galactic Alphabet' client-side.
	 * This can be any arbitrary text string, since the vanilla client cannot read the text anyway.
	 * Example: 'bless creature range free'.
	 */
	public function getDisplayName() : string{
		return $this->displayName;
	}

	/**
	 * Returns the enchantments that will be applied to the item when this option is clicked.
	 *
	 * @return EnchantmentInstance[]
	 */
	public function getEnchantments() : array{
		return $this->enchantments;
	}
}
