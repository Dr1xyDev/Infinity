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

class TickSyncPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::TICK_SYNC_PACKET;

	public int $clientSendTime;
	public int $serverReceiveTime;

	public static function request(int $clientTime) : self
	{
		$result = new self();
		$result->clientSendTime = $clientTime;
		$result->serverReceiveTime = 0; //useless
		return $result;
	}

	public static function response(int $clientSendTime, int $serverReceiveTime) : self
	{
		$result = new self();
		$result->clientSendTime = $clientSendTime;
		$result->serverReceiveTime = $serverReceiveTime;
		return $result;
	}

	public function getClientSendTime() : int
	{
		return $this->clientSendTime;
	}

	public function getServerReceiveTime() : int
	{
		return $this->serverReceiveTime;
	}

	protected function decodePayload() : void
	{
		$this->clientSendTime = $this->getLLong();
		$this->serverReceiveTime = $this->getLLong();
	}

	protected function encodePayload() : void
	{
		$this->putLLong($this->clientSendTime);
		$this->putLLong($this->serverReceiveTime);
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleTickSync($this);
	}
}
