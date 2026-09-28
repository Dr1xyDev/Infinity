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

class BlockPickRequestPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::BLOCK_PICK_REQUEST_PACKET;

	/** @var int */
	public $blockX;
	/** @var int */
	public $blockY;
	/** @var int */
	public $blockZ;
	/** @var bool */
	public $addUserData = false;
	/** @var int */
	public $hotbarSlot;

	protected function decodePayload() : void
	{
		$this->getSignedBlockPosition($this->blockX, $this->blockY, $this->blockZ);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->addUserData = $this->getBool();
		}
		$this->hotbarSlot = $this->getByte();
	}

	protected function encodePayload() : void
	{
		$this->putSignedBlockPosition($this->blockX, $this->blockY, $this->blockZ);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->putBool($this->addUserData);
		}
		$this->putByte($this->hotbarSlot);
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleBlockPickRequest($this);
	}
}
