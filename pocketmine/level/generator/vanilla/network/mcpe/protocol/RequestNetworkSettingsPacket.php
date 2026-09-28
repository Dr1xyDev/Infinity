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

/**
 * This is the first packet sent in a game session. It contains the client's protocol version.
 * The server is expected to respond to this with network settings, which will instruct the client which compression
 * type to use, amongst other things.
 */
class RequestNetworkSettingsPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::REQUEST_NETWORK_SETTINGS_PACKET;

	private int $protocolVersion;

	public function canBeSentBeforeLogin() : bool
	{
		return true;
	}

	/**
	 * @generate-create-func
	 */
	public static function create(int $protocolVersion) : self
	{
		$result = new self();
		$result->protocolVersion = $protocolVersion;
		return $result;
	}

	public function getProtocolVersion() : int
	{
		return $this->protocolVersion;
	}

	protected function decodeHeader() : void
	{
		$this->getUnsignedVarInt();
	}

	protected function decodePayload() : void
	{
		$this->protocolVersion = $this->getInt();
	}

	protected function encodePayload() : void
	{
		$this->putInt($this->protocolVersion);
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleRequestNetworkSettings($this);
	}
}
