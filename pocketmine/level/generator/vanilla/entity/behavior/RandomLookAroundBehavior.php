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

use function cos;
use function pi;
use function sin;

class RandomLookAroundBehavior extends Behavior
{
	protected float $lookX = 0;
	protected float $lookZ = 0;
	protected int $idleTime = 0;

	public function __construct(Mob $mob)
	{
		parent::__construct($mob);
		$this->mutexBits = 3;
	}

	public function canStart() : bool
	{
		return $this->random->nextFloat() < 0.02;
	}

	public function onStart() : void
	{
		$d0 = (pi() * 2) * $this->random->nextFloat();
		$this->lookX = cos($d0);
		$this->lookZ = sin($d0);
		$this->idleTime = 20 + $this->random->nextBoundedInt(20);
	}

	public function canContinue() : bool
	{
		return $this->idleTime > 0;
	}

	public function onTick() : void
	{
		$this->idleTime--;
		$this->mob->getLookHelper()->setLookPosition($this->mob->x + $this->lookX, $this->mob->y + $this->mob->getEyeHeight(), $this->mob->z + $this->lookZ, 10, $this->mob->getVerticalFaceSpeed());
	}
}
