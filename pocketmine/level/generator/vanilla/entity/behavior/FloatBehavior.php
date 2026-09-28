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

class FloatBehavior extends Behavior
{
	private int $tick = 0;

	public function __construct(Mob $mob)
	{
		parent::__construct($mob);
		$this->mutexBits = 4;
	}

	public function canStart() : bool
	{
		return $this->mob->isInsideOfWater();
	}

	public function onStart() : void
	{
		$this->mob->setSwimmer(true);
	}

	public function onEnd() : void
	{
		$this->mob->setSwimmer(false);
	}

	public function onTick() : void
	{
		if ($this->random->nextFloat() < 0.8) {
			$this->mob->getJumpHelper()->setJumping(true);
		}
	}
}
