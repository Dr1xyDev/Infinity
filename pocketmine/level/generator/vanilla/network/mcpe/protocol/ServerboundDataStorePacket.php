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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\ddui\DataStoreUpdate;

class ServerboundDataStorePacket extends DataPacket{
	public const NETWORK_ID = ProtocolInfo::SERVERBOUND_DATA_STORE_PACKET;

	public DataStoreUpdate $update;

	/**
	 * @generate-create-func
	 */
	public static function create(DataStoreUpdate $update) : self{
		$result = new self();
		$result->update = $update;
		return $result;
	}

	protected function decodePayload() : void{
		$this->update = DataStoreUpdate::read($this);
	}

	protected function encodePayload() : void{
		$this->update->write($this);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleServerboundDataStore($this);
	}
}
