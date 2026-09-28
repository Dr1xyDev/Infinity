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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\PlayerPermissions;

/**
 * Sent by the client to request that the server change permissions of a player. This could be itself or another player.
 * Used when toggling permission switches or changing a player's permission level in the pause menu.
 */
class RequestPermissionsPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::REQUEST_PERMISSIONS_PACKET;

	public const FLAG_BUILD = 1 << 0;
	public const FLAG_MINE = 1 << 1;
	public const FLAG_DOORS_AND_SWITCHES = 1 << 2;
	public const FLAG_OPEN_CONTAINERS = 1 << 3;
	public const FLAG_ATTACK_PLAYERS = 1 << 4;
	public const FLAG_ATTACK_MOBS = 1 << 5;
	public const FLAG_OPERATOR = 1 << 6;
	public const FLAG_TELEPORT = 1 << 7;

	private int $targetActorUniqueId;
	/** @see PlayerPermissions */
	private int $playerPermission;
	private int $customFlags;

	/**
	 * @generate-create-func
	 */
	public static function create(int $targetActorUniqueId, int $playerPermission, int $customFlags) : self
	{
		$result = new self();
		$result->targetActorUniqueId = $targetActorUniqueId;
		$result->playerPermission = $playerPermission;
		$result->customFlags = $customFlags;
		return $result;
	}

	public function getTargetActorUniqueId() : int
	{
		return $this->targetActorUniqueId;
	}

	/** @see PlayerPermissions */
	public function getPlayerPermission() : int
	{
		return $this->playerPermission;
	}

	public function getCustomFlags() : int
	{
		return $this->customFlags;
	}

	protected function decodePayload() : void
	{
		$this->targetActorUniqueId = $this->getLLong();
		$this->playerPermission = $this->getVarInt();
		$this->customFlags = $this->getLShort();
	}

	protected function encodePayload() : void
	{
		$this->putLLong($this->targetActorUniqueId);
		$this->putVarInt($this->playerPermission);
		$this->putLShort($this->customFlags);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleRequestPermissions($this);
	}
}
