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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\FullContainerName;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\ItemStackWrapper;

use function count;

class InventoryContentPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::INVENTORY_CONTENT_PACKET;

	public int $windowId;
	/** @var Item[]|ItemStackWrapper[] */
	public array $items = [];
	/** @var int[] */
	public array $index = [];
	public FullContainerName $containerName;
	public int $dynamicContainerSize = 0;
	public Item|ItemStackWrapper $storage;

	/**
	 * @generate-create-func
	 * @param Item[]|ItemStackWrapper[] $items
	 */
	public static function create(int $windowId, array $items, FullContainerName $containerName, int $dynamicContainerSize, Item|ItemStackWrapper $storage) : self
	{
		$result = new self();
		$result->windowId = $windowId;
		$result->items = $items;
		$result->containerName = $containerName;
		$result->dynamicContainerSize = $dynamicContainerSize;
		$result->storage = $storage;
		return $result;
	}

	protected function decodePayload() : void
	{
		$this->windowId = $this->getUnsignedVarInt();
		$count = $this->getUnsignedVarInt();
		for ($i = 0; $i < $count; ++$i) {
			if ($this->protocol >= ProtocolInfo::PROTOCOL_407 && $this->protocol <= ProtocolInfo::PROTOCOL_428) {
				$this->index[] = $this->getVarInt();
			}

			$this->items[] = $this->getNetworkItemStackDescriptor();
		}

		if ($this->protocol >= ProtocolInfo::PROTOCOL_712) {
			if ($this->protocol < ProtocolInfo::PROTOCOL_729) {
				$this->containerName = new FullContainerName($this->windowId, $this->getUnsignedVarInt());
			} else {
				$this->containerName = FullContainerName::read($this);
				if ($this->protocol >= ProtocolInfo::PROTOCOL_748) {
					$this->storage = $this->getNetworkItemStackDescriptor();
				} else {
					$this->dynamicContainerSize = $this->getUnsignedVarInt();
				}
			}
		}
	}

	protected function encodePayload() : void
	{
		$this->putUnsignedVarInt($this->windowId);
		$this->putUnsignedVarInt(count($this->items));
		$index = 1;
		foreach ($this->items as $item) {
			if ($this->protocol >= ProtocolInfo::PROTOCOL_407 && $this->protocol <= ProtocolInfo::PROTOCOL_428) {
				if ($item->getStackId() === 0) {
					$this->putVarInt(0);
				} else {
					$this->putVarInt($index++);
				}
			}

			$this->putNetworkItemStackDescriptor($item);
		}

		if ($this->protocol >= ProtocolInfo::PROTOCOL_712) {
			$containerName = $this->containerName ?? new FullContainerName(0);
			if ($this->protocol < ProtocolInfo::PROTOCOL_729) {
				$this->putUnsignedVarInt($containerName->getDynamicId() === null ? 0 : $containerName->getDynamicId());
			} else {
				$containerName->write($this);
				if ($this->protocol >= ProtocolInfo::PROTOCOL_748) {
					$this->putNetworkItemStackDescriptor($this->storage ?? ItemStackWrapper::legacy(ItemStack::null()));
				} else {
					$this->putUnsignedVarInt($this->dynamicContainerSize);
				}
			}
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleInventoryContent($this);
	}
}
