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

class SendPartyDestinationCookiePacket extends DataPacket{
	public const NETWORK_ID = ProtocolInfo::SEND_PARTY_DESTINATION_COOKIE_PACKET;

	private string $cookie;
	private string $intent;
	private string $destinationName;

	/**
	 * @generate-create-func
	 */
	public static function create(string $cookie, string $intent, string $destinationName) : self{
		$result = new self();
		$result->cookie = $cookie;
		$result->intent = $intent;
		$result->destinationName = $destinationName;
		return $result;
	}

	public function getCookie() : string{ return $this->cookie; }

	public function getIntent() : string{ return $this->intent; }

	public function getDestinationName() : string{ return $this->destinationName; }

	protected function decodePayload() : void{
		$this->cookie = $this->getString();
		$this->intent = $this->getString();
		$this->destinationName = $this->getString();
	}

	protected function encodePayload() : void{
		$this->putString($this->cookie);
		$this->putString($this->intent);
		$this->putString($this->destinationName);
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleSendPartyDestinationCookie($this);
	}
}
