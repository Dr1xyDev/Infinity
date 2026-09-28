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

class PartyDestinationCookieResponsePacket extends DataPacket{
	public const NETWORK_ID = ProtocolInfo::PARTY_DESTINATION_COOKIE_RESPONSE_PACKET;

	private string $cookie;
	private bool $accepted;

	/**
	 * @generate-create-func
	 */
	public static function create(string $cookie, bool $accepted) : self{
		$result = new self();
		$result->cookie = $cookie;
		$result->accepted = $accepted;
		return $result;
	}

	public function getCookie() : string{ return $this->cookie; }

	public function isAccepted() : bool{ return $this->accepted; }

	protected function decodePayload() : void{
		$this->cookie = $this->getString();
		$this->accepted = $this->getBool();
	}

	protected function encodePayload() : void{
		$this->putString($this->cookie);
		$this->putBool($this->accepted);
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handlePartyDestinationCookieResponse($this);
	}
}
