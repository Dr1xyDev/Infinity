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

use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;
use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\DebugMarkerData;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\DebugRendererType;

class ClientboundDebugRendererPacket extends DataPacket {
	public const NETWORK_ID = ProtocolInfo::CLIENTBOUND_DEBUG_RENDERER_PACKET;

	private DebugRendererType $type;
	private ?DebugMarkerData $data = null;

	protected function decodePayload() : void{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_897) {
			$this->type = DebugRendererType::fromName($this->getString());
			$this->data = $this->getOptional(fn() => DebugMarkerData::read($this));
		} else {
			$this->type = DebugRendererType::fromPacket($this->getLInt());
			$this->data = DebugMarkerData::read($this);
		}
	}

	protected function encodePayload() : void{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_897) {
			$this->putString($this->type->getName());
			$this->putOptional($this->data, fn(DebugMarkerData $data) => $data->write($this));
		} else {
			$this->putLInt($this->type->value);
			$this->data->write($this);
		}
	}

	public function mustBeDecoded() : bool{
		return false;
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleClientboundDebugRenderer($this);
	}
}
