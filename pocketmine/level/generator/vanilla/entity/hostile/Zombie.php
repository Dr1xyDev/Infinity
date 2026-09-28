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

use pocketmine\level\generator\vanilla\entity\Ageable;
use pocketmine\level\generator\vanilla\entity\behavior\FloatBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\LookAtPlayerBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\MeleeAttackBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\NearestAttackableTargetBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\RandomLookAroundBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\RandomStrollBehavior;
use pocketmine\level\generator\vanilla\entity\Monster;
use pocketmine\level\generator\vanilla\entity\passive\Villager;
use pocketmine\level\generator\vanilla\entity\Smite;
use pocketmine\level\generator\vanilla\event\entity\EntityDamageEvent;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ActorEventPacket;
use pocketmine\Player;

use function mt_rand;

class Zombie extends Monster implements Ageable, Smite
{
	public const NETWORK_ID = self::ZOMBIE;

	public float $width = 0.6;
	public float $height = 1.8;

	protected function initEntity() : void
	{
		$this->setMovementSpeed($this->isBaby() ? 0.35 : 0.23);
		$this->setFollowRange(35);
		$this->setAttackDamage(3);

		parent::initEntity();
	}

	public function getName() : string
	{
		return "Zombie";
	}

	public function getDrops() : array
	{
		$looting = $this->getLootingLevel();
		$drops = [];

		$rottenFlesh = mt_rand(0, 2 + $looting);
		if ($rottenFlesh > 0) {
			$drops[] = ItemFactory::get(Item::ROTTEN_FLESH, 0, $rottenFlesh);
		}

		if (mt_rand(0, 199) < 5 + $looting) {
			switch (mt_rand(0, 2)) {
				case 0:
					$drops[] = ItemFactory::get(Item::IRON_INGOT, 0, 1);
					break;
				case 1:
					$drops[] = ItemFactory::get(Item::CARROT, 0, 1);
					break;
				case 2:
					$drops[] = ItemFactory::get(Item::POTATO, 0, 1);
					break;
			}
		}

		return $drops;
	}

	public function getXpDropAmount() : int
	{
		//TODO: check for equipment
		return $this->isBaby() ? 12 : 5;
	}

	public function onAttack(EntityDamageEvent $source) : void
	{
		$this->broadcastEntityEvent(ActorEventPacket::ARM_SWING);
	}

	protected function addBehaviors() : void
	{
		$this->behaviorPool->setBehavior(0, new FloatBehavior($this));
		$this->behaviorPool->setBehavior(1, new MeleeAttackBehavior($this, 1.0));
		$this->behaviorPool->setBehavior(2, new RandomStrollBehavior($this, 1.0));
		$this->behaviorPool->setBehavior(3, new LookAtPlayerBehavior($this, 8.0));
		$this->behaviorPool->setBehavior(4, new RandomLookAroundBehavior($this));

		$this->targetBehaviorPool->setBehavior(1, new NearestAttackableTargetBehavior($this, Player::class));
		$this->targetBehaviorPool->setBehavior(2, new NearestAttackableTargetBehavior($this, Villager::class));
	}

	public function entityBaseTick(int $diff = 1) : bool
	{
		if (!$this->isOnFire() && $this->level->isDayTime() && !$this->isImmobile()) {
			if (!$this->isInsideOfWater() && $this->level->canSeeSky($this)) {
				$this->setOnFire(5);
			}
		}
		return parent::entityBaseTick($diff);
	}
}
