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

class SetSpawnPositionPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::SET_SPAWN_POSITION_PACKET;

	public const TYPE_PLAYER_SPAWN = 0;
	public const TYPE_WORLD_SPAWN = 1;

	/** @var int */
	public $spawnType;
	/** @var int */
	public $x;
	/** @var int */
	public $y;
	/** @var int */
	public $z;
	/** @var int */
	public $dimension;
	/** @var int */
	public $x2;
	/** @var int */
	public $y2;
	/** @var int */
	public $z2;
	/** @var bool */
	public $spawnForced;

	protected function decodePayload() : void
	{
		$this->spawnType = $this->getVarInt();
		$this->getBlockPosition($this->x, $this->y, $this->z);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->dimension = $this->getVarInt();
			$this->getBlockPosition($this->x2, $this->y2, $this->z2);
		} else {
			$this->spawnForced = $this->getBool();
		}
	}

	protected function encodePayload() : void
	{
		$this->putVarInt($this->spawnType);
		$this->putBlockPosition($this->x, $this->y, $this->z);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->putVarInt($this->dimension);
			$this->putBlockPosition($this->x2, $this->y2, $this->z2);
		} else {
			$this->putBool($this->spawnForced);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleSetSpawnPosition($this);
	}
}
