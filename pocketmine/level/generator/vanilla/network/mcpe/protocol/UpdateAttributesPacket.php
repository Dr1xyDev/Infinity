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

use pocketmine\level\generator\vanilla\entity\Attribute;
use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;

class UpdateAttributesPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::UPDATE_ATTRIBUTES_PACKET;

	/** @var int */
	public $entityRuntimeId;
	/** @var Attribute[] */
	public $entries = [];
	/** @var int */
	public $tick = 0;

	protected function decodePayload() : void
	{
		$this->entityRuntimeId = $this->getEntityRuntimeId();
		$this->entries = $this->getAttributeList();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_419) {
			$this->tick = $this->getUnsignedVarLong();
		}
	}

	protected function encodePayload() : void
	{
		$this->putEntityRuntimeId($this->entityRuntimeId);
		$this->putAttributeList(...$this->entries);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_419) {
			$this->putUnsignedVarLong($this->tick);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleUpdateAttributes($this);
	}
}
