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

use pocketmine\level\generator\vanilla\entity\Arthropod;
use pocketmine\level\generator\vanilla\entity\Effect;
use pocketmine\level\generator\vanilla\entity\EffectInstance;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\entity\Living;

class BaneOfArthropodsEnchantment extends MeleeWeaponEnchantment
{
	public function getMinEnchantAbility(int $level) : int
	{
		return 5 + ($level - 1) * 8;
	}

	public function getMaxEnchantAbility(int $level) : int
	{
		return $this->getMinEnchantAbility($level) + 20;
	}

	public function isApplicableTo(Entity $victim) : bool
	{
		return $victim instanceof Arthropod;
	}

	public function getDamageBonus(int $enchantmentLevel) : float
	{
		return $enchantmentLevel * 2.5;
	}

	public function onPostAttack(Entity $attacker, Entity $victim, int $enchantmentLevel) : void
	{
		if ($victim instanceof Living) {
			$victim->addEffect(new EffectInstance(Effect::getEffect(Effect::SLOWNESS), 20 + $victim->random->nextBoundedInt(10) * $enchantmentLevel, 3));
		}
	}
}
