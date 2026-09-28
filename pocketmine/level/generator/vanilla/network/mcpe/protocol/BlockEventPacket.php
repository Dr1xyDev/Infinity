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

class BlockEventPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::BLOCK_EVENT_PACKET;

	public const TYPE_CHEST = 1;

	public const DATA_CHEST_CLOSED = 0;
	public const DATA_CHEST_OPEN = 1;

	public int $x = 0;
	public int $y = 0;
	public int $z = 0;
	public int $eventType;
	public int $eventData;

	public static function create(int $x, int $y, int $z, int $eventType, int $eventData) : self
	{
		$result = new self();
		$result->x = $x;
		$result->y = $y;
		$result->z = $z;
		$result->eventType = $eventType;
		$result->eventData = $eventData;
		return $result;
	}

	protected function decodePayload() : void
	{
		$this->getBlockPosition($this->x, $this->y, $this->z);
		$this->eventType = $this->getVarInt();
		$this->eventData = $this->getVarInt();
	}

	protected function encodePayload() : void
	{
		$this->putBlockPosition($this->x, $this->y, $this->z);
		$this->putVarInt($this->eventType);
		$this->putVarInt($this->eventData);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleBlockEvent($this);
	}
}
