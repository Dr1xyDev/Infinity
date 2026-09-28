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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\GameMode;

class UpdatePlayerGameTypePacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::UPDATE_PLAYER_GAME_TYPE_PACKET;

	/** @see GameMode */
	public int $gameMode;
	public int $playerEntityUniqueId;
	public int $tick;

	public static function create(int $gameMode, int $playerEntityUniqueId, int $tick) : self
	{
		$result = new self();
		$result->gameMode = $gameMode;
		$result->playerEntityUniqueId = $playerEntityUniqueId;
		$result->tick = $tick;
		return $result;
	}

	public function getGameMode() : int
	{
		return $this->gameMode;
	}

	public function getPlayerEntityUniqueId() : int
	{
		return $this->playerEntityUniqueId;
	}

	public function getTick() : int
	{
		return $this->tick;
	}

	protected function decodePayload() : void
	{
		$this->gameMode = $this->getVarInt();
		$this->playerEntityUniqueId = $this->getEntityUniqueId();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_748) {
			$this->tick = $this->getUnsignedVarLong();
		} else {
			$this->tick = $this->getUnsignedVarInt();
		}
	}

	protected function encodePayload() : void
	{
		$this->putVarInt($this->gameMode);
		$this->putEntityUniqueId($this->playerEntityUniqueId);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_748) {
			$this->putUnsignedVarLong($this->tick);
		} else {
			$this->putUnsignedVarInt($this->tick);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleUpdatePlayerGameType($this);
	}
}
