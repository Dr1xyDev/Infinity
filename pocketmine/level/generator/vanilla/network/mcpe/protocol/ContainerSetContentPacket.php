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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\ItemStack;

use function count;

class ContainerSetContentPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::CONTAINER_SET_CONTENT_PACKET;

	public int $windowId;
	public int $entityUniqueId;
	/** @var Item[]|ItemStack[] */
	public array $items = [];
	/** @var int[] */
	public array $hotbar = [];

	/**
	 * @generate-create-func
	 * @param Item[]|ItemStack[] $items
	 * @param int[]              $hotbar
	 */
	public static function create(int $windowId, int $entityUniqueId, array $items, array $hotbar) : self
	{
		$result = new self();
		$result->windowId = $windowId;
		$result->entityUniqueId = $entityUniqueId;
		$result->items = $items;
		$result->hotbar = $hotbar;
		return $result;
	}

	protected function decodePayload() : void
	{
		$this->windowId = $this->getUnsignedVarInt();
		$this->entityUniqueId = $this->getEntityUniqueId();
		$count = $this->getUnsignedVarInt();
		for ($s = 0; $s < $count && !$this->feof(); ++$s) {
			$this->items[$s] = $this->getItemStackWithoutStackId();
		}

		$hotbarCount = $this->getUnsignedVarInt(); //MCPE always sends this, even when it's not a player inventory
		for ($s = 0; $s < $hotbarCount && !$this->feof(); ++$s) {
			$this->hotbar[$s] = $this->getVarInt();
		}
	}

	protected function encodePayload() : void
	{
		$this->putUnsignedVarInt($this->windowId);
		$this->putEntityUniqueId($this->entityUniqueId);
		$this->putUnsignedVarInt(count($this->items));
		foreach ($this->items as $slot) {
			$this->putItemStackWithoutStackId($slot);
		}

		if ($this->windowId === ContainerIds::INVENTORY && count($this->hotbar) > 0) {
			$this->putUnsignedVarInt(count($this->hotbar));
			foreach ($this->hotbar as $slot) {
				$this->putVarInt($slot);
			}
		} else {
			$this->putUnsignedVarInt(0);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleContainerSetContent($this);
	}

}
