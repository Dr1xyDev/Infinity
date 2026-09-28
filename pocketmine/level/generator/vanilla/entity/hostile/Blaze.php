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

use pocketmine\level\generator\vanilla\entity\behavior\HurtByTargetBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\LookAtPlayerBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\NearestAttackableTargetBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\RandomLookAroundBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\RandomStrollBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\RangedAttackBehavior;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\entity\Monster;
use pocketmine\level\generator\vanilla\entity\projectile\SmallFireball;
use pocketmine\level\generator\vanilla\entity\RangedAttackerMob;
use pocketmine\level\generator\vanilla\event\entity\EntityDamageEvent;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\Player;

use function rand;

class Blaze extends Monster implements RangedAttackerMob
{
	public const NETWORK_ID = self::BLAZE;

	public float $height = 1.8;
	public float $width = 0.6;

	public function initEntity() : void
	{
		$this->setMaxHealth(20);
		$this->setMovementSpeed(0.23000000417232513);
		$this->setFollowRange(35);

		parent::initEntity();
	}

	public function getName() : string
	{
		return "Blaze";
	}

	protected function addBehaviors() : void
	{
		$this->targetBehaviorPool->setBehavior(0, new HurtByTargetBehavior($this));
		$this->targetBehaviorPool->setBehavior(1, new NearestAttackableTargetBehavior($this, Player::class));

		$this->behaviorPool->setBehavior(1, new RangedAttackBehavior($this, 1.0, 30, 60, 10));
		$this->behaviorPool->setBehavior(2, new RandomStrollBehavior($this, 1.0));
		$this->behaviorPool->setBehavior(3, new LookAtPlayerBehavior($this, 8.0));
		$this->behaviorPool->setBehavior(4, new RandomLookAroundBehavior($this));
	}

	public function getXpDropAmount() : int
	{
		return 10;
	}

	public function getDrops() : array
	{
		$looting = $this->getLootingLevel();
		$drops = [];

		$blazeRods = rand(0, 1 + $looting);
		if ($blazeRods > 0) {
			$drops[] = ItemFactory::get(Item::BLAZE_ROD, 0, $blazeRods);
		}

		$glowstoneDust = rand(0, 2 + $looting);
		if ($glowstoneDust > 0) {
			$drops[] = ItemFactory::get(Item::GLOWSTONE_DUST, 0, $glowstoneDust);
		}

		return $drops;
	}

	public function onBehaviorUpdate() : bool
	{
		$hasUpdate = parent::onBehaviorUpdate();

		if ($this->isWet()) {
			$this->attack(new EntityDamageEvent($this, EntityDamageEvent::CAUSE_DROWNING, 1));
		}

		$target = $this->getTargetEntity();
		if ($target !== null && $target->y + $target->getEyeHeight() > $this->y + $this->getEyeHeight()) {
			$this->motion->y += (0.30000001192092896 - $this->motion->y) * 0.30000001192092896;
		}

		return $hasUpdate;
	}

	public function onRangedAttackToTarget(Entity $target, float $power) : void
	{
		$dv = $target->subtractVector($this)->normalize();
		$fireball = new SmallFireball($this->level, Entity::createBaseNBT($this->add($this->random->nextFloat() * $power, $this->getEyeHeight(), $this->random->nextFloat() * $power), $dv), $this);
		$fireball->setMotion($dv->multiply($power));
		$fireball->spawnToAll();
	}

	public function canDespawn() : bool
	{
		return true;
	}

	public function isValidLightLevel() : bool
	{
		return true;
	}
}
