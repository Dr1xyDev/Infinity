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

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\block\Water;
use pocketmine\level\generator\vanilla\entity\Mob;
use pocketmine\level\generator\vanilla\math\Vector3;

class FleeSunBehavior extends Behavior
{
	/** @var float */
	protected $speedMultiplier = 1.0;
	/** @var Vector3 */
	protected $shelter;

	public function __construct(Mob $mob, float $speedMultiplier = 1.0)
	{
		parent::__construct($mob);

		$this->speedMultiplier = $speedMultiplier;
		$this->mutexBits = 1;
	}

	public function canStart() : bool
	{
		if ($this->mob->isOnFire() && $this->mob->level->isDayTime() && $this->mob->level->canSeeSky($this->mob->floor())) {
			$this->shelter = $this->findPossibleShelter();

			return $this->shelter !== null;
		}

		return false;
	}

	public function onStart() : void
	{
		$this->mob->getNavigator()->tryMoveTo($this->shelter, $this->speedMultiplier);
	}

	public function canContinue() : bool
	{
		return $this->mob->getNavigator()->isBusy();
	}

	public function findPossibleShelter() : ?Block
	{
		for ($i = 0; $i < 10; $i++) {
			$block = $this->mob->level->getBlock($this->mob->add($this->random->nextBoundedInt(20) - 10, $this->random->nextBoundedInt(6) - 3, $this->random->nextBoundedInt(20) - 10));
			$canSeeSky = $this->mob->level->canSeeSky($block);
			if (!$block->isSolid() && ($block instanceof Water || !$canSeeSky)) {
				return $block;
			}
		}

		return null;
	}
}
