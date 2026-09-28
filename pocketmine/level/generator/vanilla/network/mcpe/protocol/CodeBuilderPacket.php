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

class CodeBuilderPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::CODE_BUILDER_PACKET;

	public string $url;
	public bool $openCodeBuilder;

	public static function create(string $url, bool $openCodeBuilder) : self
	{
		$result = new self();
		$result->url = $url;
		$result->openCodeBuilder = $openCodeBuilder;
		return $result;
	}

	public function getUrl() : string
	{
		return $this->url;
	}

	public function openCodeBuilder() : bool
	{
		return $this->openCodeBuilder;
	}

	protected function decodePayload() : void
	{
		$this->url = $this->getString();
		$this->openCodeBuilder = $this->getBool();
	}

	protected function encodePayload() : void
	{
		$this->putString($this->url);
		$this->putBool($this->openCodeBuilder);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleCodeBuilder($this);
	}
}
