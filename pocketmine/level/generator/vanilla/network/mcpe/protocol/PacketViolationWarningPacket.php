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

class PacketViolationWarningPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::PACKET_VIOLATION_WARNING_PACKET;

	public const int TYPE_MALFORMED = 0;

	public const int SEVERITY_WARNING = 0;
	public const int SEVERITY_FINAL_WARNING = 1;
	public const int SEVERITY_TERMINATING_CONNECTION = 2;

	public int $type;
	public int $severity;
	public int $packetId;
	public string $message;

	public static function create(int $type, int $severity, int $packetId, string $message) : self
	{
		$result = new self();

		$result->type = $type;
		$result->severity = $severity;
		$result->packetId = $packetId;
		$result->message = $message;

		return $result;
	}

	public function getType() : int
	{
		return $this->type;
	}

	public function getSeverity() : int
	{
		return $this->severity;
	}

	public function getPacketId() : int
	{
		return $this->packetId;
	}

	public function getMessage() : string
	{
		return $this->message;
	}

	protected function decodePayload() : void
	{
		$this->type = $this->getVarInt();
		$this->severity = $this->getVarInt();
		$this->packetId = $this->getVarInt();
		$this->message = $this->getString();
	}

	protected function encodePayload() : void
	{
		$this->putVarInt($this->type);
		$this->putVarInt($this->severity);
		$this->putVarInt($this->packetId);
		$this->putString($this->message);
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handlePacketViolationWarning($this);
	}
}
