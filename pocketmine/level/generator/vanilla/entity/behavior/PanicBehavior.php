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

class PanicBehavior extends RandomStrollBehavior
{
	public function __construct(Mob $mob, float $speedMultiplier = 1.0)
	{
		parent::__construct($mob, $speedMultiplier, 0);
	}

	public function canStart() : bool
	{
		if ($this->mob->getRevengeTarget() !== null || $this->mob->isOnFire()) {
			$this->targetPos = RandomPositionGenerator::findRandomTargetBlock($this->mob, 5, 4);

			if ($this->targetPos !== null) {
				return true;
			}
		}
		return false;
	}
}
