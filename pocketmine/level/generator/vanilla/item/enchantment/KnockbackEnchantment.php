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
use pocketmine\level\generator\vanilla\entity\Living;

class KnockbackEnchantment extends MeleeWeaponEnchantment
{
	public function isApplicableTo(Entity $victim) : bool
	{
		return $victim instanceof Living;
	}

	public function getDamageBonus(int $enchantmentLevel) : float
	{
		return 0;
	}

	public function onPostAttack(Entity $attacker, Entity $victim, int $enchantmentLevel) : void
	{
		if ($victim instanceof Living) {
			$diff = $victim->subtractVector($attacker);
			$victim->knockBack($diff->x, $diff->z, $enchantmentLevel * 0.5);
		}
	}
}
