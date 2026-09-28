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
use pocketmine\level\generator\vanilla\utils\Random;

/**
 * Everything the conditions and functions of a loot table may look at
 */
final class LootContext
{
	public function __construct(
		private Random $random,
		private float $luck = 0.0,
		private ?Entity $thisEntity = null,
		private ?Entity $killer = null,
		private int $lootingLevel = 0
	) {
	}

	public function getRandom() : Random
	{
		return $this->random;
	}

	public function getLuck() : float
	{
		return $this->luck;
	}

	/**
	 * The entity the loot is generated for (killed mob), if any
	 */
	public function getThisEntity() : ?Entity
	{
		return $this->thisEntity;
	}

	public function getKiller() : ?Entity
	{
		return $this->killer;
	}

	public function getLootingLevel() : int
	{
		return $this->lootingLevel;
	}

	/**
	 * Random integer in [min, max]
	 */
	public function randomInt(int $min, int $max) : int
	{
		return $max <= $min ? $min : $min + $this->random->nextBoundedInt($max - $min + 1);
	}

	/**
	 * Random float in [min, max)
	 */
	public function randomFloat(float $min, float $max) : float
	{
		return $max <= $min ? $min : $min + $this->random->nextFloat() * ($max - $min);
	}
}
