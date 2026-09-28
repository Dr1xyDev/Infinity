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

class ClientboundDataDrivenUIShowScreenPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::CLIENTBOUND_DATA_DRIVEN_UI_SHOW_SCREEN_PACKET;

	public string $screenId;
	public int $formId;
	public ?int $dataInstanceId = null;

	protected function decodePayload() : void{
		$this->screenId = $this->getString();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_944) {
			$this->formId = $this->getLInt();
			$this->dataInstanceId = $this->getOptional(fn() => $this->getLInt());
		}
	}

	protected function encodePayload() : void{
		$this->putString($this->screenId);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_944) {
			$this->putLInt($this->formId);
			$this->putOptional($this->dataInstanceId, fn(int $dataInstanceId) => $this->putLInt($dataInstanceId));
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleClientboundDataDrivenUIShowScreen($this);
	}
}
