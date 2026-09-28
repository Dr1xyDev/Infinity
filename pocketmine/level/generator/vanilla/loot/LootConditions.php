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

namespace pocketmine\level\generator\vanilla\loot;

use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\entity\Tamable;
use pocketmine\Player;
use function is_array;
use function is_numeric;
use function is_string;
use function str_replace;

/**
 * Vanilla loot conditions. Conditions this server can't evaluate are treated as passed.
 */
final class LootConditions
{
	private function __construct()
	{
		//NOOP
	}

	/**
	 * @param mixed[] $conditions
	 */
	public static function testAll(array $conditions, LootContext $context) : bool
	{
		foreach ($conditions as $condition) {
			if (is_array($condition) && !self::test($condition, $context)) {
				return false;
			}
		}
		return true;
	}

	/**
	 * @param mixed[] $condition
	 */
	public static function test(array $condition, LootContext $context) : bool
	{
		$name = is_string($condition["condition"] ?? null) ? str_replace("minecraft:", "", $condition["condition"]) : "";
		switch ($name) {
			case "random_chance":
				return $context->getRandom()->nextFloat() < self::number($condition["chance"] ?? 1, $context);
			case "random_chance_with_looting":
				$chance = self::number($condition["chance"] ?? 1, $context) + $context->getLootingLevel() * self::number($condition["looting_multiplier"] ?? 0, $context);
				return $context->getRandom()->nextFloat() < $chance;
			case "random_difficulty_chance":
				return $context->getRandom()->nextFloat() < self::number($condition["normal"] ?? $condition["default_chance"] ?? 1, $context);
			case "random_regional_difficulty_chance":
				return $context->getRandom()->nextFloat() < self::number($condition["max_chance"] ?? 1, $context) * 0.5;
			case "killed_by_player":
				return $context->getKiller() instanceof Player;
			case "killed_by_player_or_pets":
				$killer = $context->getKiller();
				return $killer instanceof Player || ($killer instanceof Tamable && $killer->isTamed());
			case "killed_by_entity":
				return $context->getKiller() !== null;
			case "entity_properties":
				$entity = ($condition["entity"] ?? "this") === "killer" ? $context->getKiller() : $context->getThisEntity();
				return self::testEntityProperties($entity, is_array($condition["properties"] ?? null) ? $condition["properties"] : []);
			default:
				return true;
		}
	}

	/**
	 * @param mixed[] $properties
	 */
	private static function testEntityProperties(?Entity $entity, array $properties) : bool
	{
		if ($entity === null) {
			return false;
		}
		if (isset($properties["on_fire"]) && (bool) $properties["on_fire"] !== $entity->isOnFire()) {
			return false;
		}
		return true;
	}

	private static function number(mixed $value, LootContext $context) : float
	{
		return is_numeric($value) ? (float) $value : LootRange::toFloat($value, $context, 1.0);
	}
}
