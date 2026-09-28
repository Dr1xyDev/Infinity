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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\ItemStack;

class ContainerSetSlotPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::CONTAINER_SET_SLOT_PACKET;

	public int $windowId;
	public int $inventorySlot;
	public int $hotbarSlot = 0;
	public Item|ItemStack $item;
	public int $selectSlot = 0;

	/**
	 * @generate-create-func
	 */
	public static function create(int $windowId, int $inventorySlot, int $hotbarSlot, Item|ItemStack $item, int $selectSlot) : self
	{
		$result = new self();
		$result->windowId = $windowId;
		$result->hotbarSlot = $hotbarSlot;
		$result->inventorySlot = $inventorySlot;
		$result->item = $item;
		$result->selectSlot = $selectSlot;
		return $result;
	}

	protected function decodePayload() : void
	{
		$this->windowId = $this->getByte();
		$this->inventorySlot = $this->getVarInt();
		$this->hotbarSlot = $this->getVarInt();
		$this->item = $this->getItemStackWithoutStackId();
		$this->selectSlot = $this->getByte();
	}

	protected function encodePayload() : void
	{
		$this->putByte($this->windowId);
		$this->putVarInt($this->inventorySlot);
		$this->putVarInt($this->hotbarSlot);
		$this->putItemStackWithoutStackId($this->item);
		$this->putByte($this->selectSlot);
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleContainerSetSlot($this);
	}

}
