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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol;

use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\FullContainerName;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\ItemStackWrapper;

class InventorySlotPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::INVENTORY_SLOT_PACKET;

	public int $windowId;
	public int $inventorySlot;
	public int $isNullItem;
	public ?FullContainerName $containerName;
	public ?ItemStackWrapper $storage;
	public int $dynamicContainerSize = 0;
	public ItemStackWrapper $item;

	/**
	 * @generate-create-func
	 */
	public static function create(int $windowId, int $inventorySlot, ?FullContainerName $containerName, int $dynamicContainerSize, ?ItemStackWrapper $storage, ItemStackWrapper $item) : self
	{
		$result = new self();
		$result->windowId = $windowId;
		$result->inventorySlot = $inventorySlot;
		$result->containerName = $containerName;
		$result->dynamicContainerSize = $dynamicContainerSize;
		$result->storage = $storage;
		$result->item = $item;
		return $result;
	}

	protected function decodePayload() : void
	{
		$this->windowId = $this->getUnsignedVarInt();
		$this->inventorySlot = $this->getUnsignedVarInt();

		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_975) {
			$this->containerName = $this->getOptional(fn() => FullContainerName::read($this));
			$this->storage = $this->getOptional(fn () => $this->getNetworkItemStackDescriptor(ProtocolInfo::PROTOCOL_975));
		} else {
			if ($this->protocol >= ProtocolInfo::PROTOCOL_407 && $this->protocol <= ProtocolInfo::PROTOCOL_428) {
				$this->isNullItem = $this->getVarInt();
			}

			if ($this->protocol >= ProtocolInfo::PROTOCOL_712) {
				if ($this->protocol < ProtocolInfo::PROTOCOL_729) {
					$this->containerName = new FullContainerName($this->windowId, $this->getUnsignedVarInt());
				} else {
					$this->containerName = FullContainerName::read($this);
					if ($this->protocol >= ProtocolInfo::PROTOCOL_748) {
						$this->storage = $this->getItemStackWrapper();
					} else {
						$this->dynamicContainerSize = $this->getUnsignedVarInt();
					}
				}
			}
		}

		$this->item = $this->getNetworkItemStackDescriptor(ProtocolInfo::PROTOCOL_975);
	}

	protected function encodePayload() : void
	{
		$this->putUnsignedVarInt($this->windowId);
		$this->putUnsignedVarInt($this->inventorySlot);

		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_975) {
			$this->putOptional($this->containerName, fn(FullContainerName $v) => $v->write($this));
			$this->putOptional($this->storage, fn(ItemStackWrapper $v) => $this->putNetworkItemStackDescriptor($v, ProtocolInfo::PROTOCOL_975));
		} else {
			if ($this->protocol >= ProtocolInfo::PROTOCOL_407 && $this->protocol <= ProtocolInfo::PROTOCOL_428) {
				$this->putVarInt($this->item->getItemStack()->isNull() ? 0 : 1);
			}

			if ($this->protocol >= ProtocolInfo::PROTOCOL_712) {
				$containerName = $this->containerName ?? new FullContainerName(0);
				if ($this->protocol < ProtocolInfo::PROTOCOL_729) {
					$this->putUnsignedVarInt($containerName->getDynamicId() === null ? 0 : $containerName->getDynamicId());
				} else {
					$containerName->write($this);
					if ($this->protocol >= ProtocolInfo::PROTOCOL_748) {
						$this->putItemStackWrapper($this->storage ?? ItemStackWrapper::legacy(ItemStack::null()));
					} else {
						$this->putUnsignedVarInt($this->dynamicContainerSize);
					}
				}
			}
		}

		$this->putNetworkItemStackDescriptor($this->item, ProtocolInfo::PROTOCOL_975);

	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleInventorySlot($this);
	}
}
