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

class ClientCacheStatusPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::CLIENT_CACHE_STATUS_PACKET;

	public bool $enabled;

	public static function create(bool $enabled) : self
	{
		$result = new self();
		$result->enabled = $enabled;
		return $result;
	}

	public function isEnabled() : bool
	{
		return $this->enabled;
	}

	protected function decodePayload() : void
	{
		$this->enabled = $this->getBool();
	}

	protected function encodePayload() : void
	{
		$this->putBool($this->enabled);
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleClientCacheStatus($this);
	}
}
