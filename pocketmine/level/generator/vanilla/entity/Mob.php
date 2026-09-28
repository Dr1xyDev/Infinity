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

use pocketmine\level\generator\vanilla\block\Liquid;
use pocketmine\level\generator\vanilla\entity\behavior\BehaviorPool;
use pocketmine\level\generator\vanilla\entity\helper\EntityBodyHelper;
use pocketmine\level\generator\vanilla\entity\helper\EntityJumpHelper;
use pocketmine\level\generator\vanilla\entity\helper\EntityLookHelper;
use pocketmine\level\generator\vanilla\entity\helper\EntityMoveHelper;
use pocketmine\level\generator\vanilla\entity\pathfinder\EntityNavigator;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\timings\Timings;

use function abs;
use function boolval;
use function cos;
use function deg2rad;
use function floor;
use function intval;
use function sin;

abstract class Mob extends Living
{
	public const MOTION_THRESHOLD = 0.0005;

	/** @var BehaviorPool */
	protected $behaviorPool;
	/** @var BehaviorPool */
	protected $targetBehaviorPool;
	/** @var EntityNavigator */
	protected $navigator;
	/** @var Entity[] */
	protected $seenEntities = [];
	/** @var Entity[] */
	protected $unseenEntities = [];
	/** @var Vector3 */
	protected $homePosition;

	protected $landMovementFactor = 0.0;
	protected $jumpMovementFactor = 0.02;

	protected $isJumping = false;
	protected $jumpTicks = 0;

	/** @var EntityMoveHelper */
	protected $moveHelper;
	/** @var EntityJumpHelper */
	protected $jumpHelper;
	/** @var EntityBodyHelper */
	protected $bodyHelper;
	/** @var EntityLookHelper */
	protected $lookHelper;

	protected bool $hasPlayerAround = false;
	protected int $lastPlayerAroundCheck = -1;
	protected int $sightCacheTick = 0;
	protected int $despawnCheckTick = 0;
	protected int $entityCollisionTick = 0;

	public $yawOffset = 0.0;

	public function getHomePosition() : Vector3
	{
		return $this->homePosition;
	}

	public function setHomePosition(Vector3 $homePosition) : void
	{
		$this->homePosition = $homePosition;
	}

	public function getAIMoveSpeed() : float
	{
		return $this->landMovementFactor;
	}

	public function setAIMoveSpeed(float $value) : void
	{
		$this->landMovementFactor = $value;
	}

	public function isJumping() : bool
	{
		return $this->isJumping;
	}

	public function setJumping(bool $isJumping) : void
	{
		$this->isJumping = $isJumping;
	}

	public function getMoveHelper() : EntityMoveHelper
	{
		return $this->moveHelper;
	}

	public function getJumpHelper() : EntityJumpHelper
	{
		return $this->jumpHelper;
	}

	public function getBodyHelper() : EntityBodyHelper
	{
		return $this->bodyHelper;
	}

	public function getLookHelper() : EntityLookHelper
	{
		return $this->lookHelper;
	}

	protected function initEntity() : void
	{
		parent::initEntity();

		$this->targetBehaviorPool = new BehaviorPool();
		$this->behaviorPool = new BehaviorPool();
		$this->navigator = new EntityNavigator($this);
		$this->moveHelper = new EntityMoveHelper($this);
		$this->jumpHelper = new EntityJumpHelper($this);
		$this->lookHelper = new EntityLookHelper($this);
		$this->bodyHelper = new EntityBodyHelper($this);

		$this->addBehaviors();
		$this->setImmobile(boolval($this->namedtag->getByte("Immobile", $this->namedtag->getByte("NoAI", 1))));

		$this->stepHeight = 0.6;
	}

	public function saveNBT() : void
	{
		parent::saveNBT();

		$this->namedtag->setByte("Immobile", intval($this->isImmobile()));
	}

	public function onUpdate(int $currentTick) : bool
	{
		if ($this->closed) {
			return false;
		}

		if ($this->isAlive() && ($this->lastPlayerAroundCheck === -1 || $this->lastPlayerAroundCheck + 20 < $currentTick)) {
			$this->hasPlayerAround = false;
			foreach ($this->getViewers() as $viewer) {
				if ($viewer instanceof Player && $this->distanceSquared($viewer) < 1024) { // 32^2
					$this->hasPlayerAround = true;
					break;
				}
			}
			$this->lastPlayerAroundCheck = $currentTick;
		}

		if ($this->isAlive() && !$this->hasPlayerAround) { //freeze entity till no player around
			return true;
		}

		$hasUpdate = false;

		if (!$this->isImmobile()) {
			if ($this->jumpTicks > 0) {
				$this->jumpTicks--;
			}

			$hasUpdate = $this->onBehaviorUpdate();
		}

		return parent::onUpdate($currentTick) || $hasUpdate;
	}

	public function hasMovementUpdate() : bool
	{
		return parent::hasMovementUpdate()
			|| abs($this->yaw - $this->lastYaw) > 0
			|| abs($this->pitch - $this->lastPitch) > 0
			|| abs($this->headYaw - $this->lastHeadYaw) > 0;
	}

	protected function onBehaviorUpdate() : bool
	{
		$hasUpdate = false;

		Timings::$mobBehaviorUpdate->startTiming();
		$hasUpdate |= $this->targetBehaviorPool->onUpdate();
		$hasUpdate |= $this->behaviorPool->onUpdate();
		Timings::$mobBehaviorUpdate->stopTiming();

		Timings::$mobNavigationUpdate->startTiming();
		$hasUpdate |= $this->navigator->onNavigateUpdate();
		Timings::$mobNavigationUpdate->stopTiming();

		$this->moveHelper->onUpdate();
		$this->lookHelper->onUpdate();
		$this->jumpHelper->doJump();

		if (++$this->sightCacheTick >= 5) {
			$this->clearSightCache();
			$this->sightCacheTick = 0;
		}

		if ($this->isJumping) {
			if ($this->isInsideOfWater()) {
				$this->handleWaterJump();
			} elseif ($this->isInsideOfLava()) {
				$this->handleLavaJump();
			} elseif ($this->onGround && $this->jumpTicks === 0) {
				$this->jump();
				$this->jumpTicks = 10;
			}
		} else {
			$this->jumpTicks = 0;
		}

		$this->moveStrafing *= 0.98;
		$this->moveForward *= 0.98;
		$this->moveWithHeading($this->moveStrafing, $this->moveForward);

		$this->bodyHelper->onUpdate();

		if (++$this->despawnCheckTick >= 100) {
			$this->tryToDespawn();
			$this->despawnCheckTick = 0;
		}

		return (bool) $hasUpdate;
	}

	public function canSeeEntity(Entity $target) : bool
	{
		$targetId = $target->getId();
		if (isset($this->unseenEntities[$targetId])) {
			return false;
		} elseif (isset($this->seenEntities[$targetId])) {
			return true;
		} else {
			// TODO: Fix seen from corners
			$canSee = $this->getNavigator()->isClearBetweenPoints($this, $target);

			if ($canSee) {
				$this->seenEntities[$targetId] = true;
			} else {
				$this->unseenEntities[$targetId] = true;
			}

			return $canSee;
		}
	}

	public function clearSightCache() : void
	{
		$this->seenEntities = [];
		$this->unseenEntities = [];
	}

	protected function addBehaviors() : void
	{

	}

	public function getBehaviorPool() : BehaviorPool
	{
		return $this->behaviorPool;
	}

	public function getTargetBehaviorPool() : BehaviorPool
	{
		return $this->targetBehaviorPool;
	}

	public function handleWaterJump() : void
	{
		$this->motion->y += 0.04;
	}

	public function handleLavaJump() : void
	{
		$this->motion->y += 0.04;
	}

	public function getNavigator() : EntityNavigator
	{
		return $this->navigator;
	}

	public function getVerticalFaceSpeed() : int
	{
		return 40;
	}

	protected function checkEntityCollision() : void
	{
		if (++$this->entityCollisionTick % 4 === 0) {
			parent::checkEntityCollision();
		}
	}

	public function canBePushed() : bool
	{
		return !$this->isImmobile();
	}

	public function updateLeashedState() : void
	{
		parent::updateLeashedState();

		$entity = $this->getLeashedToEntity();
		if ($this->isLeashed() && $entity !== null) {
			$f = $this->distance($entity);

			if ($this instanceof Tamable && $this->isSitting()) {
				if ($f > 10) {
					$this->clearLeashed(true, true);
				}
				return;
			}

			if ($f > 4) {
				$this->navigator->tryMoveTo($entity, 1.0);
			}

			if ($f > 6) {
				$d0 = ($entity->x - $this->x) / $f;
				$d1 = ($entity->y - $this->y) / $f;
				$d2 = ($entity->z - $this->z) / $f;

				$this->motion->x += $d0 * abs($d0) * 0.4;
				$this->motion->y += $d1 * abs($d1) * 0.4;
				$this->motion->z += $d2 * abs($d2) * 0.4;
			}

			if ($f > 10) {
				$this->clearLeashed(true, true);
			}
		}
	}

	protected function tryToDespawn() : void
	{
		if ($this->canDespawn() && !$this->hasPlayerAround && $this->level->getNearestEntity($this, 128, Player::class, true) === null) {
			$this->flagForDespawn();
		}
	}

	public function canDespawn() : bool
	{
		return !$this->isImmobile() && !$this->isLeashed() && $this->getOwningEntityId() === null;
	}

	public function getBlockPathWeight(Vector3 $pos) : float
	{
		return 0.0;
	}

	public function canSpawnHere() : bool
	{
		return parent::canSpawnHere() && $this->getBlockPathWeight($this) > 0;
	}

	public function moveWithHeading(float $strafe, float $forward)
	{
		if (!$this->isInsideOfWater()) {
			if (!$this->isInsideOfLava()) {
				$f4 = 0.91;

				if ($this->onGround) {
					$f4 *= $this->level->getBlock($this->down())->getFrictionFactor();
				}

				$f = 0.16277136 / ($f4 * $f4 * $f4);

				if ($this->onGround) {
					$f5 = $this->getAIMoveSpeed() * $f;
				} else {
					$f5 = $this->jumpMovementFactor;
				}

				$this->moveFlying($strafe, $forward, $f5);
			} else {
				$this->moveFlying($strafe, $forward, 0.02);

				if ($this->isCollidedHorizontally && $this->level->getBlock($this) instanceof Liquid) {
					$this->motion->y = 0.3;
				}
			}
		} else {
			$f2 = 0.02;
			$f3 = 0; // TODO: check enchantment

			if ($f3 > 3.0) {
				$f3 = 3.0;
			}

			if (!$this->onGround) {
				$f3 *= 0.5;
			}

			if ($f3 > 0.0) {
				$f2 += ($this->getAIMoveSpeed() * 1.0 - $f2) * $f3 / 3.0;
			}

			$this->moveFlying($strafe, $forward, $f2);
			if ($this->isCollidedHorizontally && $this->level->getBlock($this) instanceof Liquid) {
				$this->motion->y = 0.3;
			}
		}
	}

	protected function onMovementUpdate() : void
	{
		if ($this->clientMoveTicks === 0) {
			$f = 1 - $this->drag;

			$this->motion->x *= $f;
			$this->motion->y *= $f;
			$this->motion->z *= $f;
		}

		$this->checkMotion();

		if ($this->motion->x != 0 || $this->motion->y != 0 || $this->motion->z != 0) {
			$this->move($this->motion->x, $this->motion->y, $this->motion->z);
		}

		$this->tryChangeMovement();
	}

	protected function tryChangeMovement() : void
	{
		if ($this->isInsideOfWater()) {
			$this->motion->x *= 0.8;
			$this->motion->y *= 0.8;
			$this->motion->z *= 0.8;

			$this->motion->y -= 0.02;
		} elseif ($this->isInsideOfLava()) {
			$this->motion->x *= 0.5;
			$this->motion->y *= 0.5;
			$this->motion->z *= 0.5;

			$this->motion->y -= 0.02;
		} else {
			$friction = 0.91;

			if (!$this->onGround || $this->forceMovementUpdate) {
				$this->applyGravity();
			}

			$this->motion->y *= $friction;

			if ($this->onGround) {
				$friction *= $this->level->getBlockAt((int) floor($this->x), (int) floor($this->y - 1), (int) floor($this->z))->getFrictionFactor();
			}

			$this->motion->x *= $friction;
			$this->motion->z *= $friction;
		}
	}

	public function getLookVector() : Vector3
	{
		$y = -sin(deg2rad($this->pitch));
		$xz = cos(deg2rad($this->pitch));
		$x = -$xz * sin(deg2rad($this->headYaw));
		$z = $xz * cos(deg2rad($this->headYaw));

		return (new Vector3($x, $y, $z))->normalize();
	}

	public function faceEntity(Entity $entity, float $dxz, float $dy) : void
	{
		if ($entity instanceof Living) {
			$d2 = $entity->y + $entity->getEyeHeight() - ($this->y + $this->getEyeHeight());
		} else {
			$d2 = ($entity->y + $entity->getBoundingBox()->maxY) / 2 - ($this->y + $this->getEyeHeight());
		}

		$this->lookAt(new Vector3($entity->x, $d2, $entity->z));

		$this->yaw = EntityLookHelper::updateRotation($this->lastYaw, $this->yaw, $dxz);
		$this->pitch = EntityLookHelper::updateRotation($this->lastPitch, $this->pitch, $dy);
	}
}
