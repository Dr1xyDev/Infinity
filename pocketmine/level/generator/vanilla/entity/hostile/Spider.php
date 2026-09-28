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
use pocketmine\level\generator\vanilla\entity\Arthropod;
use pocketmine\level\generator\vanilla\entity\behavior\FloatBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\LeapAtTargetBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\LookAtPlayerBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\MeleeAttackBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\NearestAttackableTargetBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\RandomLookAroundBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\RandomStrollBehavior;
use pocketmine\level\generator\vanilla\entity\Monster;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\Player;

use function rand;

class Spider extends Monster implements Ageable, Arthropod
{
	public const NETWORK_ID = self::SPIDER;

	public float $width = 1.4;
	public float $height = 0.9;

	protected function initEntity() : void
	{
		$this->setMaxHealth(16);
		$this->setMovementSpeed(0.3);
		$this->setFollowRange(35);
		$this->setAttackDamage(2);
		$this->setCanClimb(true);
		$this->setCanClimbWalls(true);

		parent::initEntity();
	}

	public function getName() : string
	{
		return "Spider";
	}

	protected function addBehaviors() : void
	{
		// TODO: Spider Attack
		$this->behaviorPool->setBehavior(0, new FloatBehavior($this));
		$this->behaviorPool->setBehavior(1, new LeapAtTargetBehavior($this, 0.4));
		$this->behaviorPool->setBehavior(2, new MeleeAttackBehavior($this, 1.0));
		$this->behaviorPool->setBehavior(3, new RandomStrollBehavior($this, 1.0));
		$this->behaviorPool->setBehavior(4, new LookAtPlayerBehavior($this, 8.0));
		$this->behaviorPool->setBehavior(5, new RandomLookAroundBehavior($this));

		$this->targetBehaviorPool->setBehavior(0, new class ($this, Player::class) extends NearestAttackableTargetBehavior {
			public function canStart() : bool
			{
				$canStart = parent::canStart();

				if ($this->mob instanceof Spider && $canStart) {
					if ($this->mob->level->isDayTime() && !$this->mob->canSpawnHere()) {
						return false;
					}
				}

				return $canStart;
			}
		});
	}

	public function getXpDropAmount() : int
	{
		return $this->getRevengeTarget() instanceof Player ? 5 : 0;
	}

	public function getDrops() : array
	{
		$looting = $this->getLootingLevel();
		$drops = [];

		$string = rand(0, 2 + $looting);
		if ($string > 0) {
			$drops[] = ItemFactory::get(Item::STRING, 0, $string);
		}

		if ($this->getRevengeTarget() instanceof Player && $this->level->random->nextBoundedInt(3) === 0) {
			$spiderEyes = 1 + ($looting > 0 ? rand(0, $looting) : 0);
			$drops[] = ItemFactory::get(Item::SPIDER_EYE, 0, $spiderEyes);
		}
		return $drops;
	}
}
