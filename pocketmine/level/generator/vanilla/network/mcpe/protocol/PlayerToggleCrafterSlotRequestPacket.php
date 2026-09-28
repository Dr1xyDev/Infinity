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

class PlayerToggleCrafterSlotRequestPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::PLAYER_TOGGLE_CRAFTER_SLOT_REQUEST_PACKET;

	private Vector3 $position;
	private int $slot;
	private bool $disabled;

	/**
	 * @generate-create-func
	 */
	public static function create(Vector3 $position, int $slot, bool $disabled) : self
	{
		$result = new self();
		$result->position = $position;
		$result->slot = $slot;
		$result->disabled = $disabled;
		return $result;
	}

	public function getPosition() : Vector3
	{
		return $this->position;
	}

	public function getSelectedSlot() : int
	{
		return $this->slot;
	}

	public function isDisabled() : bool
	{
		return $this->disabled;
	}

	protected function decodePayload() : void
	{
		$x = $this->getLInt();
		$y = $this->getLInt();
		$z = $this->getLInt();
		$this->position = new Vector3($x, $y, $z);
		$this->slot = $this->getByte();
		$this->disabled = $this->getBool();
	}

	protected function encodePayload() : void
	{
		$this->putLInt($this->position->getX());
		$this->putLInt($this->position->getY());
		$this->putLInt($this->position->getZ());
		$this->putByte($this->slot);
		$this->putBool($this->disabled);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handlePlayerToggleCrafterSlotRequest($this);
	}
}
