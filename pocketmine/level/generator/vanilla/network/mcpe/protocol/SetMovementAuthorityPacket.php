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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\ServerAuthMovementMode;

class SetMovementAuthorityPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::SET_MOVEMENT_AUTHORITY_PACKET;

	private ServerAuthMovementMode $mode;

	/**
	 * @generate-create-func
	 */
	public static function create(ServerAuthMovementMode $mode) : self
	{
		$result = new self();
		$result->mode = $mode;
		return $result;
	}

	public function getMode() : ServerAuthMovementMode
	{
		return $this->mode;
	}

	protected function decodePayload() : void
	{
		$this->mode = ServerAuthMovementMode::fromPacket($this->getByte());
	}

	protected function encodePayload() : void
	{
		$this->putByte($this->mode->value);
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleSetMovementAuthority($this);
	}
}
