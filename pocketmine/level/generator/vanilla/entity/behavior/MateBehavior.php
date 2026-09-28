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

namespace pocketmine\level\generator\vanilla\entity\behavior;

use pocketmine\level\generator\vanilla\entity\Animal;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\entity\Mob;

class MateBehavior extends Behavior
{
	protected float $speedMultiplier;
	protected int $spawnBabyDelay = 0;
	protected ?Animal $targetMate = null;

	public function __construct(Animal $mob, float $speedMultiplier)
	{
		parent::__construct($mob);

		$this->speedMultiplier = $speedMultiplier;
		$this->mutexBits = 3;
	}

	public function canStart() : bool
	{
		if ($this->mob->isInLove()) {
			$mate = $this->getNearbyMate();
			if ($mate !== null) {
				$this->targetMate = $mate;
				return true;
			}
		}

		return false;
	}

	public function canContinue() : bool
	{
		return $this->targetMate->isAlive() && $this->targetMate->isInLove() && $this->spawnBabyDelay < 60;
	}

	public function onTick() : void
	{
		$this->mob->getLookHelper()->setLookPositionWithEntity($this->targetMate, 10, $this->mob->getVerticalFaceSpeed());
		$this->mob->getNavigator()->tryMoveTo($this->targetMate, $this->speedMultiplier);

		$this->spawnBabyDelay++;

		if ($this->spawnBabyDelay === 60 && $this->mob->distance($this->targetMate) < 9) {
			$this->spawnBaby();
		}
	}

	public function onEnd() : void
	{
		$this->targetMate = null;
		$this->spawnBabyDelay = 0;
	}

	public function getNearbyMate() : ?Animal
	{
		$searchDist = 8.0;
		$list = $this->mob->level->getNearbyEntities($this->mob->getBoundingBox()->expandedCopy($searchDist, $searchDist, $searchDist), $this->mob);
		$distSq = $searchDist * $searchDist;
		$animal = null;

		foreach ($list as $entity) {
			if ($entity instanceof Animal && $entity->isInLove() && !$entity->isBaby() && $entity::NETWORK_ID === $this->mob::NETWORK_ID) {
				$d = $entity->distanceSquared($this->mob);
				if ($d < $distSq) {
					$distSq = $d;
					$animal = $entity;
				}
			}
		}

		return $animal;
	}

	private function spawnBaby() : void
	{
		if ($this->mob->isInLove()) {
			/** @var Mob $baby */
			$baby = Entity::createEntity($this->mob::NETWORK_ID, $this->mob->level, Entity::createBaseNBT($this->mob));
			$baby->setBaby(true);
			$baby->setImmobile(false);
			$baby->spawnToAll();

			$this->targetMate->setInLove(false);
			$this->mob->setInLove(false);
		}
	}
}
