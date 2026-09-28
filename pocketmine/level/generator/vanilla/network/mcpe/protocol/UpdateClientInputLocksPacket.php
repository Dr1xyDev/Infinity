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

use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;

class UpdateClientInputLocksPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::UPDATE_CLIENT_INPUT_LOCKS_PACKET;

	private int $flags;
	private Vector3 $position;

	/**
	 * @generate-create-func
	 */
	public static function create(int $flags, Vector3 $position) : self
	{
		$result = new self();
		$result->flags = $flags;
		$result->position = $position;
		return $result;
	}

	protected function decodePayload() : void
	{
		$this->flags = $this->getUnsignedVarInt();
		if ($this->protocol < ProtocolInfo::PROTOCOL_944) {
			$this->position = $this->getVector3();
		}
	}

	protected function encodePayload() : void
	{
		$this->putUnsignedVarInt($this->flags);
		if ($this->protocol < ProtocolInfo::PROTOCOL_944) {
			$this->putVector3($this->position);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleUpdateClientInputLocks($this);
	}
}
