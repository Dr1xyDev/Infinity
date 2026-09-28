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

use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\entity\EntityIds;
use pocketmine\level\generator\vanilla\entity\Living;
use pocketmine\level\generator\vanilla\event\entity\EntityDamageByEntityEvent;
use pocketmine\level\generator\vanilla\event\entity\EntityDamageEvent;
use pocketmine\level\generator\vanilla\inventory\AltayEntityEquipment;
use pocketmine\level\generator\vanilla\inventory\ArmorInventory;
use pocketmine\level\generator\vanilla\inventory\utils\EquipmentSlot;
use pocketmine\level\generator\vanilla\item\Armor;
use pocketmine\level\generator\vanilla\item\ArmorSlot;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\nbt\NBT;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\nbt\tag\ListTag;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelEventPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\Player;

use function array_merge;
use function min;

class ArmorStand extends Living
{
	public const NETWORK_ID = EntityIds::ARMOR_STAND;

	public const TAG_MAINHAND = "Mainhand";
	public const TAG_OFFHAND = "Offhand";
	public const TAG_POSE_INDEX = "PoseIndex";
	public const TAG_ARMOR = "Armor";

	/** @var AltayEntityEquipment */
	protected $equipment;

	public float $width = 0.5;
	public float $height = 1.975;

	protected $gravity = 0.04;

	protected $vibrateTimer = 0;

	public function getEquipment() : AltayEntityEquipment
	{
		return $this->equipment;
	}

	protected function initEntity() : void
	{
		$this->setMaxHealth(6);
		$this->setImmobile(true);

		parent::initEntity();

		$this->equipment = new AltayEntityEquipment($this);

		if ($this->namedtag->hasTag(self::TAG_ARMOR, ListTag::class)) {
			$armors = $this->namedtag->getListTag(self::TAG_ARMOR);

			/** @var CompoundTag $armor */
			foreach ($armors as $armor) {
				$slot = $armor->getByte("Slot", 0);

				$this->armorInventory->setItem($slot, Item::nbtDeserialize($armor));
			}
		}

		if ($this->namedtag->hasTag(self::TAG_MAINHAND, CompoundTag::class)) {
			$this->equipment->setItemInHand(Item::nbtDeserialize($this->namedtag->getCompoundTag(self::TAG_MAINHAND)));
		}
		if ($this->namedtag->hasTag(self::TAG_OFFHAND, CompoundTag::class)) {
			$this->equipment->setOffhandItem(Item::nbtDeserialize($this->namedtag->getCompoundTag(self::TAG_OFFHAND)));
		}

		$this->setPose(min($this->namedtag->getInt(self::TAG_POSE_INDEX, 0), 12));
		$this->propertyManager->setString(self::DATA_INTERACTIVE_TAG, "armorstand.change.pose");
	}

	public function setPose(int $pose) : void{
		$this->propertyManager->setInt(self::DATA_ARMOR_STAND_POSE_INDEX, $pose);
	}

	public function getPose() : int{
		return $this->propertyManager->getInt(self::DATA_ARMOR_STAND_POSE_INDEX);
	}

	public function onFirstInteract(Player $player, Vector3 $clickPos) : bool
	{
		if ($player->isSneaking()) {
			$this->setPose(($this->getPose() + 1) % 13);
			return true;
		}

		if ($this->isValid() && !$player->isSpectator()) {
			$item = $player->getInventory()->getItemInHand();
			$targetSlot = EquipmentSlot::MAINHAND;
			$isArmorSlot = false;

			if ($item instanceof Armor) {
				$targetSlot = $item->getArmorSlot();
				$isArmorSlot = true;
			} elseif ($item->getId() === Item::SKULL || $item->getId() === Item::PUMPKIN) {
				$targetSlot = ArmorSlot::SLOT_HELMET;
				$isArmorSlot = true;
			} elseif ($item->isNull()) {
				$clickOffset = $clickPos->y - $this->y;

				if ($clickOffset >= 0.1 && $clickOffset < 0.55 && !$this->armorInventory->getItem(ArmorInventory::SLOT_FEET)->isNull()) {
					$targetSlot = ArmorSlot::SLOT_BOOTS;
					$isArmorSlot = true;
				} elseif ($clickOffset >= 0.9 && $clickOffset < 1.6 && !$this->armorInventory->getItem(ArmorInventory::SLOT_CHEST)->isNull()) {
					$targetSlot = ArmorSlot::SLOT_CHESTPLATE;
					$isArmorSlot = true;
				} elseif ($clickOffset >= 0.4 && $clickOffset < 1.2 && !$this->armorInventory->getItem(ArmorInventory::SLOT_LEGS)->isNull()) {
					$targetSlot = ArmorSlot::SLOT_LEGGINGS;
					$isArmorSlot = true;
				} elseif ($clickOffset >= 1.6 && !$this->armorInventory->getItem(ArmorInventory::SLOT_HEAD)->isNull()) {
					$targetSlot = ArmorSlot::SLOT_HELMET;
					$isArmorSlot = true;
				}
			}

			$this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_MOB_ARMOR_STAND_PLACE);

			$this->tryChangeEquipment($player, $item, $targetSlot, $isArmorSlot);

			return true;
		}

		return false;
	}

	protected function tryChangeEquipment(Player $player, Item $targetItem, int $slot, bool $isArmorSlot = false) : void{
		$sourceItem = $isArmorSlot ? $this->armorInventory->getItem($slot) : $this->equipment->getItem($slot);

		if ($player->isCreative() && $sourceItem->isNull() && !$targetItem->isNull()) {
			$newItem = (clone $targetItem)->setCount(1);
			if ($isArmorSlot) {
				$this->armorInventory->setItem($slot, $newItem);
			} else {
				$this->equipment->setItem($slot, $newItem);
			}
		} elseif ($targetItem->isNull() || $targetItem->getCount() <= 1) {
			if ($isArmorSlot) {
				$this->armorInventory->setItem($slot, $targetItem);
			} else {
				$this->equipment->setItem($slot, $targetItem);
			}
			$player->getInventory()->setItemInHand($sourceItem);
		} elseif (!$sourceItem->isNull()) {
			return;
		} else {
			//$targetItem is a copy of the held item: the held stack itself must shrink, or the item gets duplicated
			$newItem = $targetItem->pop(1);
			if ($isArmorSlot) {
				$this->armorInventory->setItem($slot, $newItem);
			} else {
				$this->equipment->setItem($slot, $newItem);
			}
			$player->getInventory()->setItemInHand($targetItem);
		}

		$this->equipment->sendContents($player);
		$this->armorInventory->sendContents($player);
	}

	public function fall(float $fallDistance) : void
	{
		parent::fall($fallDistance);

		$this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_SOUND_ARMOR_STAND_FALL, $this->getId());
	}

	public function saveNBT() : void
	{
		parent::saveNBT();

		if ($this->equipment instanceof AltayEntityEquipment) {
			$this->namedtag->setTag($this->equipment->getItemInHand()->nbtSerialize(-1, self::TAG_MAINHAND), true);
			$this->namedtag->setTag($this->equipment->getOffhandItem()->nbtSerialize(-1, self::TAG_OFFHAND), true);
		}

		if ($this->armorInventory !== null) {
			$armorTag = new ListTag(self::TAG_ARMOR, [], NBT::TAG_Compound);

			for ($i = 0; $i < 4; $i++) {
				$armorTag->push($this->armorInventory->getItem($i)->nbtSerialize($i));
			}

			$this->namedtag->setTag($armorTag, true);
		}

		$this->namedtag->setInt(self::TAG_POSE_INDEX, $this->getPose(), true);
	}

	public function getDrops() : array
	{
		return array_merge($this->equipment->getContents(), $this->armorInventory->getContents(), [ItemFactory::get(Item::ARMOR_STAND)]);
	}

	public function attack(EntityDamageEvent $source) : void
	{
		if ($source instanceof EntityDamageByEntityEvent) {
			$damager = $source->getDamager();
			if ($damager instanceof Player) {
				if ($damager->isCreative()) {
					$this->kill();
				}
			}
		}
		if ($source->getCause() === EntityDamageEvent::CAUSE_CONTACT) { // cactus
			$source->setCancelled(true);
		}

		Entity::attack($source);

		if (!$source->isCancelled()) {
			$this->setGenericFlag(self::DATA_FLAG_VIBRATING, true);
			$this->vibrateTimer += 30;
		}
	}

	protected function doHitAnimation() : void
	{
		$this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_SOUND_ARMOR_STAND_HIT);
	}

	public function startDeathAnimation() : void
	{
		$this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_SOUND_ARMOR_STAND_BREAK);
		$this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_PARTICLE_ARMOR_STAND_DESTROY);
	}

	protected function onDeathUpdate(int $tickDiff) : bool
	{
		return true;
	}

	protected function sendSpawnPacket(Player $player) : void
	{
		if ($player->getProtocolVersion() >= ProtocolInfo::PROTOCOL_407) {
			parent::sendSpawnPacket($player);

			$this->equipment->sendContents($player);
		}
	}

	public function getName() : string
	{
		return "ArmorStand";
	}

	public function canBePushed() : bool
	{
		return false;
	}

	public function entityBaseTick(int $tickDiff = 1) : bool
	{
		$hasUpdate = parent::entityBaseTick($tickDiff);

		if ($this->getGenericFlag(self::DATA_FLAG_VIBRATING) && $this->vibrateTimer-- <= 0) {
			$this->setGenericFlag(self::DATA_FLAG_VIBRATING, false);
		}

		return $hasUpdate;
	}
}
