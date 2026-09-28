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

#include <rules/DataPacket.h>

use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;

class ClientboundCloseFormPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::CLIENTBOUND_CLOSE_FORM_PACKET;

	/**
	 * @generate-create-func
	 */
	public static function create() : self
	{
		return new self();
	}

	protected function decodePayload() : void
	{
		//No payload
	}

	protected function encodePayload() : void
	{
		//No payload
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleClientboundCloseForm($this);
	}
}
