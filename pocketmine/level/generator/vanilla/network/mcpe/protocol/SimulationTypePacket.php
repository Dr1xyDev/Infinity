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

#include <rules/DataPacket.h>

use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;

class SimulationTypePacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::SIMULATION_TYPE_PACKET;

	public const int GAME = 0;
	public const int EDITOR = 1;
	public const int TEST = 2;

	private int $type;

	public static function create(int $type) : self
	{
		$result = new self();
		$result->type = $type;
		return $result;
	}

	public function getType() : int
	{
		return $this->type;
	}

	protected function decodePayload() : void
	{
		$this->type = $this->getByte();
	}

	protected function encodePayload() : void
	{
		$this->putByte($this->type);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleSimulationType($this);
	}
}
