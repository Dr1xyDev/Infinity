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

class FilterTextPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::FILTER_TEXT_PACKET;

	public string $text;
	public bool $fromServer;

	public static function create(string $text, bool $server) : self
	{
		$result = new self();
		$result->text = $text;
		$result->fromServer = $server;
		return $result;
	}

	public function getText() : string
	{
		return $this->text;
	}

	public function isFromServer() : bool
	{
		return $this->fromServer;
	}

	protected function decodePayload() : void
	{
		$this->text = $this->getString();
		$this->fromServer = $this->getBool();
	}

	protected function encodePayload() : void
	{
		$this->putString($this->text);
		$this->putBool($this->fromServer);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleFilterText($this);
	}
}
