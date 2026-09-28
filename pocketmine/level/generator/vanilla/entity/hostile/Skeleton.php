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

use pocketmine\level\generator\vanilla\entity\behavior\AvoidMobTypeBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\FleeSunBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\FloatBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\LookAtPlayerBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\NearestAttackableTargetBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\RandomLookAroundBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\RandomStrollBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\RangedAttackBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\RestrictSunBehavior;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\entity\Monster;
use pocketmine\level\generator\vanilla\entity\passive\Wolf;
use pocketmine\level\generator\vanilla\entity\projectile\Arrow;
use pocketmine\level\generator\vanilla\entity\RangedAttackerMob;
use pocketmine\level\generator\vanilla\entity\Smite;
use pocketmine\level\generator\vanilla\inventory\AltayEntityEquipment;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\network\mcpe\convert\TypeConverter;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\ContainerIds;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\Player;

use function rand;
use function sqrt;

class Skeleton extends Monster implements RangedAttackerMob, Smite
{
	public const NETWORK_ID = self::SKELETON;

	public float $width = 0.6;
	public float $height = 1.99;

	/** @var AltayEntityEquipment */
	protected $equipment;

	protected function initEntity() : void
	{
		$this->setMovementSpeed(0.25);
		$this->setFollowRange(35);
		$this->setAttackDamage(2);

		parent::initEntity();

		$this->equipment = new AltayEntityEquipment($this);

		$this->equipment->setItemInHand(ItemFactory::get(Item::BOW));

		// TODO: Armors
	}

	public function getName() : string
	{
		return "Skeleton";
	}

	public function getDrops() : array
	{
		$looting = $this->getLootingLevel();
		$drops = [];

		$bones = rand(0, 2 + $looting);
		if ($bones > 0) {
			$drops[] = ItemFactory::get(Item::BONE, 0, $bones);
		}

		$arrows = rand(0, 2 + $looting);
		if ($arrows > 0) {
			$drops[] = ItemFactory::get(Item::ARROW, 0, $arrows);
		}

		return $drops;
	}

	public function getXpDropAmount() : int
	{
		return 5;
	}

	protected function addBehaviors() : void
	{
		$this->behaviorPool->setBehavior(0, new FloatBehavior($this));
		$this->behaviorPool->setBehavior(1, new RestrictSunBehavior($this));
		$this->behaviorPool->setBehavior(2, new FleeSunBehavior($this, 1.0));
		$this->behaviorPool->setBehavior(3, new AvoidMobTypeBehavior($this, Wolf::class, 6.0, 1.0, 1.2));
		$this->behaviorPool->setBehavior(4, new RandomStrollBehavior($this, 1.0));
		$this->behaviorPool->setBehavior(5, new RangedAttackBehavior($this, 1.0, 20, 60, 15.0));
		$this->behaviorPool->setBehavior(6, new LookAtPlayerBehavior($this, 8.0));
		$this->behaviorPool->setBehavior(7, new RandomLookAroundBehavior($this));

		$this->targetBehaviorPool->setBehavior(0, new NearestAttackableTargetBehavior($this, Player::class));
		//$this->targetBehaviorPool->setBehavior(2, new NearestAttackableTargetBehavior($this, IronGolem::class, false));
	}

	public function onRangedAttackToTarget(Entity $target, float $power) : void
	{
		$pos = $this->add(0, $this->getEyeHeight() - 0.1, 0);
		$motion = $target->add(0, $target->height / 3, 0)->subtractVector($pos)->normalize();
		$f = sqrt($motion->x ** 2 + $motion->z ** 2);

		/** @var Arrow $arrow */
		$arrow = Entity::createEntity("Arrow", $this->level, Entity::createBaseNBT($pos->addVector($motion)));
		// TODO: Enchants
		$arrow->setThrowableMotion($motion->add(0, $f * 0.2, 0), 1.6, (14 - $this->level->getDifficulty() * 4));
		$arrow->setPickupMode(Arrow::PICKUP_NONE);
		$arrow->setBaseDamage($power * 2 + $this->random->nextFloat() * 0.25 + ($this->level->getDifficulty() * 0.11));

		$this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_BOW);
		$arrow->spawnToAll();
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

	public function sendSpawnPacket(Player $player) : void
	{
		parent::sendSpawnPacket($player);

		$this->equipment->sendContents([$player]);

		// stupid hack for 1.1
		$player->sendDataPacket(MobEquipmentPacket::create(
			$this->getId(),
			ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet($this->equipment->getItemInHand(), $player->getProtocolVersion())),
			10,
			10,
			ContainerIds::INVENTORY
		));
	}
}
