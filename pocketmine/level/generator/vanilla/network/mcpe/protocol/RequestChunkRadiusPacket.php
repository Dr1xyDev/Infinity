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

use function ord;

class RequestChunkRadiusPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::REQUEST_CHUNK_RADIUS_PACKET;

	/** @var int */
	public $radius;
	/** @var int */
	public $maxRadius;

	protected function decodePayload() : void
	{
		$this->radius = $this->getVarInt();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_582) {
			$this->maxRadius = ord($this->get(1));
		}
	}

	protected function encodePayload() : void
	{
		$this->putVarInt($this->radius);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_582) {
			$this->putByte($this->maxRadius);
		}
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleRequestChunkRadius($this);
	}
}
