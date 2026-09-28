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
use pocketmine\level\generator\vanilla\entity\Tamable;
use pocketmine\Player;

class FollowOwnerBehavior extends Behavior
{
	protected float $speedMultiplier;
	protected int $followDelay = 0;
	/** @var Tamable */
	protected $mob;
	protected float $minDistance;
	protected float $maxDistance;
	protected ?Entity $owner = null;

	public function __construct(Tamable $mob, float $speedMultiplier, float $minDistance, float $maxDistance)
	{
		parent::__construct($mob);

		$this->minDistance = $minDistance;
		$this->maxDistance = $maxDistance;
		$this->speedMultiplier = $speedMultiplier;

		$this->mutexBits = 3;
	}

	public function canStart() : bool
	{
		$owner = $this->mob->getOwningEntity();

		if ($owner !== null && !($owner instanceof Player && $owner->isSpectator()) && !$this->mob->isSitting() && $this->mob->distanceSquared($owner) > ($this->minDistance ** 2)) {
			$this->owner = $owner;

			return true;
		}

		return false;
	}

	public function onStart() : void
	{
		$this->followDelay = 0;
		$this->mob->getNavigator()->setAvoidsWater(false);
	}

	public function canContinue() : bool
	{
		return $this->mob->getNavigator()->isBusy() && $this->mob->distanceSquared($this->owner) > ($this->maxDistance ** 2) && !$this->mob->isSitting();
	}

	public function onTick() : void
	{
		$this->mob->getLookHelper()->setLookPositionWithEntity($this->owner, 10, $this->mob->getVerticalFaceSpeed());

		if (!$this->mob->isSitting()) {
			if (--$this->followDelay <= 0) {
				$this->followDelay = 10;

				$this->mob->getNavigator()->tryMoveTo($this->owner, $this->speedMultiplier);

				if (!$this->mob->isLeashed()) {
					if ($this->mob->distanceSquared($this->owner) > 144) {
						$this->mob->teleport($this->owner);
						$this->mob->getNavigator()->clearPath(true);
					}
				}
			}
		}
	}

	public function onEnd() : void
	{
		$this->mob->getNavigator()->clearPath(true);
		$this->owner = null;
		$this->mob->getNavigator()->setAvoidsWater(true);
	}
}
