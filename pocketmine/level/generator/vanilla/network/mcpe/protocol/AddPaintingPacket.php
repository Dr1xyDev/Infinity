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

class AddPaintingPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::ADD_PAINTING_PACKET;

	public ?int $entityUniqueId = null;
	public int $entityRuntimeId;
	public float|int $x = 0;
	public float|int $y = 0;
	public float|int $z = 0;
	public int $direction;
	public string $title;

	/**
	 * @generate-create-func
	 */
	public static function create(
		int $entityUniqueId,
		int $entityRuntimeId,
		float|int $x,
		float|int $y,
		float|int $z,
		int $direction,
		string $title
	) : self
	{
		$result = new self();
		$result->entityUniqueId = $entityUniqueId;
		$result->entityRuntimeId = $entityRuntimeId;
		$result->x = $x;
		$result->y = $y;
		$result->z = $z;
		$result->direction = $direction;
		$result->title = $title;
		return $result;
	}

	protected function decodePayload() : void
	{
		$this->entityUniqueId = $this->getEntityUniqueId();
		$this->entityRuntimeId = $this->getEntityRuntimeId();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$position = $this->getVector3();
			$this->x = $position->x;
			$this->y = $position->y;
			$this->z = $position->z;
		} else {
			$this->getBlockPosition($this->x, $this->y, $this->z);
		}
		$this->direction = $this->getVarInt();
		$this->title = $this->getString();
	}

	protected function encodePayload() : void
	{
		$this->putEntityUniqueId($this->entityUniqueId ?? $this->entityRuntimeId);
		$this->putEntityRuntimeId($this->entityRuntimeId);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->putVector3(new Vector3($this->x, $this->y, $this->z));
		} else {
			$this->putBlockPosition($this->x, $this->y, $this->z);
		}
		$this->putVarInt($this->direction);
		$this->putString($this->title);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleAddPainting($this);
	}
}
