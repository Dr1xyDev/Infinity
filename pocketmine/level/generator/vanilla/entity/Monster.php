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

namespace pocketmine\level\generator\vanilla\entity;

use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\math\Vector3;

use function max;

abstract class Monster extends Mob
{
	protected function isValidLightLevel() : bool
	{
		$x = $this->getFloorX();
		$y = $this->getFloorY();
		$z = $this->getFloorZ();

		if ($this->level->getBlockLightAt($x, $y, $z) < $this->random->nextBoundedInt(32)) {
			$i = max(
				$this->level->getRealBlockSkyLightAt($x, $y + 1, $z),
				$this->level->getRealBlockSkyLightAt($x, $y - 1, $z),
				$this->level->getRealBlockSkyLightAt($x, $y, $z + 1),
				$this->level->getRealBlockSkyLightAt($x, $y, $z - 1),
				$this->level->getRealBlockSkyLightAt($x + 1, $y, $z),
				$this->level->getRealBlockSkyLightAt($x - 1, $y, $z)
			);

			return $i <= $this->random->nextBoundedInt(8);
		}
		return false;
	}

	public function entityBaseTick(int $diff = 1) : bool
	{
		$hasUpdate = parent::entityBaseTick($diff);

		if ($this->isAlive()) {
			if ($this->level->getDifficulty() === Level::DIFFICULTY_PEACEFUL) {
				$this->flagForDespawn();
			}
		}

		return $hasUpdate;
	}

	public function canSpawnHere() : bool
	{
		return $this->level->getDifficulty() !== Level::DIFFICULTY_PEACEFUL && $this->isValidLightLevel();
	}

	public function canDespawn() : bool
	{
		return false;
	}

	public function getBlockPathWeight(Vector3 $pos) : float
	{
		return 0.5 - max(
			$this->level->getRealBlockSkyLightAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ()),
			$this->level->getBlockLightAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ())
		);
	}

}
