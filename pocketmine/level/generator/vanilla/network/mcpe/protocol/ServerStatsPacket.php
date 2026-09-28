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

/**
 * Relays server performance statistics to the client.
 * It's currently unclear what the purpose of this packet is - probably to power some fancy debug screen.
 */
class ServerStatsPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::SERVER_STATS_PACKET;

	private float $serverTime;
	private float $networkTime;

	/**
	 * @generate-create-func
	 */
	public static function create(float $serverTime, float $networkTime) : self
	{
		$result = new self();
		$result->serverTime = $serverTime;
		$result->networkTime = $networkTime;
		return $result;
	}

	public function getServerTime() : float
	{
		return $this->serverTime;
	}

	public function getNetworkTime() : float
	{
		return $this->networkTime;
	}

	protected function decodePayload() : void
	{
		$this->serverTime = $this->getLFloat();
		$this->networkTime = $this->getLFloat();
	}

	protected function encodePayload() : void
	{
		$this->putLFloat($this->serverTime);
		$this->putLFloat($this->networkTime);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleServerStats($this);
	}
}
