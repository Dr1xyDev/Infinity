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

use pocketmine\level\generator\vanilla\entity\Attribute;
use pocketmine\level\generator\vanilla\entity\Living;
use pocketmine\level\generator\vanilla\entity\Mob;
use pocketmine\Player;

use function floor;

abstract class TargetBehavior extends Behavior
{
	protected $shouldCheckSight;

	private $nearbyOnly;
	private $targetSearchStatus;
	private $targetSearchDelay;
	private $targetUnseenTicks;

	public function __construct(Mob $mob, bool $checkSight, bool $onlyNearby = false)
	{
		parent::__construct($mob);

		$this->shouldCheckSight = $checkSight;
		$this->nearbyOnly = $onlyNearby;
	}

	public function canContinue() : bool
	{
		$target = $this->mob->getTargetEntity();

		if ($target == null) {
			return false;
		} elseif (!$target->isAlive()) {
			return false;
		} else {
			if ($this->mob->distance($target) > $this->getTargetDistance()) {
				return false;
			}

			if ($target instanceof Player && $target->isCreative()) {
				return false;
			}

			return true;
		}
	}

	protected function getTargetDistance() : float
	{
		return $this->mob->getAttributeMap()->getAttribute(Attribute::FOLLOW_RANGE)->getValue();
	}

	public function onStart() : void
	{
		$this->targetSearchStatus = 0;
		$this->targetSearchDelay = 0;
		$this->targetUnseenTicks = 0;
	}

	public function onEnd() : void
	{
		$this->mob->setTargetEntity(null);
	}

	public function isSuitableTarget(Mob $attacker, Living $target, bool $includeInvisibles, bool $checkSight) : bool
	{
		if ($target == null) {
			return false;
		} elseif ($target === $attacker) {
			return false;
		} elseif (!$target->isAlive()) {
			return false;
		} elseif ($target instanceof Player && !$includeInvisibles && $target->isCreative()) {
			return false;
		}

		return !$checkSight || $attacker->canSeeEntity($target);
	}

	public function isSuitableTargetLocal(Living $target, bool $includeInvisibles) : bool
	{
		if (!$this->isSuitableTarget($this->mob, $target, $includeInvisibles, $this->shouldCheckSight)) {
			return false;
		} else {
			if ($this->nearbyOnly) {
				if (--$this->targetSearchDelay <= 0) {
					$this->targetSearchStatus = 0;
				}

				if ($this->targetSearchStatus == 0) {
					$this->targetSearchStatus = $this->canEasilyReach($target) ? 1 : 2;
				}

				if ($this->targetSearchStatus == 2) {
					return false;
				}
			}

			return true;
		}
	}

	private function canEasilyReach(Living $entity) : bool
	{
		$this->targetSearchDelay = 10 + $this->mob->random->nextBoundedInt(5);
		$path = $this->mob->getNavigator()->findPath($entity);

		if ($path == null) {
			return false;
		} else {
			$point = $path->getFinalPathPoint();

			if ($point == null) {
				return false;
			} else {
				$i = $point->x - floor($entity->x);
				$j = $point->y - floor($entity->z);

				return ($i * $i + $j * $j) <= 2.25;
			}
		}
	}
}
