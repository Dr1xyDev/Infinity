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

namespace pocketmine\level\generator\vanilla\entity\helper;

use pocketmine\level\generator\vanilla\entity\Mob;

use function atan2;
use function floor;
use function pi;

class EntityMoveHelper
{
	protected $targetX = 0;
	protected $targetY = 0;
	protected $targetZ = 0;
	protected $needsUpdate = false;
	protected $speedMultiplier = 1.0;
	/** @var Mob */
	protected $entity;

	public function __construct(Mob $mob)
	{
		$this->entity = $mob;
		$this->targetX = $mob->getFloorX();
		$this->targetY = $mob->getFloorY();
		$this->targetZ = $mob->getFloorZ();
	}

	public function moveTo(float $x, float $y, float $z, float $speedMultiplier) : void
	{
		$this->targetX = $x;
		$this->targetY = $y;
		$this->targetZ = $z;
		$this->speedMultiplier = $speedMultiplier;
		$this->needsUpdate = true;
	}

	public function onUpdate() : void
	{
		$this->entity->setMoveForward(0.0);

		if ($this->needsUpdate) {
			$this->needsUpdate = false;
			$i = floor($this->entity->y + 0.5);
			$d0 = $this->targetX - $this->entity->x;
			$d1 = $this->targetZ - $this->entity->z;
			$d2 = $this->targetY - $i;
			$d3 = $d0 * $d0 + $d2 * $d2 + $d1 * $d1;

			if ($d3 >= 0) {
				$f = atan2($d1, $d0) * 180 / pi() - 90;
				$this->entity->yaw = EntityLookHelper::limitAngle($this->entity->yaw, $f, 30);
				$this->entity->setAIMoveSpeed($sf = $this->speedMultiplier * $this->entity->getMovementSpeed());
				$this->entity->setMoveForward($sf);

				if ($d2 > 0 && ($d0 * $d0 + $d1 * $d1) < 1.0) {
					$this->entity->getJumpHelper()->setJumping(true);
				}
			}
		}
	}

	public function getSpeedMultiplier() : float
	{
		return $this->speedMultiplier;
	}

	public function getTargetX() : int
	{
		return $this->targetX;
	}

	public function getTargetY() : int
	{
		return $this->targetY;
	}

	public function getTargetZ() : int
	{
		return $this->targetZ;
	}
}
