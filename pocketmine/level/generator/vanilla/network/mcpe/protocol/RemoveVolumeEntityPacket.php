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

class RemoveVolumeEntityPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::REMOVE_VOLUME_ENTITY_PACKET;

	public int $entityNetId;
	public int $dimension;

	public static function create(int $entityNetId, int $dimension) : self
	{
		$result = new self();
		$result->entityNetId = $entityNetId;
		$result->dimension = $dimension;
		return $result;
	}

	public function getEntityNetId() : int
	{
		return $this->entityNetId;
	}

	public function getDimension() : int
	{
		return $this->dimension;
	}

	protected function decodePayload() : void
	{
		$this->entityNetId = $this->getUnsignedVarInt();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_503) {
			$this->dimension = $this->getVarInt();
		}
	}

	protected function encodePayload() : void
	{
		$this->putUnsignedVarInt($this->entityNetId);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_503) {
			$this->putVarInt($this->dimension);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleRemoveVolumeEntity($this);
	}
}
