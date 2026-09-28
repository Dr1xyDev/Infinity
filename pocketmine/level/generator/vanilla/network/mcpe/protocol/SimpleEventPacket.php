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

class SimpleEventPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::SIMPLE_EVENT_PACKET;

	public const TYPE_ENABLE_COMMANDS = 1;
	public const TYPE_DISABLE_COMMANDS = 2;

	/** @var int */
	public $eventType;

	protected function decodePayload() : void
	{
		$this->eventType = $this->getLShort();
	}

	protected function encodePayload() : void
	{
		$this->putLShort($this->eventType);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleSimpleEvent($this);
	}
}
