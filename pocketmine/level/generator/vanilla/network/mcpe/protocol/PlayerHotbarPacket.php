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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\inventory\ContainerIds;

use function count;

/**
 * One of the most useless packets.
 */
class PlayerHotbarPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::PLAYER_HOTBAR_PACKET;

	/** @var int */
	public $selectedHotbarSlot;
	/** @var int */
	public $windowId = ContainerIds::INVENTORY;
	/** @var array */
	public $slots = [];
	/** @var bool */
	public $selectHotbarSlot = true;

	protected function decodePayload() : void
	{
		$this->selectedHotbarSlot = $this->getUnsignedVarInt();
		$this->windowId = $this->getByte();
		if ($this->protocol <= ProtocolInfo::PROTOCOL_407) {
			$slots = $this->getUnsignedVarInt();
			for ($slot = 0; $slot < $slots; ++$slot) {
				$this->slots[$slot] = $this->getUnsignedVarInt();
			}
		}
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->selectHotbarSlot = $this->getBool();
		}
	}

	protected function encodePayload() : void
	{
		$this->putUnsignedVarInt($this->selectedHotbarSlot);
		$this->putByte($this->windowId);
		if ($this->protocol <= ProtocolInfo::PROTOCOL_407) {
			$this->putUnsignedVarInt(count($this->slots));
			foreach ($this->slots as $slot) {
				$this->putUnsignedVarInt($slot);
			}
		}
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->putBool($this->selectHotbarSlot);
		}
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handlePlayerHotbar($this);
	}
}
