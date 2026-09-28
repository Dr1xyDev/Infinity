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

namespace pocketmine\level\generator\vanilla\item;

use pocketmine\level\generator\vanilla\item\enchantment\Enchantment;
use pocketmine\level\generator\vanilla\nbt\tag\ByteTag;
use pocketmine\level\generator\vanilla\utils\Utils;

use function min;

abstract class Durable extends Item
{
	/**
	 * Returns whether this item will take damage when used.
	 */
	public function isUnbreakable() : bool
	{
		return $this->getNamedTag()->getByte("Unbreakable", 0) !== 0;
	}

	/**
	 * Sets whether the item will take damage when used.
	 *
	 * @return void
	 */
	public function setUnbreakable(bool $value = true)
	{
		$this->setNamedTagEntry(new ByteTag("Unbreakable", $value ? 1 : 0));
	}

	/**
	 * Applies damage to the item.
	 *
	 * @return bool if any damage was applied to the item
	 */
	public function applyDamage(int $amount) : bool
	{
		if ($this->isUnbreakable() || $this->isBroken()) {
			return false;
		}

		$amount -= $this->getUnbreakingDamageReduction($amount);

		$this->meta = min($this->meta + $amount, $this->getMaxDurability());
		if ($this->isBroken()) {
			$this->onBroken();
		}

		return true;
	}

	protected function getUnbreakingDamageReduction(int $amount) : int
	{
		if (($unbreakingLevel = $this->getEnchantmentLevel(Enchantment::UNBREAKING)) > 0) {
			$negated = 0;

			$chance = 1 / ($unbreakingLevel + 1);
			for ($i = 0; $i < $amount; ++$i) {
				if (Utils::getRandomFloat() > $chance) {
					$negated++;
				}
			}

			return $negated;
		}

		return 0;
	}

	/**
	 * Called when the item's damage exceeds its maximum durability.
	 */
	protected function onBroken() : void
	{
		$this->pop();
	}

	/**
	 * Returns the maximum amount of damage this item can take before it breaks.
	 */
	abstract public function getMaxDurability() : int;

	/**
	 * Returns whether the item is broken.
	 */
	public function isBroken() : bool
	{
		return $this->meta >= $this->getMaxDurability();
	}
}
