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

namespace pocketmine\level\generator\vanilla\inventory;

use pocketmine\level\generator\vanilla\event\inventory\InventoryOpenEvent;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\item\ItemFactory;
use pocketmine\level\generator\vanilla\item\ItemIds;
use pocketmine\level\generator\vanilla\level\Level;
use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\convert\TypeConverter;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\ContainerIds;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\Player;
use pocketmine\level\generator\vanilla\utils\AssumptionFailedError;
use SplFixedArray;

use function array_map;
use function array_slice;
use function array_values;
use function count;
use function max;
use function min;
use function range;
use function spl_object_hash;

abstract class BaseInventory implements Inventory
{
	/** @var int */
	protected $maxStackSize = Inventory::MAX_STACK;
	/** @var string */
	protected $name;
	/** @var string */
	protected $title;
	/** @var SplFixedArray|Item[] */
	protected $slots;
	/** @var Player[] */
	protected $viewers = [];
	/** @var InventoryEventProcessor */
	protected $eventProcessor;

	/**
	 * @param Item[] $items
	 */
	public function __construct(array $items = [], ?int $size = null, ?string $title = null)
	{
		$this->slots = new SplFixedArray($size ?? $this->getDefaultSize());
		$this->title = $title ?? $this->getName();

		$this->setContents($items, false);
	}

	abstract public function getName() : string;

	public function getTitle() : string
	{
		return $this->title;
	}

	/**
	 * Returns the size of the inventory.
	 */
	public function getSize() : int
	{
		return $this->slots->getSize();
	}

	/**
	 * Sets the new size of the inventory.
	 * WARNING: If the size is smaller, any items past the new size will be lost.
	 */
	public function setSize(int $size)
	{
		$this->slots->setSize($size);
	}

	abstract public function getDefaultSize() : int;

	public function getMaxStackSize() : int
	{
		return $this->maxStackSize;
	}

	public function getItem(int $index) : Item
	{
		return isset($this->slots[$index]) ? clone $this->slots[$index] : ItemFactory::get(Item::AIR, 0, 0);
	}

	/**
	 * Returns the raw slot storage (SplFixedArray of Item|null).
	 * WARNING: Do NOT mutate items in this array directly; this is intended for read-only fast paths
	 * such as inventory inspection (isEmpty/isFull checks). Bypasses cloning for performance.
	 *
	 * @return SplFixedArray|Item[]
	 */
	public function getSlots() : SplFixedArray
	{
		return $this->slots;
	}

	/**
	 * @return Item[]
	 */
	public function getContents(bool $includeEmpty = false) : array
	{
		$contents = [];

		foreach ($this->slots as $i => $slot) {
			if ($slot !== null) {
				$contents[$i] = clone $slot;
			} elseif ($includeEmpty) {
				$contents[$i] = ItemFactory::get(Item::AIR, 0, 0);
			}
		}

		return $contents;
	}

	/**
	 * @param Item[] $items
	 */
	public function setContents(array $items, bool $send = true) : void
	{
		if (count($items) > $this->getSize()) {
			$items = array_slice($items, 0, $this->getSize(), true);
		}

		for ($i = 0, $size = $this->getSize(); $i < $size; ++$i) {
			if (!isset($items[$i])) {
				if ($this->slots[$i] !== null) {
					$this->clear($i, false);
				}
			} else {
				if (!$this->setItem($i, $items[$i], false)) {
					$this->clear($i, false);
				}
			}
		}

		if ($send) {
			$this->sendContents($this->getViewers());
		}
	}

	/**
	 * Drops the contents of the inventory into the specified Level at the specified position and clears the inventory
	 * contents.
	 */
	public function dropContents(Level $level, Vector3 $position) : void
	{
		foreach ($this->getContents() as $item) {
			$level->dropItem($position, $item);
		}

		$this->clearAll();
	}

	public function setItem(int $index, Item $item, bool $send = true) : bool
	{
		if ($index < 0 || $index >= $this->slots->getSize()) {
			return false;
		}

		if ($item->isNull()) {
			$item = ItemFactory::get(Item::AIR, 0, 0);
		} else {
			$item = clone $item;
		}

		$oldItem = $this->getItem($index);
		if ($this->eventProcessor !== null) {
			$newItem = $this->eventProcessor->onSlotChange($this, $index, $oldItem, $item);
			if ($newItem === null) {
				return false;
			}
		} else {
			$newItem = $item;
		}

		$this->slots[$index] = $newItem->isNull() ? null : $newItem;
		$this->onSlotChange($index, $oldItem, $send);

		return true;
	}

	/**
	 * Helper for utility functions which search the inventory.
	 * TODO: make this abstract instead of providing a slow default implementation (BC break)
	 */
	protected function getMatchingItemCount(int $slot, Item $test, bool $checkDamage, bool $checkTags) : int{
		$item = $this->getItem($slot);
		return $item->equals($test, $checkDamage, $checkTags) ? $item->getCount() : 0;
	}

	public function contains(Item $item) : bool{
		$count = max(1, $item->getCount());
		$checkDamage = !$item->hasAnyDamageValue();
		$checkTags = $item->hasNamedTag();
		for($i = 0, $size = $this->getSize(); $i < $size; $i++){
			$slotCount = $this->getMatchingItemCount($i, $item, $checkDamage, $checkTags);
			if($slotCount > 0){
				$count -= $slotCount;
				if($count <= 0){
					return true;
				}
			}
		}

		return false;
	}

	public function all(Item $item) : array{
		$slots = [];
		$checkDamage = !$item->hasAnyDamageValue();
		$checkTags = $item->hasNamedTag();
		for($i = 0, $size = $this->getSize(); $i < $size; $i++){
			if($this->getMatchingItemCount($i, $item, $checkDamage, $checkTags) > 0){
				$slots[$i] = $this->getItem($i);
			}
		}

		return $slots;
	}

	public function remove(Item $item) : void
	{
		$checkDamage = !$item->hasAnyDamageValue();
		$checkTags = $item->hasNamedTag();

		for($i = 0, $size = $this->getSize(); $i < $size; $i++){
			if($this->getMatchingItemCount($i, $item, $checkDamage, $checkTags) > 0){
				$this->clear($i);
			}
		}
	}

	public function first(Item $item, bool $exact = false) : int{
		$count = $exact ? $item->getCount() : max(1, $item->getCount());
		$checkDamage = $exact || !$item->hasAnyDamageValue();
		$checkTags = $exact || $item->hasNamedTag();

		for($i = 0, $size = $this->getSize(); $i < $size; $i++){
			$slotCount = $this->getMatchingItemCount($i, $item, $checkDamage, $checkTags);
			if($slotCount > 0 && ($slotCount === $count || (!$exact && $slotCount > $count))){
				return $i;
			}
		}

		return -1;
	}

	public function firstEmpty() : int
	{
		foreach ($this->slots as $i => $slot) {
			if ($slot === null || $slot->isNull()) {
				return $i;
			}
		}

		return -1;
	}

	public function firstOccupied() : int
	{
		for ($i = 0; $i < $this->getSize(); $i++) {
			if (($item = $this->getItem($i))->getId() !== ItemIds::AIR && $item->getCount() > 0) {
				return $i;
			}
		}

		return -1;
	}

	public function isSlotEmpty(int $index) : bool
	{
		return $this->slots[$index] === null || $this->slots[$index]->isNull();
	}

	public function canAddItem(Item $item) : bool{
		return $this->getAddableItemQuantity($item) === $item->getCount();
	}

	public function getAddableItemQuantity(Item $item) : int{
		$count = $item->getCount();
		$maxStackSize = min($this->getMaxStackSize(), $item->getMaxStackSize());

		for($i = 0, $size = $this->getSize(); $i < $size; ++$i){
			if($this->isSlotEmpty($i)){
				$count -= $maxStackSize;
			}else{
				$slotCount = $this->getMatchingItemCount($i, $item, true, true);
				if($slotCount > 0 && ($diff = $maxStackSize - $slotCount) > 0){
					$count -= $diff;
				}
			}

			if($count <= 0){
				return $item->getCount();
			}
		}

		return $item->getCount() - $count;
	}

	public function addItem(Item ...$slots) : array
	{
		/** @var Item[] $itemSlots */
		/** @var Item[] $slots */
		$itemSlots = [];
		foreach ($slots as $slot) {
			if (!$slot->isNull()) {
				$itemSlots[] = clone $slot;
			}
		}

		$emptySlots = [];

		for ($i = 0, $size = $this->getSize(); $i < $size; ++$i) {
			$item = $this->getItem($i);
			if ($item->isNull()) {
				$emptySlots[] = $i;
				continue;
			}

			foreach ($itemSlots as $index => $slot) {
				$slotCount = $this->getMatchingItemCount($i, $slot, true, true);
				if($slotCount === 0){
					continue;
				}

				$maxStackSize = min($this->getMaxStackSize(), $slot->getMaxStackSize());
				if($slotCount < $maxStackSize){
					$amount = min($maxStackSize - $slotCount, $slot->getCount());
					if ($amount > 0) {
						$slot->setCount($slot->getCount() - $amount);
						$item->setCount($item->getCount() + $amount);
						$this->setItem($i, $item);
						if ($slot->getCount() <= 0) {
							unset($itemSlots[$index]);
						}
					}
				}
			}

			if (count($itemSlots) === 0) {
				break;
			}
		}

		if (count($itemSlots) > 0 && count($emptySlots) > 0) {
			foreach ($emptySlots as $slotIndex) {
				//This loop only gets the first item, then goes to the next empty slot
				foreach ($itemSlots as $index => $slot) {
					$amount = min($slot->getMaxStackSize(), $slot->getCount(), $this->getMaxStackSize());
					$slot->setCount($slot->getCount() - $amount);
					$item = clone $slot;
					$item->setCount($amount);
					$this->setItem($slotIndex, $item);
					if ($slot->getCount() <= 0) {
						unset($itemSlots[$index]);
					}
					break;
				}
			}
		}

		return $itemSlots;
	}

	public function removeItem(Item ...$slots) : array
	{
		/** @var Item[] $itemSlots */
		/** @var Item[] $slots */
		$itemSlots = [];
		foreach ($slots as $slot) {
			if (!$slot->isNull()) {
				$itemSlots[] = clone $slot;
			}
		}

		for($i = 0, $size = $this->getSize(); $i < $size; ++$i){
			if($this->isSlotEmpty($i)){
				continue;
			}

			foreach ($itemSlots as $index => $slot) {
				$slotCount = $this->getMatchingItemCount($i, $slot, !$slot->hasAnyDamageValue(), $slot->hasNamedTag());
				if($slotCount > 0){
					$amount = min($slotCount, $slot->getCount());
					$slot->setCount($slot->getCount() - $amount);

					$slotItem = $this->getItem($i);
					$slotItem->setCount($slotItem->getCount() - $amount);
					$this->setItem($i, $slotItem);
					if($slot->getCount() <= 0){
						unset($itemSlots[$index]);
					}
				}
			}

			if (count($itemSlots) === 0) {
				break;
			}
		}

		return $itemSlots;
	}

	public function clear(int $index, bool $send = true) : bool
	{
		return $this->setItem($index, ItemFactory::get(Item::AIR, 0, 0), $send);
	}

	public function clearAll(bool $send = true) : void
	{
		for ($i = 0, $size = $this->getSize(); $i < $size; ++$i) {
			$this->clear($i, false);
		}

		if ($send) {
			$this->sendContents($this->getViewers());
		}
	}

	/**
	 * @return Player[]
	 */
	public function getViewers() : array
	{
		return $this->viewers;
	}

	/**
	 * Removes the inventory window from all players currently viewing it.
	 *
	 * @param bool $force Force removal of permanent windows such as the player's own inventory. Used internally.
	 */
	public function removeAllViewers(bool $force = false) : void
	{
		foreach ($this->viewers as $hash => $viewer) {
			$viewer->removeWindow($this, $force);
			unset($this->viewers[$hash]);
		}
	}

	public function setMaxStackSize(int $size) : void
	{
		$this->maxStackSize = $size;
	}

	public function open(Player $who) : bool
	{
		$ev = new InventoryOpenEvent($this, $who);
		$ev->call();
		if ($ev->isCancelled()) {
			return false;
		}
		$this->onOpen($who);

		return true;
	}

	public function close(Player $who) : void
	{
		$this->onClose($who);
	}

	public function onOpen(Player $who) : void
	{
		$this->viewers[spl_object_hash($who)] = $who;
	}

	public function onClose(Player $who) : void
	{
		unset($this->viewers[spl_object_hash($who)]);
	}

	public function onSlotChange(int $index, Item $before, bool $send) : void
	{
		if ($send) {
			$this->sendSlot($index, $this->getViewers());
		}
	}

	/**
	 * @param Player|Player[] $target
	 */
	public function sendContents($target) : void
	{
		if ($target instanceof Player) {
			$target = [$target];
		}

		$contents = $this->getContents(true);
		$typeConverter = TypeConverter::getInstance();

		/** @var Player[][] $protocolPlayers */
		$protocolPlayers = [];
		foreach ($target as $player) {
			$protocolPlayers[$player->getProtocolVersion()][] = $player;
		}

		foreach ($protocolPlayers as $protocolVersion => $players) {
			$itemStacks = array_map(fn(Item $item) => $typeConverter->coreItemStackToNet($item, $protocolVersion), $contents);

			foreach ($players as $player) {
				if (($windowId = $player->getWindowId($this)) === ContainerIds::NONE) {
					$this->close($player);
					continue;
				}

				$hotbar = [];
				if ($this instanceof PlayerInventory && $player->getProtocolVersion() < ProtocolInfo::PROTOCOL_407) {
					$air = $typeConverter->coreItemStackToNet(ItemFactory::air(), $protocolVersion);
					for ($i = 0; $i < $this->getHotbarSize(); $i++) {
						$itemStacks[] = clone $air;
					}

					$hotbar = range($this->getHotbarSize(), $this->getHotbarSize() * 2, 1);
				}

				if ($this instanceof FakeInventory) {
					$netSlots = array_values($this->getUIOffsets($player));
					foreach ($itemStacks as $slotId => $itemStack) {
						$packetSlot = $netSlots[$slotId] ?? null;
						if ($packetSlot === null) {
							continue;
						}
						$player->sendInventorySlotPackets($windowId, $packetSlot, ItemStackWrapper::legacy($itemStack));
					}
				} else {
					$player->sendInventoryContentPackets($windowId, array_map(fn(ItemStack $itemStack) => ItemStackWrapper::legacy($itemStack), $itemStacks), $hotbar);
				}
			}
		}
	}

	/**
	 * @param Player|Player[] $target
	 */
	public function sendSlot(int $index, $target) : void
	{
		if ($target instanceof Player) {
			$target = [$target];
		}

		$item = $this->getItem($index);
		$typeConverter = TypeConverter::getInstance();

		/** @var Player[][] $protocolPlayers */
		$protocolPlayers = [];
		foreach ($target as $player) {
			$protocolPlayers[$player->getProtocolVersion()][] = $player;
		}

		foreach ($protocolPlayers as $protocolVersion => $players) {
			$itemStack = $typeConverter->coreItemStackToNet($item, $protocolVersion);

			foreach ($players as $player) {
				if (($windowId = $player->getWindowId($this)) === ContainerIds::NONE) {
					$this->close($player);
					continue;
				}

				$netSlot = $index;
				if ($this instanceof FakeInventory) {
					$windowId = ContainerIds::UI;
					$netSlot = array_values($this->getUIOffsets($player))[$index] ?? throw new AssumptionFailedError("We already have an ItemStackInfo, so this should not be null");
				}

				if ($windowId === ContainerIds::OFFHAND) {
					//TODO: HACK!
					//The client may sometimes ignore the InventorySlotPacket for the offhand slot.
					//This can cause a lot of problems (totems, arrows, and more...).
					//The workaround is to send an InventoryContentPacket instead
					//BDS (Bedrock Dedicated Server) also seems to work this way.
					$player->sendInventoryContentPackets($windowId, [ItemStackWrapper::legacy($itemStack)]);
				} else {
					$player->sendInventorySlotPackets($windowId, $netSlot, ItemStackWrapper::legacy($itemStack));
				}
			}
		}
	}

	public function slotExists(int $slot) : bool
	{
		return $slot >= 0 && $slot < $this->slots->getSize();
	}

	public function getEventProcessor() : ?InventoryEventProcessor
	{
		return $this->eventProcessor;
	}

	public function setEventProcessor(?InventoryEventProcessor $eventProcessor) : void
	{
		$this->eventProcessor = $eventProcessor;
	}

	public function findSlot(Item $itemFind, bool $checkCount = true) : int
	{
		for ($i = 0, $size = $this->getSize(); $i < $size; ++$i) {
			$item = $this->getItem($i);
			if ($item->isNull()) {
				continue;
			}

			if ($itemFind->equals($item, !$itemFind->hasAnyDamageValue(), $itemFind->hasCompoundTag())) {
				if ($checkCount && $item->getCount() < $itemFind->getCount()) {
					continue;
				}

				return $i;
			}
		}

		return -1;
	}
}
