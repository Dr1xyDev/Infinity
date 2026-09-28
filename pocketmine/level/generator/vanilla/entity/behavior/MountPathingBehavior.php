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

use pocketmine\level\generator\vanilla\entity\passive\AbstractHorse;
use pocketmine\level\generator\vanilla\entity\utils\RandomPositionGenerator;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ActorEventPacket;

class MountPathingBehavior extends Behavior
{
	protected $rideTime = 0;
	/** @var AbstractHorse */
	protected $mob;

	public function __construct(AbstractHorse $mob)
	{
		parent::__construct($mob);

		$this->mutexBits = 7;
	}

	public function canStart() : bool
	{
		return $this->mob->getRiddenByEntity() !== null;
	}

	public function onTick() : void
	{
		if (!$this->mob->isTamed()) {
			if ($this->rideTime > 100 && !$this->mob->isRearing()) {
				if ($this->rideTime === 100) {
					$pos = RandomPositionGenerator::findRandomTargetBlock($this->mob, 10, 7);

					if ($pos !== null) {
						$this->mob->getNavigator()->tryMoveTo($pos, 1, $this->mob->distanceSquared($pos) + 2);
					}
				}

				if ($this->mob->random->nextBoundedInt(1) === 0) {
					$this->mob->setInLove(true);
					$this->mob->setTamed(true);
				} else {
					$this->mob->setRearing(true);
				}
			} elseif ($this->rideTime > 120) {
				$this->mob->broadcastEntityEvent(ActorEventPacket::TAME_FAIL);
				$this->mob->throwRider();
				$this->mob->setRearing(false);
			}

			$this->rideTime++;
			$this->mutexBits = 2;
		}
	}

	public function onEnd() : void
	{
		$this->rideTime = 0;

		$this->mob->setRearing(false);
		$this->mob->throwRider();
	}
}
