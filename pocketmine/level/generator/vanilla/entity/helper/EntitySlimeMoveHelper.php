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

use pocketmine\level\generator\vanilla\entity\hostile\Slime;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelSoundEventPacket;

class EntitySlimeMoveHelper extends EntityMoveHelper
{
	/** @var Slime */
	protected $entity;

	protected $targetYaw = 0;
	protected $jumpTimer = 0;
	protected $speedJump = false;

	public function __construct(Slime $slime)
	{
		parent::__construct($slime);
	}

	public function jumpWithYaw(float $yaw, bool $speedJump)
	{
		$this->targetYaw = $yaw;
		$this->speedJump = $speedJump;
	}

	public function setSpeed(float $speedIn)
	{
		$this->speedMultiplier = $speedIn;
		$this->needsUpdate = true;
	}

	public function onUpdate() : void
	{
		$this->entity->yaw = EntityLookHelper::limitAngle($this->entity->yaw, $this->targetYaw, 30.0);
		$this->entity->headYaw = $this->entity->yaw;
		$this->entity->yawOffset = $this->entity->yaw;

		if (!$this->needsUpdate) {
			$this->entity->setMoveForward(0);
		} else {
			$this->needsUpdate = false;

			if ($this->entity->onGround) {
				$this->entity->setAIMoveSpeed($s = $this->speedMultiplier * $this->entity->getMovementSpeed());
				$this->entity->setMoveForward($s);

				if ($this->jumpTimer-- <= 0) {
					$this->jumpTimer = $this->entity->getJumpDelay();

					if ($this->speedJump) {
						$this->jumpTimer /= 3;
					}

					$this->entity->getJumpHelper()->setJumping(true);

					if ($this->entity->makesSoundOnJump()) {
						$this->entity->level->broadcastLevelSoundEvent($this->entity, $this->entity->getSlimeSize() > 1 ? LevelSoundEventPacket::SOUND_SQUISH_BIG : LevelSoundEventPacket::SOUND_SQUISH_SMALL, -1, $this->entity::NETWORK_ID);
					}
				} else {
					$this->entity->setMoveStrafing(0);
					$this->entity->setMoveForward(0);
					$this->entity->setAIMoveSpeed(0);
				}
			} else {
				$this->entity->setAIMoveSpeed($s = $this->speedMultiplier * $this->entity->getMovementSpeed());
				$this->entity->setMoveForward($s);
			}
		}
	}
}
