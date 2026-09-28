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
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ActorEventPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AddActorPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\EntityLink;
use pocketmine\Player;

use function array_map;
use function count;
use function intval;
use function is_array;
use function min;
use function mt_rand;
use function rand;

class Cat extends Tamable
{
	public const NETWORK_ID = self::CAT;

	public const COLOR_WHITE = 0;
	public const COLOR_TUXEDO = 1;
	public const COLOR_RED = 2;
	public const COLOR_SIAMESE = 3;
	public const COLOR_BRITISH_SHORTHAIR = 4;
	public const COLOR_CALICO = 5;
	public const COLOR_PERSIAN = 6;
	public const COLOR_RAGDOLL = 7;
	public const COLOR_TABBY = 8;
	public const COLOR_BLACK = 9;
	public const COLOR_JELLIE = 10;

	public float $width = 0.6;
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
		$this->propertyManager->setInt(self::DATA_VARIANT, intval($this->namedtag->getInt("CatType", mt_rand(0, 10))));

		parent::initEntity();
	}

	public function getName() : string
	{
		return "Cat";
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

	protected function sendSpawnPacket(Player $player) : void
	{
		$metadata = $this->propertyManager->getAll();
		if ($player->getProtocolVersion() < ProtocolInfo::PROTOCOL_407) {
			if (isset($metadata[self::DATA_VARIANT])) {
				$metadata[self::DATA_VARIANT][1] = self::translateToOcelotColor($metadata[self::DATA_VARIANT][1]);
			}
		}

		$links = [];
		if (count($this->passengers) !== 0) {
			foreach ($this->getPassengers() as $passenger) {
				$passenger->spawnTo($player);
			}

			$links = array_map(function (int $entityId) {
				return new EntityLink(
					$this->getId(),
					$entityId,
					EntityLink::TYPE_RIDER,
					true,
					false
				);
			}, $this->passengers);
		}

		$player->dataPacket(AddActorPacket::create(
			$this->getId(),
			$this->getId(),
			($player->getProtocolVersion() >= ProtocolInfo::PROTOCOL_407 ? static::NETWORK_ID : Ocelot::NETWORK_ID),
			$this->asVector3(),
			$this->getMotion(),
			$this->pitch,
			$this->yaw,
			$this->headYaw ?? $this->yaw,
			$this->yaw,
			$this->attributeMap->getAll(),
			$metadata,
			new PropertySyncData([], []),
			$links
		));
	}

	public function sendData($player, ?array $data = null) : void
	{
		if (!is_array($player)) {
			$player = [$player];
		}

		$pk = new SetActorDataPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->metadata = $data ?? $this->propertyManager->getAll();
		$pk->syncedProperties = new PropertySyncData([], []);

		foreach ($player as $p) {
			if (isset($pk->metadata[self::DATA_VARIANT])) {
				if ($p->getProtocolVersion() < ProtocolInfo::PROTOCOL_407) {
					$pk->metadata[self::DATA_VARIANT][1] = self::translateToOcelotColor($pk->metadata[self::DATA_VARIANT][1]);
				}
			}

			$p->dataPacket(clone $pk);
		}
	}

	protected static function translateToOcelotColor(int $color) : int
	{
		return match ($color) {
			self::COLOR_SIAMESE, self::COLOR_BRITISH_SHORTHAIR, self::COLOR_CALICO, self::COLOR_RAGDOLL, self::COLOR_JELLIE, self::COLOR_WHITE => Ocelot::TYPE_SIAMESE,
			self::COLOR_PERSIAN, self::COLOR_TABBY, self::COLOR_RED => Ocelot::TYPE_RED,
			default => Ocelot::TYPE_BLACK,
		};
	}
}
