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

use pocketmine\level\generator\vanilla\entity\Animal;
use pocketmine\level\generator\vanilla\entity\behavior\FloatBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\FollowParentBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\LookAtPlayerBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\MateBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\PanicBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\RandomLookAroundBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\RandomStrollBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\TemptBehavior;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\math\Vector3;

use function boolval;
use function intval;
use function rand;

class Chicken extends Animal
{
	public const NETWORK_ID = self::CHICKEN;

	public float $width = 0.4;
	public float $height = 0.7;

	protected $chickenJockey = false;
	protected $timeUntilNextEgg = 0;

	public function isChickenJockey() : bool
	{
		return $this->chickenJockey;
	}

	public function setChickenJockey(bool $chickenJockey) : void
	{
		$this->chickenJockey = $chickenJockey;
	}

	protected function addBehaviors() : void
	{
		$this->behaviorPool->setBehavior(0, new FloatBehavior($this));
		$this->behaviorPool->setBehavior(1, new PanicBehavior($this, 1.4));
		$this->behaviorPool->setBehavior(2, new MateBehavior($this, 1.0));
		$this->behaviorPool->setBehavior(3, new TemptBehavior($this, [Item::WHEAT_SEEDS], 1.0));
		$this->behaviorPool->setBehavior(4, new FollowParentBehavior($this, 1.1));
		$this->behaviorPool->setBehavior(5, new RandomStrollBehavior($this, 1.0));
		$this->behaviorPool->setBehavior(6, new LookAtPlayerBehavior($this, 6.0));
		$this->behaviorPool->setBehavior(7, new RandomLookAroundBehavior($this));
	}

	protected function initEntity() : void
	{
		$this->setMaxHealth(4);
		$this->setMovementSpeed(0.25);
		$this->setFollowRange(10);

		$this->setChickenJockey(boolval($this->namedtag->getByte("isChickenJockey", 0)));
		$this->timeUntilNextEgg = $this->level->random->nextBoundedInt(6000) + 6000;

		parent::initEntity();
	}

	public function getName() : string
	{
		return "Chicken";
	}

	public function getXpDropAmount() : int
	{
		return rand(1, 3);
	}

	public function getDrops() : array
	{
		$looting = $this->getLootingLevel();
		return [
			($this->isOnFire() ? ItemFactory::get(Item::COOKED_CHICKEN, 0, 1 + ($looting > 0 ? rand(0, $looting) : 0)) : ItemFactory::get(Item::RAW_CHICKEN, 0, 1 + ($looting > 0 ? rand(0, $looting) : 0))),
			ItemFactory::get(Item::FEATHER, 0, rand(0, 2 + $looting))
		];
	}

	public function saveNBT() : void
	{
		parent::saveNBT();

		$this->namedtag->setByte("isChickenJockey", intval($this->isChickenJockey()));
	}

	public function getRiderSeatPosition(int $seatNumber = 0) : Vector3
	{
		return new Vector3(0, 1, 0);
	}

	public function entityBaseTick(int $diff = 1) : bool
	{
		if (!$this->onGround && $this->motion->y < 0) {
			$this->motion->y *= 0.6;
		}

		if (!$this->isImmobile() && !$this->isBaby() && !$this->isChickenJockey() && $this->timeUntilNextEgg-- <= 0) {
			$this->level->dropItem($this, ItemFactory::get(Item::EGG));
			$this->timeUntilNextEgg = $this->level->random->nextBoundedInt(6000) + 6000;
		}
		return parent::entityBaseTick($diff);
	}

	public function fall(float $fallDistance) : void
	{
		// chickens do not get damage when fall
	}
}
