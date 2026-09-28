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
use pocketmine\Player;

class SlimeAttackBehavior extends Behavior
{
	/** @var Slime */
	protected $mob;

	private $attackTime;

	public function __construct(Slime $slime)
	{
		parent::__construct($slime);

		$this->setMutexBits(2);
	}

	public function canStart() : bool
	{
		$target = $this->mob->getTargetEntity();

		return $target === null ? false : (!$target->isAlive() ? false : !($target instanceof Player) || ($target instanceof Player && !$target->isCreative()));
	}

	public function onStart() : void
	{
		$this->attackTime = 300;
	}

	public function canContinue() : bool
	{
		return $this->canStart() && --$this->attackTime > 0;
	}

	public function onTick() : void
	{
		$this->mob->faceEntity($this->mob->getTargetEntity(), 10, 10);
		$this->mob->getMoveHelper()->jumpWithYaw($this->mob->yaw, $this->mob->canDamagePlayer());
	}
}
