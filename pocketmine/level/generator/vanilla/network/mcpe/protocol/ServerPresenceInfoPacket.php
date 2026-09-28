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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\PresenceInfo;

/**
 * Relays server performance statistics to the client.
 * It's currently unclear what the purpose of this packet is - probably to power some fancy debug screen.
 */
class ServerPresenceInfoPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::SERVER_PRESENCE_INFO_PACKET;

	private ?PresenceInfo $presenceConfig;

	/**
	 * @generate-create-func
	 */
	public static function create(?PresenceInfo $presenceConfig) : self{
		$result = new self();
		$result->presenceConfig = $presenceConfig;
		return $result;
	}

	public function getPresenceConfig() : ?PresenceInfo{ return $this->presenceConfig; }

	protected function decodePayload() : void
	{
		$this->presenceConfig = $this->getOptional(fn() => PresenceInfo::read($this));
	}

	protected function encodePayload() : void
	{
		$this->putOptional($this->presenceConfig, fn(PresenceInfo $v) => $v->write($this));
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleServerPresenceInfo($this);
	}
}
