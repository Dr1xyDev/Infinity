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

class LabTablePacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::LAB_TABLE_PACKET;

	/** @var int */
	public $uselessByte; //0 for client -> server, 1 for server -> client. Seems useless.

	/** @var int */
	public $x;
	/** @var int */
	public $y;
	/** @var int */
	public $z;

	/** @var int */
	public $reactionType;

	protected function decodePayload() : void
	{
		$this->uselessByte = $this->getByte();
		$this->getSignedBlockPosition($this->x, $this->y, $this->z);
		$this->reactionType = $this->getByte();
	}

	protected function encodePayload() : void
	{
		$this->putByte($this->uselessByte);
		$this->putSignedBlockPosition($this->x, $this->y, $this->z);
		$this->putByte($this->reactionType);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleLabTable($this);
	}
}
