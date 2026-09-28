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

use pocketmine\level\generator\vanilla\entity\Mob;
use pocketmine\level\generator\vanilla\entity\utils\RandomPositionGenerator;

class AvoidMobTypeBehavior extends Behavior
{
	protected $targetEntityClass;
	protected $avoidDistance;
	protected $farSpeed;
	protected $nearSpeed;

	protected $nearestEntity;

	protected $path;

	public function __construct(Mob $mob, string $targetEntityClass, float $avoidDistance, float $farSpeed, float $nearSpeed)
	{
		parent::__construct($mob);

		$this->targetEntityClass = $targetEntityClass;
		$this->avoidDistance = $avoidDistance;
		$this->farSpeed = $farSpeed;
		$this->nearSpeed = $nearSpeed;

		$this->setMutexBits(1);
	}

	public function canStart() : bool
	{
		$nearest = $this->mob->level->getNearestEntity($this->mob, $this->avoidDistance, $this->targetEntityClass);

		if ($nearest !== null) {
			$this->nearestEntity = $nearest;

			$vec = RandomPositionGenerator::findRandomTargetBlockAwayFrom($this->mob, 16, 7, $nearest);

			if ($vec !== null && $nearest->distanceSquared($vec) >= $nearest->distanceSquared($this->mob)) {
				$this->path = $this->mob->getNavigator()->findPath($vec);
				return true;
			}
		}

		return false;
	}

	public function canContinue() : bool
	{
		return $this->mob->getNavigator()->isBusy();
	}

	public function onStart() : void
	{
		$this->mob->getNavigator()->setPath($this->path);
		$this->mob->getNavigator()->setSpeedMultiplier($this->farSpeed);
	}

	public function onTick() : void
	{
		if ($this->mob->distanceSquared($this->nearestEntity) < 49) {
			$this->mob->getNavigator()->setSpeedMultiplier($this->nearSpeed);
		} else {
			$this->mob->getNavigator()->setSpeedMultiplier($this->farSpeed);
		}
	}

	public function onEnd() : void
	{
		$this->nearestEntity = null;
	}
}
