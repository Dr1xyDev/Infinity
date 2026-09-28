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
use pocketmine\level\generator\vanilla\entity\Living;
use pocketmine\level\generator\vanilla\entity\Tamable;

class OwnerHurtByTargetBehavior extends TargetBehavior
{
	/** @var Tamable */
	protected $mob;
	protected int $revengeTimerOld = 0;
	protected ?Entity $ownerAttacker = null;

	public function __construct(Tamable $mob)
	{
		parent::__construct($mob, false);

		$this->mutexBits = 1;
	}

	public function canStart() : bool
	{
		if ($this->mob->isTamed()) {
			$owner = $this->mob->getOwningEntity();

			if ($owner instanceof Living) {
				$this->ownerAttacker = $owner->getRevengeTarget();
				$i = $owner->getRevengeTimer();

				return $i !== $this->revengeTimerOld && $this->ownerAttacker instanceof Living && $this->isSuitableTargetLocal($this->ownerAttacker, false);
			}
		}

		return false;
	}

	public function onStart() : void
	{
		$this->mob->setTargetEntity($this->ownerAttacker);
		$owner = $this->mob->getOwningEntity();

		if ($owner instanceof Living) {
			$this->revengeTimerOld = $owner->getRevengeTimer();
		}

		parent::onStart();
	}
}
