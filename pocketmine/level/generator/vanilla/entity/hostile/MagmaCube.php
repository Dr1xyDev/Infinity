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

namespace pocketmine\level\generator\vanilla\entity\hostile;

use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\level\particle\Particle;

class MagmaCube extends Slime
{
	public const NETWORK_ID = self::MAGMA_CUBE;

	public function getName() : string
	{
		return "Magma Cube";
	}

	public function getAttackStrength() : float
	{
		return parent::getAttackStrength() + 2;
	}

	public function canDamagePlayer() : bool
	{
		return true;
	}

	public function makesSoundOnLand() : bool
	{
		return true;
	}

	public function fall(float $fallDistance) : void
	{
		// NOOP
	}

	public function handleLavaJump() : void
	{
		$this->motion->y = 0.22 + $this->getSlimeSize() * 0.05;
	}

	public function jump() : void
	{
		$this->motion->y = 0.42 + $this->getSlimeSize() * 0.1;
	}

	protected function alterSquishAmount() : void
	{
		$this->squishAmount *= 0.9;
	}

	public function getJumpDelay() : int
	{
		return parent::getJumpDelay() * 4;
	}

	public function setOnFire(int $seconds) : void
	{
		// NOOP
	}

	public function getDrops() : array
	{
		$drops = [];

		if ($this->getSlimeSize() > 1) {
			$i = $this->random->nextBoundedInt(4) - 2;
			$loot = $this->getLootingLevel();

			if ($loot > 0) {
				$i += $this->random->nextBoundedInt($loot + 1);
			}

			for ($j = 0; $j < $i; $j++) {
				$drops[] = ItemFactory::get(Item::MAGMA_CREAM);
			}
		}

		return $drops;
	}

	protected function getParticleType() : int
	{
		return Particle::TYPE_FLAME;
	}

	public function getArmorPoints() : int
	{
		return $this->getSlimeSize() * 3;
	}

	public function canSpawnHere() : bool
	{
		return $this->level->getDifficulty() !== Level::DIFFICULTY_PEACEFUL;
	}

	protected function addAttributes() : void
	{
		parent::addAttributes();

		$this->setMovementSpeed(0.2);
	}
}
