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

use pocketmine\level\generator\vanilla\entity\Tamable;

class StayWhileSittingBehavior extends Behavior
{
	/** @var Tamable */
	protected $mob;
	protected $isSitting = false;

	public function __construct(Tamable $mob)
	{
		parent::__construct($mob);
		$this->mutexBits = 1;
	}

	public function canStart() : bool
	{
		if ($this->mob->isTamed() && !$this->mob->isInsideOfWater() && !($this->mob->fallDistance > 0)) {
			$owner = $this->mob->getOwningEntity();

			return $owner === null ? true : ((($this->mob->distanceSquared($owner) < 144 && ($this->mob->getTargetEntity() !== null && $this->mob->getTargetEntity()->isAlive())) ? false : $this->isSitting));
		}

		return false;
	}

	public function onStart() : void
	{
		$this->mob->getNavigator()->clearPath(true);
		$this->mob->setSitting(true);
	}

	public function onEnd() : void
	{
		$this->mob->setSitting(false);
	}

	public function setSitting(bool $value) : void
	{
		$this->isSitting = $value;
	}
}
