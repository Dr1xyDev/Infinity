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

namespace pocketmine\level\generator\vanilla\entity\passive;

use pocketmine\level\generator\vanilla\entity\behavior\FloatBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\FollowOwnerBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\HurtByTargetBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\LeapAtTargetBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\LookAtPlayerBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\MeleeAttackBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\NearestAttackableTargetBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\OwnerHurtByTargetBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\OwnerHurtTargetBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\RandomLookAroundBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\RandomStrollBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\StayWhileSittingBehavior;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\entity\hostile\Skeleton;
use pocketmine\level\generator\vanilla\entity\Tamable;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ActorEventPacket;
use pocketmine\Player;

use function mt_rand;

class Wolf extends Tamable
{
	public const NETWORK_ID = self::WOLF;

	public float $width = 0.6;
	public float $height = 0.8;
	/** @var StayWhileSittingBehavior */
	protected $behaviorSitting;

	protected function addBehaviors() : void
	{
		$this->behaviorPool->setBehavior(0, new FloatBehavior($this));
		$this->behaviorPool->setBehavior(1, $this->behaviorSitting = new StayWhileSittingBehavior($this));
		$this->behaviorPool->setBehavior(2, new LeapAtTargetBehavior($this, 0.4));
		$this->behaviorPool->setBehavior(3, new MeleeAttackBehavior($this, 1.0));
		$this->behaviorPool->setBehavior(4, new FollowOwnerBehavior($this, 1, 10, 2));
		$this->behaviorPool->setBehavior(5, new RandomStrollBehavior($this, 1.0));
		$this->behaviorPool->setBehavior(6, new LookAtPlayerBehavior($this, 8.0));
		$this->behaviorPool->setBehavior(7, new RandomLookAroundBehavior($this));

		$this->targetBehaviorPool->setBehavior(0, new HurtByTargetBehavior($this, true));
		$this->targetBehaviorPool->setBehavior(1, new OwnerHurtByTargetBehavior($this));
		$this->targetBehaviorPool->setBehavior(2, new OwnerHurtTargetBehavior($this));
		$this->targetBehaviorPool->setBehavior(3, new NearestAttackableTargetBehavior($this, Skeleton::class));
	}

	protected function initEntity() : void
	{
		$this->setMaxHealth(8);
		$this->setMovementSpeed(0.3);
		$this->setAttackDamage(3);
		$this->setFollowRange(16);
		$this->setTamed(false);

		parent::initEntity();
	}

	public function getName() : string
	{
		return "Wolf";
	}

	public function onInteract(Player $player, Vector3 $clickPos) : bool
	{
		if (!$this->isImmobile()) {
			if ($this->isTamed()) {
				if ($this->getOwningEntityId() == $player->id) {
					$this->setSittingFromBehavior(!$this->isSitting());
					$this->setTargetEntity(null);
				}
			} else {
				$item = $player->getInventory()->getItemInHand();
				if ($item->getId() == Item::BONE) {
					if ($player->isSurvival()) {
						$item->pop();
						$player->getInventory()->setItemInHand($item);
					}

					if (mt_rand(0, 2) == 0) {
						$this->setOwningEntity($player);
						$this->setTamed();
						$this->setSittingFromBehavior(true);
						$this->setAngry(false);

						$this->broadcastEntityEvent(ActorEventPacket::TAME_SUCCESS);
					} else {
						$this->broadcastEntityEvent(ActorEventPacket::TAME_FAIL);
					}

					return true;
				}
			}
		}

		return parent::onInteract($player, $clickPos);
	}

	public function setSittingFromBehavior(bool $value) : void
	{
		$this->behaviorSitting->setSitting($value);
	}

	public function setTargetEntity(?Entity $target) : void
	{
		parent::setTargetEntity($target);
		if ($target == null) {
			$this->setAngry(false);
		} elseif (!$this->isTamed()) {
			$this->setAngry();
		}
	}

	public function isAngry() : bool
	{
		return $this->getGenericFlag(self::DATA_FLAG_ANGRY);
	}

	public function setAngry(bool $angry = true) : void
	{
		$this->setGenericFlag(self::DATA_FLAG_ANGRY, $angry);
	}

	public function setTamed(bool $tamed = true) : void
	{
		parent::setTamed($tamed);

		if ($tamed) {
			$this->setMaxHealth(20);
		} else {
			$this->setMaxHealth(8);
		}

		$this->setAttackDamage(4);
	}
}
