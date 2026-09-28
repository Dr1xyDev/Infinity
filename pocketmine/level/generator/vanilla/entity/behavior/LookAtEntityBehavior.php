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

use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\entity\Mob;
use pocketmine\Player;

class LookAtEntityBehavior extends Behavior
{
	protected float $lookDistance = 6.0;
	protected ?Entity $nearestEntity = null;
	protected int $lookTime = 0;
	protected string $targetClass;

	public function __construct(Mob $mob, string $targetClass, float $lookDistance = 6.0)
	{
		parent::__construct($mob);

		$this->lookDistance = $lookDistance;
		$this->targetClass = $targetClass;
		$this->mutexBits = 2;
	}

	public function canStart() : bool
	{
		if ($this->random->nextFloat() < 0.02) {
			$target = $this->mob->level->getNearestEntity($this->mob->asVector3(), $this->lookDistance, $this->targetClass);

			if ($target !== null) {
				if ($target instanceof Player) {
					if ($target->isSpectator()) {
						return false;
					}
				}

				$this->nearestEntity = $target;

				return true;
			}
		}

		return false;
	}

	public function onStart() : void
	{
		$this->lookTime = 40 + $this->random->nextBoundedInt(40);
	}

	public function canContinue() : bool
	{
		return ($this->nearestEntity->isAlive() && ((!($this->nearestEntity instanceof Player && $this->nearestEntity->isSpectator()) && ((!($this->mob->distanceSquared($this->nearestEntity) > $this->lookDistance ** 2) && $this->lookTime > 0)))));
	}

	public function onTick() : void
	{
		$this->mob->getLookHelper()->setLookPositionWithEntity($this->nearestEntity, 10, $this->mob->getVerticalFaceSpeed());
		$this->lookTime--;
	}

	public function onEnd() : void
	{
		$this->nearestEntity = null;
	}
}
