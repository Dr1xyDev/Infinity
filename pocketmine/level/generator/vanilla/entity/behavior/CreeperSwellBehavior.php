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

use pocketmine\level\generator\vanilla\entity\hostile\Creeper;

class CreeperSwellBehavior extends Behavior
{
	/** @var Creeper */
	protected $mob;

	public function __construct(Creeper $mob)
	{
		parent::__construct($mob);
		$this->mutexBits = 1;
	}

	public function canStart() : bool
	{
		$target = $this->mob->getTargetEntity();
		return $target === null ? false : ($this->mob->isIgnited() || $this->mob->distance($target) < 3);
	}

	public function onTick() : void
	{
		$target = $this->mob->getTargetEntity();
		if ($this->mob->distance($target) > 7 || !$this->mob->canSeeEntity($target)) {
			$this->mob->setIgnited(false);
		} else {
			$this->mob->setIgnited(true);
		}
	}

	public function onEnd() : void
	{
		$this->mob->setTargetEntity(null);
	}
}
