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

use InvalidArgumentException;

abstract class TieredTool extends Tool
{
	public const TIER_WOODEN = 1;
	public const TIER_GOLD = 2;
	public const TIER_STONE = 3;
	public const TIER_IRON = 4;
	public const TIER_DIAMOND = 5;
	public const TIER_NETHERITE = 6;

	/** @var int */
	protected $tier;

	public function __construct(int $id, int $meta, string $name, int $tier)
	{
		parent::__construct($id, $meta, $name);
		$this->tier = $tier;
	}

	public function getMaxDurability() : int
	{
		return self::getDurabilityFromTier($this->tier);
	}

	public function getEnchantAbility() : int{
		return $this->getEnchantAbilityFromTier($this->tier);
	}

	public function getEnchantAbilityFromTier(int $tier) : int{
		static $levels = [
			self::TIER_STONE => 5,
			self::TIER_DIAMOND => 10,
			self::TIER_IRON => 14,
			self::TIER_WOODEN => 15,
			self::TIER_NETHERITE => 15,
			self::TIER_GOLD => 22
		];

		if (!isset($levels[$tier])) {
			throw new InvalidArgumentException("Unknown tier '$tier'");
		}

		return $levels[$tier];
	}

	public function getTier() : int
	{
		return $this->tier;
	}

	public static function getDurabilityFromTier(int $tier) : int
	{
		static $levels = [
			self::TIER_GOLD => 33,
			self::TIER_WOODEN => 60,
			self::TIER_STONE => 132,
			self::TIER_IRON => 251,
			self::TIER_DIAMOND => 1562,
			self::TIER_NETHERITE => 2032
		];

		if (!isset($levels[$tier])) {
			throw new InvalidArgumentException("Unknown tier '$tier'");
		}

		return $levels[$tier];
	}

	protected static function getBaseDamageFromTier(int $tier) : int
	{
		static $levels = [
			self::TIER_WOODEN => 5,
			self::TIER_GOLD => 5,
			self::TIER_STONE => 6,
			self::TIER_IRON => 7,
			self::TIER_DIAMOND => 8,
			self::TIER_NETHERITE => 9
		];

		if (!isset($levels[$tier])) {
			throw new InvalidArgumentException("Unknown tier '$tier'");
		}

		return $levels[$tier];
	}

	public static function getBaseMiningEfficiencyFromTier(int $tier) : float
	{
		static $levels = [
			self::TIER_WOODEN => 2,
			self::TIER_STONE => 4,
			self::TIER_IRON => 6,
			self::TIER_DIAMOND => 8,
			self::TIER_NETHERITE => 10,
			self::TIER_GOLD => 12
		];

		if (!isset($levels[$tier])) {
			throw new InvalidArgumentException("Unknown tier '$tier'");
		}

		return $levels[$tier];
	}

	protected function getBaseMiningEfficiency() : float
	{
		return self::getBaseMiningEfficiencyFromTier($this->tier);
	}

	public function getFuelTime() : int
	{
		if ($this->tier === self::TIER_WOODEN) {
			return 200;
		}

		return 0;
	}
}
