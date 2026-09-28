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

class ChangeDimensionPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::CHANGE_DIMENSION_PACKET;

	public int $dimension;
	public Vector3 $position;
	public bool $respawn = false;
	private ?int $loadingScreenId = null;

	protected function decodePayload() : void
	{
		$this->dimension = $this->getVarInt();
		$this->position = $this->getVector3();
		$this->respawn = $this->getBool();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_712) {
			$this->loadingScreenId = $this->getOptional(fn () => $this->getLInt());
		}
	}

	protected function encodePayload() : void
	{
		$this->putVarInt($this->dimension);
		$this->putVector3($this->position);
		$this->putBool($this->respawn);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_712) {
			$this->putOptional($this->loadingScreenId, $this->putLInt(...));
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleChangeDimension($this);
	}
}
