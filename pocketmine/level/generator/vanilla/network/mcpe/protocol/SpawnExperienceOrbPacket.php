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

class SpawnExperienceOrbPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::SPAWN_EXPERIENCE_ORB_PACKET;

	/** @var Vector3 */
	public $position;
	/** @var int */
	public $amount;

	protected function decodePayload() : void
	{
		$this->position = $this->getVector3();
		$this->amount = $this->getVarInt();
	}

	protected function encodePayload() : void
	{
		$this->putVector3($this->position);
		$this->putVarInt($this->amount);
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleSpawnExperienceOrb($this);
	}
}
