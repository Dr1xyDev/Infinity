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

use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;

class ServerPlayerPostMovePositionPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::SERVER_PLAYER_POST_MOVE_POSITION_PACKET;

	private Vector3 $position;

	/**
	 * @generate-create-func
	 */
	public static function create(Vector3 $position) : self
	{
		$result = new self();
		$result->position = $position;
		return $result;
	}

	public function getPosition() : Vector3
	{
		return $this->position;
	}

	protected function decodePayload() : void
	{
		$this->position = $this->getVector3();
	}

	protected function encodePayload() : void
	{
		$this->putVector3($this->position);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleServerPlayerPostMovePosition($this);
	}
}
