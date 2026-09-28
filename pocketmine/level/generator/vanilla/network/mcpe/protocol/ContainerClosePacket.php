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

class ContainerClosePacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::CONTAINER_CLOSE_PACKET;

	/** @var int */
	public $windowId;
	/** @var int */
	public $windowType;
	/** @var bool */
	public $server = false;

	protected function decodePayload() : void
	{
		$this->windowId = $this->getByte();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_419) {
			if ($this->protocol >= ProtocolInfo::PROTOCOL_685) {
				$this->windowType = $this->getByte();
			}
			$this->server = $this->getBool();
		}
	}

	protected function encodePayload() : void
	{
		$this->putByte($this->windowId);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_419) {
			if ($this->protocol >= ProtocolInfo::PROTOCOL_685) {
				$this->putByte($this->windowType);
			}
			$this->putBool($this->server);
		}
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleContainerClose($this);
	}
}
