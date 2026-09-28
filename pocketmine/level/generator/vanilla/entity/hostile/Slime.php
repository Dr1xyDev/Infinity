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

namespace pocketmine\level\generator\vanilla\entity\hostile;

use pocketmine\level\generator\vanilla\entity\behavior\NearestAttackableTargetBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\SlimeAttackBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\SlimeFloatBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\SlimeKeepOnJumpingBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\SlimeRandomDirectionBehavior;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\entity\helper\EntityMoveHelper;
use pocketmine\level\generator\vanilla\entity\helper\EntitySlimeMoveHelper;
use pocketmine\level\generator\vanilla\entity\Living;
use pocketmine\level\generator\vanilla\entity\Monster;
use pocketmine\level\generator\vanilla\event\entity\EntityDamageByEntityEvent;
use pocketmine\level\generator\vanilla\event\entity\EntityDamageEvent;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\level\biome\BiomeIds;
use pocketmine\level\generator\vanilla\level\particle\GenericParticle;
use pocketmine\level\generator\vanilla\level\particle\Particle;
use pocketmine\level\generator\vanilla\nbt\tag\ByteTag;
use pocketmine\level\generator\vanilla\nbt\tag\IntTag;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;

use function boolval;
use function cos;
use function intval;
use function pi;
use function sin;

class Slime extends Monster
{
	public const NETWORK_ID = self::SLIME;

	public float $height = 0;
	public float $width = 0;

	public $squishAmount = 0;
	public $squishFactor = 0;
	public $prevSquishFactor = 0;

	/** @var EntitySlimeMoveHelper */
	protected $moveHelper;

	private $wasOnGround = false;

	public function getName() : string
	{
		return "Slime";
	}

	/**
	 * @return EntitySlimeMoveHelper
	 */
	public function getMoveHelper() : EntityMoveHelper
	{
		return $this->moveHelper;
	}

	protected function initEntity() : void
	{
		parent::initEntity();

		$this->moveHelper = new EntitySlimeMoveHelper($this);

		if ($this->namedtag->hasTag("Size", IntTag::class)) {
			// Before Submarine used IntTag.
			$this->setSlimeSize($this->namedtag->getInt("Size"));
			$this->namedtag->removeTag("Size");
		} elseif ($this->namedtag->hasTag("Size", ByteTag::class)) {
			$this->setSlimeSize($this->namedtag->getByte("Size"));
		} else {
			$i = $this->random->nextBoundedInt(3);

			if ($i < 2 && $this->random->nextFloat() < 0.5) {
				$i++;
			}

			$this->setSlimeSize(1 << $i);
		}

		$this->wasOnGround = boolval($this->namedtag->getByte("wasOnGround", 0));
	}

	protected function addBehaviors() : void
	{
		$this->behaviorPool->setBehavior(1, new SlimeFloatBehavior($this));
		$this->behaviorPool->setBehavior(2, new SlimeAttackBehavior($this));
		$this->behaviorPool->setBehavior(3, new SlimeRandomDirectionBehavior($this));
		$this->behaviorPool->setBehavior(5, new SlimeKeepOnJumpingBehavior($this));

		$this->targetBehaviorPool->setBehavior(2, new NearestAttackableTargetBehavior($this, Player::class, false));
	}

	protected function setSlimeSize(int $size) : void
	{
		$this->setScale($size);

		$this->updateBoundingBox(0.51 * $size, 0.51 * $size);
		$this->eyeHeight = 0.625 * $size;

		$this->setMaxHealth($size * $size);
		$this->setMovementSpeed(0.2 + 0.1 * $size);
		$this->setHealth($this->getMaxHealth());
	}

	public function getSlimeSize() : int
	{
		return intval($this->getScale());
	}

	public function saveNBT() : void
	{
		parent::saveNBT();

		$this->namedtag->setByte("Size", $this->getSlimeSize());
		$this->namedtag->setByte("wasOnGround", intval($this->wasOnGround));
	}

	protected function getParticleType() : int
	{
		return Particle::TYPE_SLIME;
	}

	public function fall(float $fallDistance) : void
	{
		$i = $this->getSlimeSize();

		for ($j = 0; $j < $i * 8; ++$j) {
			$f = $this->random->nextFloat() * pi() * 2;
			$f1 = $this->random->nextFloat() * 0.5 + 0.5;
			$f2 = sin($f) * $i * 0.5 * $f1;
			$f3 = cos($f) * $i * 0.5 * $f1;

			$this->level->addParticle(new GenericParticle($this->add($f2, 0, $f3), $this->getParticleType()));
		}

		if ($this->makesSoundOnLand()) {
			$this->level->broadcastLevelSoundEvent($this, $this->getSlimeSize() > 1 ? LevelSoundEventPacket::SOUND_SQUISH_BIG : LevelSoundEventPacket::SOUND_SQUISH_SMALL, -1, self::NETWORK_ID);
		}
	}

	public function onUpdate(int $currentTick) : bool
	{
		$this->squishFactor += ($this->squishAmount - $this->squishFactor) * 0.5;
		$this->prevSquishFactor = $this->squishFactor;

		$hasUpdate = parent::onUpdate($currentTick);

		// TODO: Find data property or entity event for squish factor

		if (!$this->isImmobile()) {
			if ($this->onGround && !$this->wasOnGround) {
				$this->squishAmount = -0.5;
			} elseif (!$this->onGround && $this->wasOnGround) {
				$this->squishAmount = 1.0;
			}
		}

		$this->wasOnGround = $this->onGround;

		$this->alterSquishAmount();

		return $hasUpdate;
	}

	protected function alterSquishAmount() : void
	{
		$this->squishAmount *= 0.6;
	}

	/**
	 * Gets the amount of time the slime needs to wait between jumps.
	 */
	public function getJumpDelay() : int
	{
		return $this->random->nextBoundedInt(20) + 10;
	}

	public function onDeath() : void
	{
		parent::onDeath();

		$i = $this->getSlimeSize();

		if ($i > 1) {
			$j = 2 + $this->random->nextBoundedInt(3);

			for ($k = 0; $k < $j; ++$k) {
				$f = (($k % 2) - 0.5) * $i / 4;
				$f1 = (($k / 2) - 0.5) * $i / 4;

				$slime = Entity::createEntity(static::NETWORK_ID, $this->level, Entity::createBaseNBT($this));

				if ($slime instanceof Slime) {
					if ($this->getNameTag() !== "") {
						$slime->setNameTag($this->getNameTag());
					}

					$slime->setImmobile(false);
					$slime->setSlimeSize(intval($i / 2));
					$slime->setPositionAndRotation($this->add($f, 0.5, $f1), $this->random->nextFloat() * 360, 0);

					$slime->spawnToAll();
				}
			}
		}
	}

	// TODO: Collision with IronGolem

	public function onCollideWithPlayer(Player $player) : void
	{
		if ($this->canDamagePlayer()) {
			$this->attackTo($player);
		}
	}

	protected function attackTo(Living $entity) : void
	{
		$i = $this->getSlimeSize();

		if (!$entity->isInvisible() && $this->distanceSquared($entity) < 0.6 * $i * 0.6 * $i) {
			$entity->attack(new EntityDamageByEntityEvent($this, $entity, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $this->getAttackStrength()));
		}
	}

	/**
	 * Indicates weather the slime is able to damage the player (based upon the slime's size)
	 */
	public function canDamagePlayer() : bool
	{
		return $this->getSlimeSize() > 1;
	}

	/**
	 * Checks if the entity's current position is a valid location to spawn this entity.
	 */
	public function canSpawnHere() : bool
	{
		if ($this->level->random->nextBoundedInt(4) !== 1) {
			return false;
		} else {
			$biome = $this->level->getBiome($this->getFloorX(), $this->getFloorZ());

			if (($biome->getId() === BiomeIds::SWAMPLAND && $this->y > 50 && $this->y < 70 && $this->level->random->nextFloat() < 0.5) || ($this->random->nextBoundedInt(10) === 0 && $this->y < 40)) {
				return parent::canSpawnHere();
			}

			return $this->level->getProvider()->getLevelData()->getGenerator() === "flat";
		}
	}

	public function getDrops() : array
	{
		if ($this->getSlimeSize() !== 1) {
			return [];
		}

		$looting = $this->getLootingLevel();
		return [ItemFactory::get(Item::SLIME_BALL, 0, 1 + ($looting > 0 ? $this->random->nextBoundedInt($looting + 1) : 0))];
	}

	public function getXpDropAmount() : int
	{
		return $this->getSlimeSize();
	}

	/**
	 * The speed it takes to move the entityliving's rotationPitch through the faceEntity method. This is only currently
	 * use in wolves.
	 */
	public function getVerticalFaceSpeed() : int
	{
		return 0;
	}

	/**
	 * Returns true if the slime makes a sound when it jumps (based upon the slime's size)
	 */
	public function makesSoundOnJump() : bool
	{
		return $this->getSlimeSize() > 0;
	}

	/**
	 * Returns true if the slime makes a sound when it lands after a jump (based upon the slime's size)
	 */
	public function makesSoundOnLand() : bool
	{
		return $this->getSlimeSize() > 2;
	}

	public function getAttackStrength() : float
	{
		return $this->getSlimeSize();
	}
}
