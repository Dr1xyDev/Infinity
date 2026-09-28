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

class HurtArmorPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::HURT_ARMOR_PACKET;

	/** @var int */
	public $cause;
	/** @var int */
	public $health;
	/** @var int */
	public $armorSlotFlags;

	protected function decodePayload() : void
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->cause = $this->getVarInt();
		}
		$this->health = $this->getVarInt();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_465) {
			$this->armorSlotFlags = $this->getUnsignedVarLong();
		}
	}

	protected function encodePayload() : void
	{
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->putVarInt($this->cause);
		}
		$this->putVarInt($this->health);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_465) {
			$this->putUnsignedVarLong($this->armorSlotFlags);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleHurtArmor($this);
	}
}
