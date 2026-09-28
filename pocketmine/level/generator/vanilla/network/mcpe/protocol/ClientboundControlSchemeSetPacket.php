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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\ControlScheme;

class ClientboundControlSchemeSetPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::CLIENTBOUND_CONTROL_SCHEME_SET_PACKET;

	private ControlScheme $scheme;

	/**
	 * @generate-create-func
	 */
	public static function create(ControlScheme $scheme) : self
	{
		$result = new self();
		$result->scheme = $scheme;
		return $result;
	}

	public function getScheme() : ControlScheme
	{
		return $this->scheme;
	}

	protected function decodePayload() : void
	{
		$this->scheme = ControlScheme::fromPacket($this->getByte());
	}

	protected function encodePayload() : void
	{
		$this->putByte($this->scheme->value);
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleClientboundControlSchemeSet($this);
	}
}
