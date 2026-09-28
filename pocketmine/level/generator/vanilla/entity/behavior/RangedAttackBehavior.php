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
use pocketmine\level\generator\vanilla\entity\RangedAttackerMob;
use pocketmine\level\generator\vanilla\event\entity\EntityDamageByEntityEvent;
use pocketmine\level\generator\vanilla\event\entity\EntityDamageEvent;

use function floor;
use function sqrt;

class RangedAttackBehavior extends Behavior
{
	protected float $speedMultiplier = 1.0;
	protected int $minAttackTime;
	protected int $maxAttackTime;
	protected float $maxAttackDistanceIn;
	protected float $maxAttackDistance;
	protected int $rangedAttackTime = 0;
	protected int $targetSeenTicks = 0;
	protected ?Entity $attackTarget = null;

	public function __construct(Mob $mob, float $speedMultiplier, int $minAttackTime, int $maxAttackTime, float $maxAttackDistanceIn)
	{
		parent::__construct($mob);

		$this->speedMultiplier = $speedMultiplier;
		$this->minAttackTime = $minAttackTime;
		$this->maxAttackTime = $maxAttackTime;
		$this->maxAttackDistanceIn = $maxAttackDistanceIn;
		$this->maxAttackDistance = $maxAttackDistanceIn ** 2;
		$this->rangedAttackTime = -1;
		$this->mutexBits = 3;
	}

	public function canStart() : bool
	{
		if (($target = $this->mob->getTargetEntity()) !== null) {
			$this->attackTarget = $target;

			return true;
		}

		return false;
	}

	public function canContinue() : bool
	{
		return $this->canStart() || $this->mob->getNavigator()->isBusy();
	}

	public function onEnd() : void
	{
		$this->attackTarget = null;
		$this->targetSeenTicks = 0;
		$this->rangedAttackTime = -1;
	}

	public function onTick() : void
	{
		if (!$this->mob->isAlive() || $this->attackTarget === null) {
			return;
		}

		$dist = $this->mob->distanceSquared($this->attackTarget);

		if ($flag = $this->mob->canSeeEntity($this->attackTarget)) {
			++$this->targetSeenTicks;
		} else {
			$this->targetSeenTicks = 0;
		}

		if ($dist <= $this->maxAttackDistance && $this->targetSeenTicks >= 20) {
			$this->mob->getNavigator()->clearPath();
		} else {
			$this->mob->getNavigator()->tryMoveTo($this->attackTarget, $this->speedMultiplier);
		}

		$this->mob->getLookHelper()->setLookPositionWithEntity($this->attackTarget, 30, 30);

		if (--$this->rangedAttackTime <= 0) {
			if ($dist > $this->maxAttackDistance || !$flag) {
				return;
			}

			$f = sqrt($dist) / $this->maxAttackDistanceIn;
			if ($f > 1) {
				$f = 1;
			}
			if ($f < 0.1) {
				$f = 0.1;
			}

			if ($this->mob instanceof RangedAttackerMob) {
				$this->mob->onRangedAttackToTarget($this->attackTarget, $f);
			}

			if ($dist < 1) {
				$this->attackTarget->attack(new EntityDamageByEntityEvent($this->mob, $this->attackTarget, EntityDamageEvent::CAUSE_CUSTOM, $this->mob->getAttackDamage()));
			}

			$this->rangedAttackTime = (int) floor($f * ($this->maxAttackTime - $this->minAttackTime) + $this->minAttackTime);
		}
	}
}
