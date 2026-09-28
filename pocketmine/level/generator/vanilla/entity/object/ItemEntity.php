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

namespace pocketmine\level\generator\vanilla\entity\object;

use pocketmine\level\generator\vanilla\block\Water;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\event\entity\ItemDespawnEvent;
use pocketmine\level\generator\vanilla\event\entity\ItemMergeEvent;
use pocketmine\level\generator\vanilla\event\entity\ItemSpawnEvent;
use pocketmine\level\generator\vanilla\event\inventory\InventoryPickupItemEvent;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\math\AxisAlignedBB;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\convert\TypeConverter;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ActorEventPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\AddItemActorPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\TakeItemActorPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\timings\Timings;
use UnexpectedValueException;

use function get_class;
use function max;

class ItemEntity extends Entity
{
	public const NETWORK_ID = self::ITEM;

	public const MERGE_CHECK_PERIOD = 2; //0.1 seconds
	public const DEFAULT_DESPAWN_DELAY = 6000; //5 minutes
	public const NEVER_DESPAWN = -1;
	public const MAX_DESPAWN_DELAY = 32767 + self::DEFAULT_DESPAWN_DELAY; //max value storable by mojang NBT :(

	protected string $owner = "";
	protected string $thrower = "";
	protected int $pickupDelay = 0;
	protected Item $item;

	public float $width = 0.25;
	public float $height = 0.25;
	protected float $baseOffset = 0.125;

	protected $gravity = 0.04;
	protected $drag = 0.02;

	public bool $canCollide = true;

	protected int $despawnDelay = 0;

	protected function initEntity() : void
	{
		parent::initEntity();

		$this->setMaxHealth(5);
		$this->setHealth($this->namedtag->getShort("Health", (int) $this->getHealth()));

		$age = $this->namedtag->getShort("Age", 0);
		if($age === -32768){
			$this->despawnDelay = self::NEVER_DESPAWN;
		}else{
			$this->despawnDelay = max(0, self::DEFAULT_DESPAWN_DELAY - $age);
		}
		$this->pickupDelay = $this->namedtag->getShort("PickupDelay", $this->pickupDelay);
		$this->owner = $this->namedtag->getString("Owner", $this->owner);
		$this->thrower = $this->namedtag->getString("Thrower", $this->thrower);

		$itemTag = $this->namedtag->getCompoundTag("Item");
		if ($itemTag === null) {
			throw new UnexpectedValueException("Invalid " . get_class($this) . " entity: expected \"Item\" NBT tag not found");
		}

		$this->item = Item::nbtDeserialize($itemTag);
		if ($this->item->isNull()) {
			throw new UnexpectedValueException("Item for " . get_class($this) . " is invalid");
		}

		if ($this->level instanceof Level) {
			(new ItemSpawnEvent($this))->call();
		}
	}

	public function entityBaseTick(int $tickDiff = 1) : bool
	{
		if ($this->closed) {
			return false;
		}
		Timings::$itemEntityBaseTick->startTiming();
		try {
			$hasUpdate = parent::entityBaseTick($tickDiff);

			if($this->isFlaggedForDespawn()){
				return $hasUpdate;
			}

			if($this->pickupDelay !== self::NEVER_DESPAWN && $this->pickupDelay > 0){ //Infinite delay
				$hasUpdate = true;
				$this->pickupDelay -= $tickDiff;
				if($this->pickupDelay < 0){
					$this->pickupDelay = 0;
				}
			}

			if($this->hasMovementUpdate() && $this->isMergeCandidate() && $this->despawnDelay % self::MERGE_CHECK_PERIOD === 0){
				$mergeable = [$this]; //in case the merge target ends up not being this
				$mergeTarget = $this;
				foreach($this->getLevel()->getNearbyEntities($this->boundingBox->expandedCopy(0.5, 0.5, 0.5), $this) as $entity){
					if(!$entity instanceof ItemEntity || $entity->isFlaggedForDespawn()){
						continue;
					}

					if($entity->isMergeable($this)){
						$mergeable[] = $entity;
						if($entity->item->getCount() > $mergeTarget->item->getCount()){
							$mergeTarget = $entity;
						}
					}
				}
				foreach($mergeable as $itemEntity){
					if($itemEntity !== $mergeTarget){
						$itemEntity->tryMergeInto($mergeTarget);
					}
				}
			}

			if(!$this->isFlaggedForDespawn() && $this->despawnDelay !== self::NEVER_DESPAWN){
				$hasUpdate = true;
				$this->despawnDelay -= $tickDiff;
				if($this->despawnDelay <= 0){
					$ev = new ItemDespawnEvent($this);
					$ev->call();
					if($ev->isCancelled()){
						$this->despawnDelay = self::DEFAULT_DESPAWN_DELAY;
					}else{
						$this->flagForDespawn();
					}
				}
			}

			return $hasUpdate;
		} finally {
			Timings::$itemEntityBaseTick->stopTiming();
		}
	}

	private function isMergeCandidate() : bool{
		return $this->pickupDelay !== self::NEVER_DESPAWN && $this->item->getCount() < $this->item->getMaxStackSize();
	}

	/**
	 * Returns whether this item entity can merge with the given one.
	 */
	public function isMergeable(ItemEntity $entity) : bool{
		if(!$this->isMergeCandidate() || !$entity->isMergeCandidate()){
			return false;
		}
		$item = $entity->item;
		return $entity !== $this && $item->canStackWith($this->item) && $item->getCount() + $this->item->getCount() <= $item->getMaxStackSize();
	}

	/**
	 * Attempts to merge this item entity into the given item entity. Returns true if it was successful.
	 */
	public function tryMergeInto(ItemEntity $consumer) : bool{
		if(!$this->isMergeable($consumer)){
			return false;
		}

		$ev = new ItemMergeEvent($this, $consumer);
		$ev->call();

		if($ev->isCancelled()){
			return false;
		}

		$consumer->setStackSize($consumer->item->getCount() + $this->item->getCount());
		$this->flagForDespawn();
		$consumer->pickupDelay = max($consumer->pickupDelay, $this->pickupDelay);
		$consumer->despawnDelay = max($consumer->despawnDelay, $this->despawnDelay);

		return true;
	}

	protected function tryChangeMovement() : void
	{
		$this->checkObstruction($this->x, $this->y, $this->z);
		parent::tryChangeMovement();
	}

	protected function applyDragBeforeGravity() : bool
	{
		return true;
	}

	public function canSaveWithChunk() : bool{
		return !$this->item->isNull() && parent::canSaveWithChunk();
	}

	protected function applyGravity() : void
	{
		if ($this->level->getBlockAt($this->getFloorX(), $this->getFloorY(), $this->getFloorZ()) instanceof Water) {
			$bb = $this->getBoundingBox();
			$waterCount = 0;

			for ($j = 0; $j < 5; ++$j) {
				$d1 = $bb->minY + ($bb->maxY - $bb->minY) * $j / 5 + 0.4;
				$d3 = $bb->minY + ($bb->maxY - $bb->minY) * ($j + 1) / 5 + 1;

				$bb2 = new AxisAlignedBB($bb->minX, $d1, $bb->minZ, $bb->maxX, $d3, $bb->maxZ);

				if ($this->level->isLiquidInBoundingBox($bb2, new Water())) {
					$waterCount += 0.2;
				}
			}

			if ($waterCount > 0) {
				$this->motion->y += 0.002 * ($waterCount * 2 - 1);
			} else {
				$this->motion->y -= $this->gravity;
			}
		} else {
			$this->motion->y -= $this->gravity;
		}
	}

	public function saveNBT() : void
	{
		parent::saveNBT();

		$this->namedtag->setTag($this->item->nbtSerialize(-1, "Item"));
		$this->namedtag->setShort("Health", (int) $this->getHealth());
		if($this->despawnDelay === self::NEVER_DESPAWN){
			$age = -32768;
		}else{
			$age = self::DEFAULT_DESPAWN_DELAY - $this->despawnDelay;
		}
		$this->namedtag->setShort("Age", $age);
		$this->namedtag->setShort("PickupDelay", $this->pickupDelay);
		$this->namedtag->setString("Owner", $this->owner);
		$this->namedtag->setString("Thrower", $this->thrower);
	}

	public function getItem() : Item
	{
		return $this->item;
	}

	public function canCollideWith(Entity $entity) : bool{
		return false;
	}

	public function canBeCollidedWith() : bool{
		return false;
	}

	public function getPickupDelay() : int
	{
		return $this->pickupDelay;
	}

	public function setPickupDelay(int $delay) : void
	{
		$this->pickupDelay = $delay;
	}

	/**
	 * Returns the number of ticks left before this item will despawn. If -1, the item will never despawn.
	 */
	public function getDespawnDelay() : int{
		return $this->despawnDelay;
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	public function setDespawnDelay(int $despawnDelay) : void{
		if(($despawnDelay < 0 || $despawnDelay > self::MAX_DESPAWN_DELAY) && $despawnDelay !== self::NEVER_DESPAWN){
			throw new \InvalidArgumentException("Despawn ticker must be in range 0 ... " . self::MAX_DESPAWN_DELAY . " or " . self::NEVER_DESPAWN . ", got $despawnDelay");
		}
		$this->despawnDelay = $despawnDelay;
	}

	public function getOwner() : string
	{
		return $this->owner;
	}

	public function setOwner(string $owner) : void
	{
		$this->owner = $owner;
	}

	public function getThrower() : string
	{
		return $this->thrower;
	}

	public function setThrower(string $thrower) : void
	{
		$this->thrower = $thrower;
	}

	protected function sendSpawnPacket(Player $player) : void
	{
		$player->sendDataPacket(AddItemActorPacket::create(
			$this->getId(), //TODO: entity unique ID
			$this->getId(),
			ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet($this->getItem(), $player->getProtocolVersion())),
			$this->asVector3(),
			$this->getMotion(),
			$this->propertyManager->getAll(),
			false //TODO: I have no idea what this is needed for, but right now we don't support fishing anyway
		));
	}

	public function setStackSize(int $newCount) : void{
		if($newCount <= 0){
			throw new \InvalidArgumentException("Stack size must be at least 1");
		}
		$this->item->setCount($newCount);
		$this->broadcastEntityEvent(ActorEventPacket::ITEM_ENTITY_MERGE, $newCount);
	}

	public function getOffsetPosition(Vector3 $vector3) : Vector3{
		return $vector3->add(0, 0.125, 0);
	}

	public function onCollideWithPlayer(Player $player) : void
	{
		if($this->getPickupDelay() !== 0){
			return;
		}

		$item = $this->getItem();
		$playerInventory = match(true){
			$player->getOffHandInventory()->getItem(0)->canStackWith($item) && $player->getOffHandInventory()->getAddableItemQuantity($item) > 0 => $player->getOffHandInventory(),
			$player->getInventory()->getAddableItemQuantity($item) > 0 => $player->getInventory(),
			default => null
		};

		if($playerInventory === null){
			return;
		}

		$ev = new InventoryPickupItemEvent($playerInventory, $this);
		$ev->call();
		if($ev->isCancelled()){
			return;
		}

		$pk = new TakeItemActorPacket();
		$pk->eid = $player->getId();
		$pk->target = $this->getId();
		$this->server->broadcastPacket($this->getViewers(), $pk);

		foreach($playerInventory->addItem($this->getItem()) as $remains){
			$this->level->dropItem($this, $remains, new Vector3(0, 0, 0));
		}
		$this->flagForDespawn();
	}
}
