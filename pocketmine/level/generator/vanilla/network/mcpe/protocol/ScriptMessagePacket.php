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

class ScriptMessagePacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::SCRIPT_MESSAGE_PACKET;

	private string $messageId;
	private string $value;

	/**
	 * @generate-create-func
	 */
	public static function create(string $messageId, string $value) : self
	{
		$result = new self();
		$result->messageId = $messageId;
		$result->value = $value;
		return $result;
	}

	public function getMessageId() : string
	{
		return $this->messageId;
	}

	public function getValue() : string
	{
		return $this->value;
	}

	protected function decodePayload() : void
	{
		$this->messageId = $this->getString();
		$this->value = $this->getString();
	}

	protected function encodePayload() : void
	{
		$this->putString($this->messageId);
		$this->putString($this->value);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleScriptMessage($this);
	}
}
