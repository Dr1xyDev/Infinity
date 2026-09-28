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

class UpdateEquipPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::UPDATE_EQUIP_PACKET;

	/** @var int */
	public $windowId;
	/** @var int */
	public $windowType;
	/** @var int */
	public $windowSlotCount; //useless, seems to be part of a standard container header
	/** @var int */
	public $entityUniqueId;
	/** @var string */
	public $namedtag;

	protected function decodePayload() : void
	{
		$this->windowId = $this->getByte();
		$this->windowType = $this->getByte();
		$this->windowSlotCount = $this->getVarInt();
		$this->entityUniqueId = $this->getEntityUniqueId();
		$this->namedtag = $this->getRemaining();
	}

	protected function encodePayload() : void
	{
		$this->putByte($this->windowId);
		$this->putByte($this->windowType);
		$this->putVarInt($this->windowSlotCount);
		$this->putEntityUniqueId($this->entityUniqueId);
		$this->put($this->namedtag);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleUpdateEquip($this);
	}
}
