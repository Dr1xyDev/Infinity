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
use pocketmine\level\generator\vanilla\entity\behavior\LookAtPlayerBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\MateBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\PanicBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\RandomLookAroundBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\RandomStrollBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\StayWhileSittingBehavior;
use pocketmine\level\generator\vanilla\entity\behavior\TemptBehavior;
use pocketmine\level\generator\vanilla\entity\Tamable;
use pocketmine\level\generator\vanilla\event\entity\EntityDamageByEntityEvent;
use pocketmine\level\generator\vanilla\event\entity\EntityDamageEvent;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\nbt\tag\IntTag;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ActorEventPacket;
use pocketmine\Player;

use function min;
use function mt_rand;
use function rand;

class Ocelot extends Tamable
{
	public const NETWORK_ID = self::OCELOT;

	public const TYPE_WILD = 0;
	public const TYPE_BLACK = 1;
	public const TYPE_RED = 2;
	public const TYPE_SIAMESE = 3;

	public float $width = 0.35;
	public float $height = 0.7;
	/** @var StayWhileSittingBehavior */
	protected $behaviorSitting;

	protected function addBehaviors() : void
	{
		$this->behaviorPool->setBehavior(0, new FloatBehavior($this));
		$this->behaviorPool->setBehavior(1, new PanicBehavior($this, 2.0));
		$this->behaviorPool->setBehavior(2, $this->behaviorSitting = new StayWhileSittingBehavior($this));
		$this->behaviorPool->setBehavior(3, new MateBehavior($this, 2.0));
		$this->behaviorPool->setBehavior(4, new TemptBehavior($this, [
			Item::RAW_SALMON,
			Item::RAW_FISH
		], 1.0));
		$this->behaviorPool->setBehavior(5, new FollowOwnerBehavior($this, 1, 10, 2));
		$this->behaviorPool->setBehavior(6, new RandomStrollBehavior($this, 0.8));
		$this->behaviorPool->setBehavior(7, new LookAtPlayerBehavior($this, 14.0));
		$this->behaviorPool->setBehavior(8, new RandomLookAroundBehavior($this));

		// TODO: attack turtle and rabbit
	}

	protected function initEntity() : void
	{
		$this->setMaxHealth(10);
		$this->setMovementSpeed(0.3);
		$this->setFollowRange(16);
		$this->setAttackDamage(3);
		if ($this->namedtag->hasTag("CatType", IntTag::class)) {
			$this->propertyManager->setInt(self::DATA_VARIANT, $this->namedtag->getInt("CatType"));
		}

		parent::initEntity();
	}

	public function getName() : string
	{
		return "Ocelot";
	}

	public function onInteract(Player $player, Vector3 $clickPos) : bool
	{
		if (!$this->isImmobile()) {
			$item = $player->getInventory()->getItemInHand();
			if ($item->getId() == Item::RAW_SALMON || $item->getId() == Item::RAW_FISH) {
				if ($player->isSurvival()) {
					$item->pop();
					$player->getInventory()->setItemInHand($item);
				}
				if ($this->isTamed()) {
					$this->setInLove(true);
					$this->setHealth(min($this->getMaxHealth(), $this->getHealth() + 2));
				} elseif (mt_rand(0, 2) == 0) {
					$this->setOwningEntity($player);
					$this->setTamed();
					$this->setSittingFromBehavior(true);
					$this->setType(mt_rand(1, 3));
					$this->broadcastEntityEvent(ActorEventPacket::TAME_SUCCESS);
				} else {
					$this->broadcastEntityEvent(ActorEventPacket::TAME_FAIL);
				}
				return true;
			} else {
				if ($this->isTamed()) {
					$this->setSittingFromBehavior(!$this->isSitting());
				}
			}
		}
		return parent::onInteract($player, $clickPos);
	}

	public function getXpDropAmount() : int
	{
		$damage = $this->getLastDamageCause();
		if ($damage instanceof EntityDamageByEntityEvent) {
			$damager = $damage->getDamager();
			if ($damager instanceof Player || ($damager instanceof Wolf && $damager->isTamed())) {
				return rand(1, ($this->isInLove() ? 7 : 3));
			}
		}
		return 0;
	}

	public function getDrops() : array
	{
		$string = rand(0, 2 + $this->getLootingLevel());
		return $string > 0 ? [ItemFactory::get(Item::STRING, 0, $string)] : [];
	}

	public function setSittingFromBehavior(bool $value) : void
	{
		$this->behaviorSitting->setSitting($value);
	}

	public function setType(int $type) : void
	{
		$this->namedtag->setInt("CatType", $type);
		$this->propertyManager->setInt(self::DATA_VARIANT, $type);
	}

	public function attack(EntityDamageEvent $source) : void
	{
		if ($source->getCause() !== EntityDamageEvent::CAUSE_FALL) {
			parent::attack($source);
		}
	}
}
