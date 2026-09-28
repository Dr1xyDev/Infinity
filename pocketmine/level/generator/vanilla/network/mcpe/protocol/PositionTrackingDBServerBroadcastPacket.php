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

class PositionTrackingDBServerBroadcastPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::POSITION_TRACKING_D_B_SERVER_BROADCAST_PACKET;

	public const int ACTION_UPDATE = 0;
	public const int ACTION_DESTROY = 1;
	public const int ACTION_NOT_FOUND = 2;

	public int $action;
	public int $trackingId;
	public CompoundTag $nbt;

	public static function create(int $action, int $trackingId, CompoundTag $nbt) : self
	{
		$result = new self();
		$result->action = $action;
		$result->trackingId = $trackingId;
		$result->nbt = $nbt;
		return $result;
	}

	public function getAction() : int
	{
		return $this->action;
	}

	public function getTrackingId() : int
	{
		return $this->trackingId;
	}

	public function getNbt() : CompoundTag
	{
		return $this->nbt;
	}

	protected function decodePayload() : void
	{
		$this->action = $this->getByte();
		$this->trackingId = $this->getVarInt();
		$offset = $this->getOffset();
		$nbt = (new NetworkLittleEndianNBTStream())->read($this->getBuffer(), false, $offset);
		$this->setOffset($offset);
		if (!($nbt instanceof CompoundTag)) {
			throw new PacketDecodeException("Expected TAG_Compound");
		}
		$this->nbt = $nbt;
	}

	protected function encodePayload() : void
	{
		$this->putByte($this->action);
		$this->putVarInt($this->trackingId);
		$this->put((new NetworkLittleEndianNBTStream())->write($this->nbt));
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handlePositionTrackingDBServerBroadcast($this);
	}
}
