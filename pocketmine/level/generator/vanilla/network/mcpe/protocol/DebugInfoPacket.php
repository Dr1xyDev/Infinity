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

class DebugInfoPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::DEBUG_INFO_PACKET;

	public int $entityUniqueId;
	public string $data;

	public static function create(int $entityUniqueId, string $data) : self
	{
		$result = new self();
		$result->entityUniqueId = $entityUniqueId;
		$result->data = $data;
		return $result;
	}

	/**
	 * TODO: we can't call this getEntityRuntimeId() because of base class collision (crap architecture, thanks Shoghi)
	 */
	public function getEntityUniqueIdField() : int
	{
		return $this->entityUniqueId;
	}

	public function getData() : string
	{
		return $this->data;
	}

	protected function decodePayload() : void
	{
		$this->entityUniqueId = $this->getEntityUniqueId();
		$this->data = $this->getString();
	}

	protected function encodePayload() : void
	{
		$this->putEntityUniqueId($this->entityUniqueId);
		$this->putString($this->data);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleDebugInfo($this);
	}
}
