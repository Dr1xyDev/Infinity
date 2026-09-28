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

use pocketmine\level\generator\vanilla\entity\Entity;

/**
 * Classes extending this class can be applied to weapons and activate when used by a mob to attack another mob in melee
 * combat.
 */
abstract class MeleeWeaponEnchantment extends Enchantment
{
	/**
	 * Returns whether this melee enchantment has an effect on the target entity. For example, Smite only applies to
	 * undead mobs.
	 */
	abstract public function isApplicableTo(Entity $victim) : bool;

	/**
	 * Returns the amount of additional damage caused by this enchantment to applicable targets.
	 */
	abstract public function getDamageBonus(int $enchantmentLevel) : float;

	/**
	 * Called after damaging the entity to apply any post damage effects to the target.
	 */
	public function onPostAttack(Entity $attacker, Entity $victim, int $enchantmentLevel) : void
	{

	}
}
