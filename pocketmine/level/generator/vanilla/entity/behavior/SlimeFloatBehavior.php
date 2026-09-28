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

use pocketmine\level\generator\vanilla\entity\hostile\Slime;

class SlimeFloatBehavior extends Behavior
{
	/** @var Slime */
	protected $mob;

	public function __construct(Slime $slime)
	{
		parent::__construct($slime);

		$this->setMutexBits(5);
		$this->mob->setSwimmer(true);
	}

	public function canStart() : bool
	{
		return $this->mob->isInsideOfWater() || $this->mob->isInsideOfLava();
	}

	public function onTick() : void
	{
		if ($this->random->nextFloat() < 0.8) {
			$this->mob->getJumpHelper()->setJumping(true);
		}

		$this->mob->getMoveHelper()->setSpeed(1.2);
	}
}
