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

namespace pocketmine\level\generator\vanilla\entity\projectile;

use pocketmine\level\generator\vanilla\block\Block;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\entity\Living;
use pocketmine\level\generator\vanilla\entity\object\EnderCrystal;
use pocketmine\level\generator\vanilla\event\entity\EntityCombustByEntityEvent;
use pocketmine\level\generator\vanilla\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\level\generator\vanilla\event\entity\EntityDamageByEntityEvent;
use pocketmine\level\generator\vanilla\event\entity\EntityDamageEvent;
use pocketmine\level\generator\vanilla\event\entity\ProjectileHitBlockEvent;
use pocketmine\level\generator\vanilla\event\entity\ProjectileHitEntityEvent;
use pocketmine\level\generator\vanilla\event\entity\ProjectileHitEvent;
use pocketmine\level\generator\vanilla\level\ChunkManager;
use pocketmine\level\generator\vanilla\math\RayTraceResult;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\math\VoxelRayTrace;
use pocketmine\level\generator\vanilla\nbt\tag\ByteTag;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\nbt\tag\IntTag;
use pocketmine\level\generator\vanilla\timings\Timings;
use pocketmine\level\generator\vanilla\utils\Utils;

use function assert;
use function atan2;
use function ceil;
use function cos;
use function log;
use function sin;
use function sqrt;

use const M_PI;
use const PHP_INT_MAX;

abstract class Projectile extends Entity
{

	protected float $damage = 0.0;
	protected ?Vector3 $blockHit = null;
	protected ?int $blockHitId = null;
	protected ?int $blockHitData = null;

	public function __construct(ChunkManager $level, CompoundTag $nbt, ?Entity $shootingEntity = null)
	{
		parent::__construct($level, $nbt);
		if ($shootingEntity !== null) {
			$this->setOwningEntity($shootingEntity);
		}
	}

	public function entityShoot(Entity $shooter, float $pitchOffset, float $velocity, float $inaccuracy) : void{
		$xz = cos($shooter->pitch / 180 * M_PI);
		$x = -$xz * sin($shooter->yaw / 180 * M_PI);
		$y = -sin(($shooter->pitch + $pitchOffset) / 180 * M_PI);
		$z = $xz * cos($shooter->yaw / 180 * M_PI);
		$this->shoot(new Vector3($x, $y, $z), $velocity, $inaccuracy);
		$shooterSpeed = $shooter->getSpeed();
		$motion = $this->getMotion();
		$motion->x += $shooterSpeed->x;
		$motion->z += $shooterSpeed->z;
		if (!$shooter->onGround) {
			$motion->y += $shooterSpeed->y;
		}
		$this->setMotion($motion);
	}

	public function shoot(Vector3 $direction, float $velocity, float $inaccuracy) : void
	{
		$len = $direction->length();
		if ($len == 0) {
			return;
		}
		$motion = $direction->asVector3();
		$motion->x /= $len;
		$motion->y /= $len;
		$motion->z /= $len;
		$motion->x += sqrt(-2 * log(Utils::getRandomFloat())) * cos(2 * M_PI * Utils::getRandomFloat()) * 0.0075 * $inaccuracy;
		$motion->y += sqrt(-2 * log(Utils::getRandomFloat())) * cos(2 * M_PI * Utils::getRandomFloat()) * 0.0075 * $inaccuracy;
		$motion->z += sqrt(-2 * log(Utils::getRandomFloat())) * cos(2 * M_PI * Utils::getRandomFloat()) * 0.0075 * $inaccuracy;
		$motion->x *= $velocity;
		$motion->y *= $velocity;
		$motion->z *= $velocity;
		$this->setMotion($motion);
	}

	public function attack(EntityDamageEvent $source) : void
	{
		if ($source->getCause() === EntityDamageEvent::CAUSE_VOID) {
			parent::attack($source);
		}
	}

	protected function initEntity() : void
	{
		parent::initEntity();

		$this->setMaxHealth(1);
		$this->setHealth(1);
		$this->damage = $this->namedtag->getDouble("damage", $this->damage);

		if ($this->namedtag->hasTag("tileX", IntTag::class) && $this->namedtag->hasTag("tileY", IntTag::class) && $this->namedtag->hasTag("tileZ", IntTag::class)) {
			$this->blockHit = new Vector3($this->namedtag->getInt("tileX"), $this->namedtag->getInt("tileY"), $this->namedtag->getInt("tileZ"));
		}

		if ($this->namedtag->hasTag("blockId", IntTag::class)) {
			$this->blockHitId = $this->namedtag->getInt("blockId");
		}

		if ($this->namedtag->hasTag("blockData", ByteTag::class)) {
			$this->blockHitData = $this->namedtag->getByte("blockData");
		}
	}

	public function canCollideWith(Entity $entity) : bool
	{
		return ($entity instanceof Living || $entity instanceof EnderCrystal) && !$this->onGround;
	}

	public function canBeCollidedWith() : bool
	{
		return false;
	}

	/**
	 * Returns the base damage applied on collision. This is multiplied by the projectile's speed to give a result
	 * damage.
	 */
	public function getBaseDamage() : float
	{
		return $this->damage;
	}

	/**
	 * Sets the base amount of damage applied by the projectile.
	 */
	public function setBaseDamage(float $damage) : void
	{
		$this->damage = $damage;
	}

	/**
	 * Returns the amount of damage this projectile will deal to the entity it hits.
	 */
	public function getResultDamage() : int{
		return (int) ceil($this->damage);
	}

	public function saveNBT() : void
	{
		parent::saveNBT();

		$this->namedtag->setDouble("damage", $this->damage);

		if ($this->blockHit !== null) {
			$this->namedtag->setInt("tileX", $this->blockHit->x);
			$this->namedtag->setInt("tileY", $this->blockHit->y);
			$this->namedtag->setInt("tileZ", $this->blockHit->z);

			//we intentionally use different ones to PC because we don't have stringy IDs
			$this->namedtag->setInt("blockId", $this->blockHitId);
			$this->namedtag->setByte("blockData", $this->blockHitData);
		}
	}

	public function getBaseKnockBack() : float
	{
		return Living::DEFAULT_KNOCKBACK_FORCE;
	}

	protected function applyDragBeforeGravity() : bool
	{
		return true;
	}

	public function onNearbyBlockChange() : void
	{
		if ($this->blockHit !== null) {
			$blockIn = $this->level->getBlockAt($this->blockHit->x, $this->blockHit->y, $this->blockHit->z);
			if ($blockIn->getId() !== $this->blockHitId || $blockIn->getDamage() !== $this->blockHitData) {
				$this->blockHit = $this->blockHitId = $this->blockHitData = null;
			}
		}

		parent::onNearbyBlockChange();
	}

	public function shouldAlwaysUpdate() : bool
	{
		return true;
	}

	public function hasMovementUpdate() : bool
	{
		return $this->blockHit === null && parent::hasMovementUpdate();
	}

	public function move(float $dx, float $dy, float $dz) : void
	{
		$this->blocksAround = null;

		Timings::$projectileMove->startTiming();
		Timings::$projectileMoveRayTrace->startTiming();

		$start = $this->asVector3();
		$end = $start->add($dx, $dy, $dz);

		$blockHit = null;
		$entityHit = null;
		$hitResult = null;

		foreach (VoxelRayTrace::betweenPoints($start, $end) as $vector3) {
			$block = $this->level->getBlockAt($vector3->x, $vector3->y, $vector3->z);

			$blockHitResult = $this->calculateInterceptWithBlock($block, $start, $end);
			if ($blockHitResult !== null) {
				$end = $blockHitResult->hitVector;
				$blockHit = $block;
				$hitResult = $blockHitResult;
				break;
			}
		}

		$entityDistance = PHP_INT_MAX;
		$newDiff = $end->subtractVector($start);
		foreach ($this->level->getCollidingEntities($this->boundingBox->addCoord($newDiff->x, $newDiff->y, $newDiff->z)->expand(1, 1, 1), $this) as $entity) {
			if ($entity->getId() === $this->getOwningEntityId() && $this->ticksLived < 5) {
				continue;
			}

			$entityBB = $entity->boundingBox->expandedCopy(0.3, 0.3, 0.3);
			$entityHitResult = $entityBB->calculateIntercept($start, $end);

			if ($entityHitResult === null) {
				continue;
			}

			$distance = $this->distanceSquared($entityHitResult->hitVector);

			if ($distance < $entityDistance) {
				$entityDistance = $distance;
				$entityHit = $entity;
				$hitResult = $entityHitResult;
				$end = $entityHitResult->hitVector;
			}
		}

		Timings::$projectileMoveRayTrace->stopTiming();

		$this->x = $end->x;
		$this->y = $end->y;
		$this->z = $end->z;
		$this->recalculateBoundingBox();

		if ($hitResult !== null) {
			/** @var ProjectileHitEvent|null $ev */
			$ev = null;
			if ($entityHit !== null) {
				$ev = new ProjectileHitEntityEvent($this, $hitResult, $entityHit);
			} elseif ($blockHit !== null) {
				$ev = new ProjectileHitBlockEvent($this, $hitResult, $blockHit);
			} else {
				assert(false, "unknown hit type");
			}

			if ($ev !== null) {
				$ev->call();
				$this->onHit($ev);

				if ($ev instanceof ProjectileHitEntityEvent) {
					$this->onHitEntity($ev->getEntityHit(), $ev->getRayTraceResult());
				} elseif ($ev instanceof ProjectileHitBlockEvent) {
					$this->onHitBlock($ev->getBlockHit(), $ev->getRayTraceResult());
				}
			}

			$this->isCollided = $this->onGround = true;
			$this->motion->x = $this->motion->y = $this->motion->z = 0;
		} else {
			$this->isCollided = $this->onGround = false;
			$this->blockHit = $this->blockHitId = $this->blockHitData = null;

			//recompute angles...
			$f = sqrt(($this->motion->x ** 2) + ($this->motion->z ** 2));
			$this->setRotation(
				atan2($this->motion->x, $this->motion->z) * 180 / M_PI,
				atan2($this->motion->y, $f) * 180 / M_PI
			);
		}

		$this->checkChunks();
		$this->checkBlockCollision();

		Timings::$projectileMove->stopTiming();
	}

	/**
	 * Called by move() when raytracing blocks to discover whether the block should be considered as a point of impact.
	 * This can be overridden by other projectiles to allow altering the blocks which are collided with (for example
	 * some projectiles collide with any non-air block).
	 *
	 * @return RayTraceResult|null the result of the ray trace if successful, or null if no interception is found.
	 */
	protected function calculateInterceptWithBlock(Block $block, Vector3 $start, Vector3 $end) : ?RayTraceResult
	{
		return $block->calculateIntercept($start, $end);
	}

	/**
	 * Called when the projectile hits something. Override this to perform non-target-specific effects when the
	 * projectile hits something.
	 */
	protected function onHit(ProjectileHitEvent $event) : void
	{

	}

	/**
	 * Called when the projectile collides with an Entity.
	 */
	protected function onHitEntity(Entity $entityHit, RayTraceResult $hitResult) : bool
	{
		$damage = $this->getResultDamage();

		$damageEv = null;
		if ($damage >= 0) {
			if ($this->getOwningEntity() === null) {
				$ev = new EntityDamageByEntityEvent($this, $entityHit, EntityDamageEvent::CAUSE_PROJECTILE, $damage, [], $this->getBaseKnockBack());
			} else {
				$ev = new EntityDamageByChildEntityEvent($this->getOwningEntity(), $this, $entityHit, EntityDamageEvent::CAUSE_PROJECTILE, $damage, [], $this->getBaseKnockBack());
			}

			$damageEv = $ev;

			$entityHit->attack($ev);

			if ($this->isOnFire()) {
				$ev = new EntityCombustByEntityEvent($this, $entityHit, 5);
				$ev->call();
				if (!$ev->isCancelled()) {
					$entityHit->setOnFire($ev->getDuration());
				}
			}
		}

		$this->flagForDespawn();

		return ($damageEv !== null ? !$damageEv->isCancelled() : false);
	}

	/**
	 * Called when the projectile collides with a Block.
	 */
	protected function onHitBlock(Block $blockHit, RayTraceResult $hitResult) : void
	{
		$this->blockHit = $blockHit->asVector3();
		$this->blockHitId = $blockHit->getId();
		$this->blockHitData = $blockHit->getDamage();
		$blockHit->onProjectileHit($this, $hitResult);
	}
}
