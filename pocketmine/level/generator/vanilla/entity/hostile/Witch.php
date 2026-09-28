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

use pocketmine\level\generator\vanilla\entity\behavior\FloatBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\HurtByTargetBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\LookAtPlayerBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\NearestAttackableTargetBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\RandomLookAroundBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\RandomStrollBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\RangedAttackBehavior;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\entity\EntityIds;
use pocketmine\level\generator\vanilla\entity\Monster;
use pocketmine\level\generator\vanilla\entity\projectile\SplashPotion;
use pocketmine\level\generator\vanilla\entity\RangedAttackerMob;
use pocketmine\level\generator\vanilla\entity\Smite;
use pocketmine\level\generator\vanilla\inventory\AltayEntityEquipment;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\item\Potion;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\utils\Utils;

use function array_rand;
use function rand;
use function sqrt;

class Witch extends Monster implements RangedAttackerMob, Smite
{
	public const NETWORK_ID = self::WITCH;

	public float $width = 0.6;
	public float $height = 1.95;

	private $healCooldown = 0;
	private $potionVisibleTicks = 0;

	/** @var AltayEntityEquipment */
	protected $equipment;

	protected function initEntity() : void
	{
		$this->setMaxHealth(26);
		$this->setHealth(26);
		$this->setMovementSpeed(0.2);
		$this->setFollowRange(35);

		parent::initEntity();

		$this->equipment = new AltayEntityEquipment($this);
	}

	public function getName() : string
	{
		return "Witch";
	}

	public function getDrops() : array
	{
		$looting = $this->getLootingLevel();
		$drops = [];

		$glassBottles = rand(0, 2 + $looting);
		if ($glassBottles > 0) {
			$drops[] = ItemFactory::get(Item::GLASS_BOTTLE, 0, $glassBottles);
		}

		$glowstoneDust = rand(0, 2 + $looting);
		if ($glowstoneDust > 0) {
			$drops[] = ItemFactory::get(Item::GLOWSTONE_DUST, 0, $glowstoneDust);
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
		$this->behaviorPool->setBehavior(1, new RandomStrollBehavior($this, 1.0));
		$this->behaviorPool->setBehavior(2, new RangedAttackBehavior($this, 1.0, 20, 60, 10.0));
		$this->behaviorPool->setBehavior(3, new LookAtPlayerBehavior($this, 8.0));
		$this->behaviorPool->setBehavior(4, new RandomLookAroundBehavior($this));

		$this->targetBehaviorPool->setBehavior(0, new NearestAttackableTargetBehavior($this, Player::class));
		$this->targetBehaviorPool->setBehavior(1, new HurtByTargetBehavior($this));
	}

	public function onRangedAttackToTarget(Entity $target, float $power) : void
	{
		if ($this->potionVisibleTicks > 0) {
			return;
		}

		$diff = new Vector3(
			$target->x - $this->x,
			($target->y + $target->getEyeHeight() - 1) - $this->y,
			$diffZ = $target->z - $this->z
		);
		$horizontalForce = sqrt(($diff->x ** 2) + ($diff->z ** 2));
		$targetVector = $diff->add(0, $horizontalForce * 0.2, 0)->normalize()->multiply(0.75);

		$nbt = Entity::createBaseNBT($this->add(0, $this->getEyeHeight(), 0), $targetVector, Utils::getRandomFloat() * 360, 0);

		$entity = Entity::createEntity("SplashPotion", $this->level, $nbt);
		if ($entity instanceof SplashPotion) {
			$potions = [
				Potion::POISON,
				Potion::HARMING,
				Potion::SLOWNESS,
				Potion::WEAKNESS
			];
			$entity->setPotionId($potions[array_rand($potions)]);

			$entity->setOwningEntity($this);
			$entity->spawnToAll();
		}

		$this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_THROW, -1, EntityIds::PLAYER);
	}

	public function entityBaseTick(int $diff = 1) : bool
	{
		if (!$this->isImmobile()) {
			if ($this->healCooldown > 0) {
				$this->healCooldown -= $diff;
			}

			if ($this->potionVisibleTicks > 0) {
				$this->potionVisibleTicks -= $diff;
			}

			if ($this->potionVisibleTicks <= 0) {
				$this->equipment->setItemInHand(ItemFactory::get(Item::AIR));
				$this->equipment->sendSlot(0, $this->getViewers());
			}

			if ($this->getHealth() < $this->getMaxHealth() && $this->healCooldown <= 0) {
				$this->healWitch();
			}
		}

		return parent::entityBaseTick($diff);
	}

	private function healWitch() : void
	{
		$this->healCooldown = 100;
		$this->setHealth($this->getHealth() + 4);
		$this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_DRINK);

		$this->equipment->setItemInHand(ItemFactory::get(Item::POTION, 21));
		$this->equipment->sendSlot(0, $this->getViewers());

		$this->potionVisibleTicks = 32;
	}

	public function sendSpawnPacket(Player $player) : void
	{
		parent::sendSpawnPacket($player);
	}
}
