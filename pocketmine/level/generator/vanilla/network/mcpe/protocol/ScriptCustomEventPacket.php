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

class ScriptCustomEventPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::SCRIPT_CUSTOM_EVENT_PACKET;

	/** @var string */
	public $eventName;
	/** @var string json data */
	public $eventData;

	protected function decodePayload() : void
	{
		$this->eventName = $this->getString();
		$this->eventData = $this->getString();
	}

	protected function encodePayload() : void
	{
		$this->putString($this->eventName);
		$this->putString($this->eventData);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleScriptCustomEvent($this);
	}
}
