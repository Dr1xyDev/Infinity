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

use pocketmine\level\generator\vanilla\item\Item;
use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\ContainerIds;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\ItemStackWrapper;

class MobEquipmentPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::MOB_EQUIPMENT_PACKET;

	public int $entityRuntimeId;
	public Item|ItemStackWrapper $item;
	public int $inventorySlot;
	public int $hotbarSlot;
	public int $windowId = ContainerIds::INVENTORY;

	/**
	 * @generate-create-func
	 */
	public static function create(int $entityRuntimeId, Item|ItemStackWrapper $item, int $inventorySlot, int $hotbarSlot, int $windowId) : self{
		$result = new self();
		$result->entityRuntimeId = $entityRuntimeId;
		$result->item = $item;
		$result->inventorySlot = $inventorySlot;
		$result->hotbarSlot = $hotbarSlot;
		$result->windowId = $windowId;
		return $result;
	}

	protected function decodePayload() : void
	{
		$this->entityRuntimeId = $this->getEntityRuntimeId();
		$this->item = $this->getNetworkItemStackDescriptor(ProtocolInfo::PROTOCOL_975);
		$this->inventorySlot = $this->getByte();
		$this->hotbarSlot = $this->getByte();
		$this->windowId = $this->getByte();
	}

	protected function encodePayload() : void
	{
		$this->putEntityRuntimeId($this->entityRuntimeId);
		$this->putNetworkItemStackDescriptor($this->item, ProtocolInfo::PROTOCOL_975);
		$this->putByte($this->inventorySlot);
		$this->putByte($this->hotbarSlot);
		$this->putByte($this->windowId);
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleMobEquipment($this);
	}
}
