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
use pocketmine\level\generator\vanilla\entity\utils\RandomPositionGenerator;
use pocketmine\level\generator\vanilla\math\Vector3;

class RandomStrollBehavior extends Behavior
{
	/** @var float */
	protected $speedMultiplier = 1.0;
	/** @var int */
	protected $chance = 120;
	/** @var Vector3|null */
	protected $targetPos;

	public function __construct(Mob $mob, float $speedMultiplier = 1.0, int $chance = 120)
	{
		parent::__construct($mob);

		$this->speedMultiplier = $speedMultiplier;
		$this->chance = $chance;
		$this->mutexBits = 1;
	}

	public function canStart() : bool
	{
		if ($this->random->nextBoundedInt($this->chance) === 0) {
			$pos = RandomPositionGenerator::findRandomTargetBlock($this->mob, 10, 7);

			if ($pos === null) {
				return false;
			}

			$this->targetPos = $pos;

			return true;
		}

		return false;
	}

	public function canContinue() : bool
	{
		return $this->mob->getNavigator()->isBusy();
	}

	public function onStart() : void
	{
		$this->mob->getNavigator()->tryMoveTo($this->targetPos, $this->speedMultiplier);
	}

	public function onEnd() : void
	{
		$this->targetPos = null;
		$this->mob->getNavigator()->clearPath();
	}
}
