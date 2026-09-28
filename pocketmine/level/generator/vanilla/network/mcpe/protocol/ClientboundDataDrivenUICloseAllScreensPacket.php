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

class ClientboundDataDrivenUICloseAllScreensPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::CLIENTBOUND_DATA_DRIVEN_UI_CLOSE_ALL_SCREENS_PACKET;

	/**
	 * Since 1.26.10 this closes a single data-driven screen (null closes all of them)
	 */
	public ?int $formId = null;

	public static function create(?int $formId = null) : self{
		$result = new self();
		$result->formId = $formId;
		return $result;
	}

	protected function decodePayload() : void{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_944) {
			$this->formId = $this->getOptional($this->getLInt(...));
		}
	}

	protected function encodePayload() : void{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_944) {
			$this->putOptional($this->formId, $this->putLInt(...));
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleClientboundDataDrivenUICloseAllScreens($this);
	}
}
