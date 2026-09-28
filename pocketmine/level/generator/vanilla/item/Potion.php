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

use pocketmine\level\generator\vanilla\entity\Effect;
use pocketmine\level\generator\vanilla\entity\EffectInstance;
use pocketmine\level\generator\vanilla\entity\Living;
use pocketmine\level\generator\vanilla\level\sound\BottleEmptySound;
use pocketmine\Player;

class Potion extends Item implements Consumable
{
	public const WATER = 0;
	public const MUNDANE = 1;
	public const LONG_MUNDANE = 2;
	public const THICK = 3;
	public const AWKWARD = 4;
	public const NIGHT_VISION = 5;
	public const LONG_NIGHT_VISION = 6;
	public const INVISIBILITY = 7;
	public const LONG_INVISIBILITY = 8;
	public const LEAPING = 9;
	public const LONG_LEAPING = 10;
	public const STRONG_LEAPING = 11;
	public const FIRE_RESISTANCE = 12;
	public const LONG_FIRE_RESISTANCE = 13;
	public const SWIFTNESS = 14;
	public const LONG_SWIFTNESS = 15;
	public const STRONG_SWIFTNESS = 16;
	public const SLOWNESS = 17;
	public const LONG_SLOWNESS = 18;
	public const WATER_BREATHING = 19;
	public const LONG_WATER_BREATHING = 20;
	public const HEALING = 21;
	public const STRONG_HEALING = 22;
	public const HARMING = 23;
	public const STRONG_HARMING = 24;
	public const POISON = 25;
	public const LONG_POISON = 26;
	public const STRONG_POISON = 27;
	public const REGENERATION = 28;
	public const LONG_REGENERATION = 29;
	public const STRONG_REGENERATION = 30;
	public const STRENGTH = 31;
	public const LONG_STRENGTH = 32;
	public const STRONG_STRENGTH = 33;
	public const WEAKNESS = 34;
	public const LONG_WEAKNESS = 35;
	public const WITHER = 36;

	/**
	 * Returns a list of effects applied by potions with the specified ID.
	 *
	 * @return EffectInstance[]
	 */
	public static function getPotionEffectsById(int $id) : array
	{
		switch ($id) {
			case self::WATER:
			case self::MUNDANE:
			case self::LONG_MUNDANE:
			case self::THICK:
			case self::AWKWARD:
				return [];
			case self::NIGHT_VISION:
				return [
					new EffectInstance(Effect::getEffect(Effect::NIGHT_VISION), 3600)
				];
			case self::LONG_NIGHT_VISION:
				return [
					new EffectInstance(Effect::getEffect(Effect::NIGHT_VISION), 9600)
				];
			case self::INVISIBILITY:
				return [
					new EffectInstance(Effect::getEffect(Effect::INVISIBILITY), 3600)
				];
			case self::LONG_INVISIBILITY:
				return [
					new EffectInstance(Effect::getEffect(Effect::INVISIBILITY), 9600)
				];
			case self::LEAPING:
				return [
					new EffectInstance(Effect::getEffect(Effect::JUMP_BOOST), 3600)
				];
			case self::LONG_LEAPING:
				return [
					new EffectInstance(Effect::getEffect(Effect::JUMP_BOOST), 9600)
				];
			case self::STRONG_LEAPING:
				return [
					new EffectInstance(Effect::getEffect(Effect::JUMP_BOOST), 1800, 1)
				];
			case self::FIRE_RESISTANCE:
				return [
					new EffectInstance(Effect::getEffect(Effect::FIRE_RESISTANCE), 3600)
				];
			case self::LONG_FIRE_RESISTANCE:
				return [
					new EffectInstance(Effect::getEffect(Effect::FIRE_RESISTANCE), 9600)
				];
			case self::SWIFTNESS:
				return [
					new EffectInstance(Effect::getEffect(Effect::SPEED), 3600)
				];
			case self::LONG_SWIFTNESS:
				return [
					new EffectInstance(Effect::getEffect(Effect::SPEED), 9600)
				];
			case self::STRONG_SWIFTNESS:
				return [
					new EffectInstance(Effect::getEffect(Effect::SPEED), 1800, 1)
				];
			case self::SLOWNESS:
				return [
					new EffectInstance(Effect::getEffect(Effect::SLOWNESS), 1800)
				];
			case self::LONG_SLOWNESS:
				return [
					new EffectInstance(Effect::getEffect(Effect::SLOWNESS), 4800)
				];
			case self::WATER_BREATHING:
				return [
					new EffectInstance(Effect::getEffect(Effect::WATER_BREATHING), 3600)
				];
			case self::LONG_WATER_BREATHING:
				return [
					new EffectInstance(Effect::getEffect(Effect::WATER_BREATHING), 9600)
				];
			case self::HEALING:
				return [
					new EffectInstance(Effect::getEffect(Effect::INSTANT_HEALTH))
				];
			case self::STRONG_HEALING:
				return [
					new EffectInstance(Effect::getEffect(Effect::INSTANT_HEALTH), null, 1)
				];
			case self::HARMING:
				return [
					new EffectInstance(Effect::getEffect(Effect::INSTANT_DAMAGE))
				];
			case self::STRONG_HARMING:
				return [
					new EffectInstance(Effect::getEffect(Effect::INSTANT_DAMAGE), null, 1)
				];
			case self::POISON:
				return [
					new EffectInstance(Effect::getEffect(Effect::POISON), 900)
				];
			case self::LONG_POISON:
				return [
					new EffectInstance(Effect::getEffect(Effect::POISON), 2400)
				];
			case self::STRONG_POISON:
				return [
					new EffectInstance(Effect::getEffect(Effect::POISON), 440, 1)
				];
			case self::REGENERATION:
				return [
					new EffectInstance(Effect::getEffect(Effect::REGENERATION), 900)
				];
			case self::LONG_REGENERATION:
				return [
					new EffectInstance(Effect::getEffect(Effect::REGENERATION), 2400)
				];
			case self::STRONG_REGENERATION:
				return [
					new EffectInstance(Effect::getEffect(Effect::REGENERATION), 440, 1)
				];
			case self::STRENGTH:
				return [
					new EffectInstance(Effect::getEffect(Effect::STRENGTH), 3600)
				];
			case self::LONG_STRENGTH:
				return [
					new EffectInstance(Effect::getEffect(Effect::STRENGTH), 9600)
				];
			case self::STRONG_STRENGTH:
				return [
					new EffectInstance(Effect::getEffect(Effect::STRENGTH), 1800, 1)
				];
			case self::WEAKNESS:
				return [
					new EffectInstance(Effect::getEffect(Effect::WEAKNESS), 1800)
				];
			case self::LONG_WEAKNESS:
				return [
					new EffectInstance(Effect::getEffect(Effect::WEAKNESS), 4800)
				];
			case self::WITHER:
				return [
					new EffectInstance(Effect::getEffect(Effect::WITHER), 800, 1)
				];
		}

		return [];
	}

	public static function getColor(int $meta) : array
	{
		$effect = Effect::getEffect(self::getEffectId($meta));
		if ($effect !== null) {
			$color = $effect->getColor();
			return [$color->getR(), $color->getG(), $color->getB()];
		}
		return [0, 0, 0];
	}

	public static function getEffectId(int $meta) : int
	{
		switch ($meta) {
			case self::INVISIBILITY:
			case self::LONG_INVISIBILITY:
				return Effect::INVISIBILITY;
			case self::LEAPING:
			case self::LONG_LEAPING:
			case self::STRONG_LEAPING:
				return Effect::JUMP;
			case self::FIRE_RESISTANCE:
			case self::LONG_FIRE_RESISTANCE:
				return Effect::FIRE_RESISTANCE;
			case self::SWIFTNESS:
			case self::LONG_SWIFTNESS:
			case self::STRONG_SWIFTNESS:
				return Effect::SPEED;
			case self::SLOWNESS:
			case self::LONG_SLOWNESS:
				return Effect::SLOWNESS;
			case self::WATER_BREATHING:
			case self::LONG_WATER_BREATHING:
				return Effect::WATER_BREATHING;
			case self::HARMING:
			case self::STRONG_HARMING:
				return Effect::HARMING;
			case self::POISON:
			case self::LONG_POISON:
			case self::STRONG_POISON:
				return Effect::POISON;
			case self::HEALING:
			case self::STRONG_HEALING:
				return Effect::HEALING;
			case self::NIGHT_VISION:
			case self::LONG_NIGHT_VISION:
				return Effect::NIGHT_VISION;
			case self::REGENERATION:
			case self::LONG_REGENERATION:
			case self::STRONG_REGENERATION:
				return Effect::REGENERATION;
			default:
				return 0;
		}
	}

	public function __construct(int $meta = 0)
	{
		parent::__construct(self::POTION, $meta, "Potion");
	}

	public function getMaxStackSize() : int
	{
		return 1;
	}

	public function onConsume(Living $consumer)
	{
		$consumer->broadcastSound(new BottleEmptySound($consumer));
	}

	public function getAdditionalEffects() : array
	{
		//TODO: check CustomPotionEffects NBT
		return self::getPotionEffectsById($this->meta);
	}

	public function getResidue()
	{
		return ItemFactory::get(Item::GLASS_BOTTLE);
	}

	public function canStartUsingItem(Player $player) : bool
	{
		return true;
	}
}
