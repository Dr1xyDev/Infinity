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

class NetworkStackLatencyPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::NETWORK_STACK_LATENCY_PACKET;

	public int $timestamp;
	public bool $needResponse;

	protected function decodePayload() : void
	{
		$this->timestamp = $this->getLLong();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->needResponse = $this->getBool();
		}
	}

	protected function encodePayload() : void
	{
		$this->putLLong($this->timestamp);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->putBool($this->needResponse);
		}
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleNetworkStackLatency($this);
	}
}
