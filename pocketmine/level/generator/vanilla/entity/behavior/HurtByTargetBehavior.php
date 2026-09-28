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

use pocketmine\level\generator\vanilla\entity\Living;
use pocketmine\level\generator\vanilla\entity\Mob;

class HurtByTargetBehavior extends TargetBehavior
{
	protected $alertSameType;
	protected $hurtOwner;
	protected $revengeTimerOld = 0;

	public function __construct(Mob $mob, bool $alertSameType = false, bool $hurtOwner = false)
	{
		parent::__construct($mob, false);

		$this->alertSameType = $alertSameType;
		$this->hurtOwner = $hurtOwner;
	}

	public function canStart() : bool
	{
		$attacker = $this->mob->getRevengeTarget();
		$i = $this->mob->getRevengeTimer();

		return $i !== $this->revengeTimerOld && $attacker instanceof Living && !($this->hurtOwner && $this->mob->getOwningEntity() === $attacker) && $this->isSuitableTargetLocal($attacker, false);
	}

	public function onStart() : void
	{
		$this->mob->setTargetEntity($this->mob->getRevengeTarget());
		$this->revengeTimerOld = $this->mob->getRevengeTimer();

		if ($this->alertSameType) {
			$d = $this->getTargetDistance();

			foreach ($this->mob->level->getNearbyEntities($this->mob->getBoundingBox()->expandedCopy($d, 10, $d), $this->mob) as $entity) {
				if ($entity->getTargetEntity() === null) {
					$entity->setTargetEntity($this->mob->getRevengeTarget());
				}
			}
		}

		parent::onStart();
	}
}
