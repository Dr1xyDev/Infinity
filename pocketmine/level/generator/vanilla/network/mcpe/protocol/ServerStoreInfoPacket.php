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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\ClientStoreEntrypointConfig;

class ServerStoreInfoPacket extends DataPacket {
	public const NETWORK_ID = ProtocolInfo::SERVER_STORE_INFO_PACKET;

	private ?ClientStoreEntrypointConfig $clientStoreEntrypointConfig;

	/**
	 * @generate-create-func
	 */
	public static function create(?ClientStoreEntrypointConfig $clientStoreEntrypointConfig) : self{
		$result = new self();
		$result->clientStoreEntrypointConfig = $clientStoreEntrypointConfig;
		return $result;
	}

	public function getClientStoreEntrypointConfig() : ?ClientStoreEntrypointConfig{ return $this->clientStoreEntrypointConfig; }

	public function decodePayload() : void{
		$this->clientStoreEntrypointConfig = $this->getOptional(fn() => ClientStoreEntrypointConfig::read($this));
	}

	public function encodePayload() : void{
		$this->putOptional($this->clientStoreEntrypointConfig, fn(ClientStoreEntrypointConfig $v) => $v->write($this));
	}

	public function mustBeDecoded() : bool{
		return false;
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleServerStoreInfo($this);
	}
}
