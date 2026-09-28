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

use InvalidArgumentException;
use LogicException;
use pocketmine\level\generator\vanilla\entity\Human;
use pocketmine\level\generator\vanilla\event\player\PlayerItemHeldEvent;
use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\network\mcpe\cache\CreativeInventoryCache;
use pocketmine\level\generator\vanilla\network\mcpe\convert\TypeConverter;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\ContainerIds;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\Player;
use function range;

class PlayerInventory extends BaseInventory
{
	/** @var Human */
	protected $holder;
	protected int $itemInHandIndex = 0;

	public function __construct(Human $player)
	{
		$this->holder = $player;
		parent::__construct();
	}

	public function getName() : string
	{
		return "Player";
	}

	public function getDefaultSize() : int
	{
		return 36;
	}

	/**
	 * Called when a client equips a hotbar slot. This method should not be used by plugins.
	 * This method will call PlayerItemHeldEvent.
	 *
	 * @param int $hotbarSlot Number of the hotbar slot to equip.
	 *
	 * @return bool if the equipment change was successful, false if not.
	 */
	public function equipItem(int $hotbarSlot) : bool
	{
		$holder = $this->getHolder();
		if(!$this->isHotbarSlot($hotbarSlot)){
			if($holder instanceof Player){
				$this->sendContents($holder);
			}
			return false;
		}

		if($holder instanceof Player){
			$ev = new PlayerItemHeldEvent($holder, $this->getItem($hotbarSlot), $hotbarSlot);
			$ev->call();

			if($ev->isCancelled()){
				$this->sendHeldItem($holder);
				return false;
			}
		}
		$this->setHeldItemIndex($hotbarSlot, false);

		return true;
	}

	private function isHotbarSlot(int $slot) : bool{
		return $slot >= 0 && $slot <= $this->getHotbarSize();
	}

	/**
	 * @throws InvalidArgumentException
	 */
	private function throwIfNotHotbarSlot(int $slot) : void{
		if(!$this->isHotbarSlot($slot)){
			throw new InvalidArgumentException("$slot is not a valid hotbar slot index (expected 0 - " . ($this->getHotbarSize() - 1) . ")");
		}
	}

	/**
	 * Returns the item in the specified hotbar slot.
	 *
	 * @throws InvalidArgumentException if the hotbar slot index is out of range
	 */
	public function getHotbarSlotItem(int $hotbarSlot) : Item{
		$this->throwIfNotHotbarSlot($hotbarSlot);
		return $this->getItem($hotbarSlot);
	}

	/**
	 * Returns the hotbar slot number the holder is currently holding.
	 */
	public function getHeldItemIndex() : int{
		return $this->itemInHandIndex;
	}

	/**
	 * Sets which hotbar slot the player is currently loading.
	 *
	 * @param int  $hotbarSlot 0-8 index of the hotbar slot to hold
	 * @param bool $send       Whether to send updates back to the inventory holder. This should usually be true for plugin calls.
	 *                         It should only be false to prevent feedback loops of equipment packets between client and server.
	 *
	 * @throws InvalidArgumentException if the hotbar slot is out of range
	 */
	public function setHeldItemIndex(int $hotbarSlot, bool $send = true) : void{
		$this->throwIfNotHotbarSlot($hotbarSlot);

		$this->itemInHandIndex = $hotbarSlot;

		if($this->getHolder() instanceof Player && $send){
			$this->sendHeldItem($this->getHolder());
		}

		$this->sendHeldItem($this->getHolder()->getViewers());
	}

	/**
	 * Returns the currently-held item.
	 */
	public function getItemInHand() : Item{
		return $this->getHotbarSlotItem($this->itemInHandIndex);
	}

	/**
	 * Sets the item in the currently-held slot to the specified item.
	 */
	public function setItemInHand(Item $item) : bool{
		return $this->setItem($this->getHeldItemIndex(), $item);
	}

	/**
	 * Returns the hotbar slot number currently held.
	 * @deprecated
	 */
	public function getHeldItemSlot() : int{
		return $this->itemInHandIndex;
	}

	/**
	 * Sends the currently-held item to specified targets.
	 * @param Player|Player[] $target
	 */
	public function sendHeldItem($target){
		if ($target instanceof Player) {
			$target = [$target];
		}

		/** @var Player[][] $protocolPlayers */
		$protocolPlayers = [];
		foreach ($target as $player) {
			$protocolPlayers[$player->getProtocolVersion()][] = $player;
		}

		foreach ($protocolPlayers as $protocolVersion => $players) {
			$pk = MobEquipmentPacket::create(
				$this->getHolder()->getId(),
				ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet($this->getItemInHand(), $protocolVersion)),
				$this->getHeldItemIndex(),
				$this->getHeldItemIndex(),
				ContainerIds::INVENTORY
			);

			foreach ($players as $player) {
				$player->sendDataPacket($pk);
				if ($player === $this->getHolder()) {
					$this->sendSlot($this->getHeldItemIndex(), $player);
				}
			}
		}
	}

	/**
	 * Returns the number of slots in the hotbar.
	 */
	public function getHotbarSize() : int{
		return 9;
	}

	public function sendCreativeContents() : void{
		//TODO: this mess shouldn't be in here
		$holder = $this->getHolder();
		if (!($holder instanceof Player)) {
			throw new LogicException("Cannot send creative inventory contents to non-player inventory holder");
		}

		$holder->sendDataPacket(CreativeInventoryCache::getInstance()->buildPacket($holder));
	}

	/**
	 * This override is here for documentation and code completion purposes only.
	 * @return Human|Player
	 */
	public function getHolder(){
		return $this->holder;
	}
}
