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
use pocketmine\level\generator\vanilla\utils\Random;

abstract class Behavior
{
	/** @var Mob */
	protected $mob;
	/** @var Random */
	protected $random;
	/** @var int */
	protected $mutexBits = 0;

	public function __construct(Mob $mob)
	{
		$this->mob = $mob;
		$this->random = $mob->random;
	}

	abstract public function canStart() : bool;

	public function onStart() : void
	{
	}

	public function canContinue() : bool
	{
		return $this->canStart();
	}

	public function onTick() : void
	{
	}

	public function onEnd() : void
	{
	}

	public function setMutexBits(int $bit) : void
	{
		$this->mutexBits = $bit;
	}

	public function getMutexBits() : int
	{
		return $this->mutexBits;
	}

	public function isMutable() : bool
	{
		return true;
	}
}
