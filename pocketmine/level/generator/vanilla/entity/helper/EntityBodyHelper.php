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

use function abs;
use function max;

class EntityBodyHelper
{
	/** @var Mob */
	protected $entity;
	protected $rotationTickCounter = 0;
	protected $prevRenderYawHead = 0.0;

	public function __construct(Mob $mob)
	{
		$this->entity = $mob;
	}

	/**
	 * Update the Head and Body rendering angles
	 */
	public function onUpdate() : void
	{
		if ($this->entity->getMotion()->lengthSquared() > 0.0025) {
			$this->entity->yawOffset = $this->entity->yaw;
			$this->entity->headYaw = $this->computeAngleWithBound($this->entity->yawOffset, $this->entity->headYaw, 75);
			$this->prevRenderYawHead = $this->entity->headYaw;
			$this->rotationTickCounter = 0;
		} else {
			$f = 75;

			if (abs($this->entity->headYaw - $this->prevRenderYawHead) > 15) {
				$this->rotationTickCounter = 0;
				$this->prevRenderYawHead = $this->entity->headYaw;
			} else {
				$this->rotationTickCounter++;

				if ($this->rotationTickCounter > 10) {
					$f = max(1 - ($this->rotationTickCounter - 10) / 10, 0) * 75;
				}
			}

			$this->entity->yawOffset = $this->computeAngleWithBound($this->entity->headYaw, $this->entity->yawOffset, $f);
		}
	}

	private function computeAngleWithBound(float $angle1, float $angle2, float $angleMax) : float
	{
		$f = EntityLookHelper::wrapAngleTo180($angle1 - $angle2);

		if ($f < -$angleMax) {
			$f = -$angleMax;
		}

		if ($f >= $angleMax) {
			$f = $angleMax;
		}

		return $angle1 - $f;
	}
}
