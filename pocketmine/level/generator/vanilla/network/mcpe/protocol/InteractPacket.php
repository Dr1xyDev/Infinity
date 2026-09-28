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

class InteractPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::INTERACT_PACKET;

	public const int ACTION_RIGHT_CLICK = 1;
	public const int ACTION_LEFT_CLICK = 2;
	public const int ACTION_LEAVE_VEHICLE = 3;
	public const int ACTION_MOUSEOVER = 4;
	public const int ACTION_OPEN_NPC = 5;
	public const int ACTION_OPEN_INVENTORY = 6;

	public int $action;
	public int $target;
	public ?Vector3 $position = null;

	protected function decodePayload() : void
	{
		$this->action = $this->getByte();
		$this->target = $this->getEntityRuntimeId();

		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			if ($this->protocol >= ProtocolInfo::PROTOCOL_897) {
				$this->position = $this->getOptional($this->getVector3(...));
			} else {
				if ($this->action === self::ACTION_MOUSEOVER || ($this->protocol >= ProtocolInfo::PROTOCOL_486 && $this->action === self::ACTION_LEAVE_VEHICLE)) {
					$this->position = $this->getVector3();
				}
			}
		}
	}

	protected function encodePayload() : void
	{
		$this->putByte($this->action);
		$this->putEntityRuntimeId($this->target);

		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			if ($this->protocol >= ProtocolInfo::PROTOCOL_897) {
				$this->putOptional($this->position, $this->putVector3(...));
			} else {
				if ($this->action === self::ACTION_MOUSEOVER || ($this->protocol >= ProtocolInfo::PROTOCOL_486 && $this->action === self::ACTION_LEAVE_VEHICLE)) {
					$this->putVector3($this->position);
				}
			}
		}
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleInteract($this);
	}
}
