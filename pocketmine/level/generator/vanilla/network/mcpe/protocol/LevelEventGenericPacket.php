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

use pocketmine\level\generator\vanilla\nbt\NetworkLittleEndianNBTStream;
use pocketmine\level\generator\vanilla\nbt\tag\CompoundTag;
use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;

class LevelEventGenericPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::LEVEL_EVENT_GENERIC_PACKET;

	public int $eventId;
	/** @var string network-format NBT */
	public string $eventData;

	public static function create(int $eventId, CompoundTag $data) : self
	{
		$result = new self();
		$result->eventId = $eventId;
		$result->eventData = (new NetworkLittleEndianNBTStream())->write($data);
		return $result;
	}

	public function getEventId() : int
	{
		return $this->eventId;
	}

	public function getEventData() : string
	{
		return $this->eventData;
	}

	protected function decodePayload() : void
	{
		$this->eventId = $this->getVarInt();
		$this->eventData = $this->getRemaining();
	}

	protected function encodePayload() : void
	{
		$this->putVarInt($this->eventId);
		($this->buffer .= $this->eventData);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleLevelEventGeneric($this);
	}
}
