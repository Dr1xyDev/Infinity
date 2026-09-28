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

class LecternUpdatePacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::LECTERN_UPDATE_PACKET;

	/** @var int */
	public $page;
	/** @var int */
	public $totalPages;
	/** @var int */
	public $x;
	/** @var int */
	public $y;
	/** @var int */
	public $z;
	/** @var bool */
	public $dropBook = false;

	protected function decodePayload() : void
	{
		$this->page = $this->getByte();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->totalPages = $this->getByte();
		}
		$this->getBlockPosition($this->x, $this->y, $this->z);
		if ($this->protocol < ProtocolInfo::PROTOCOL_662) {
			$this->dropBook = $this->getBool();
		}
	}

	protected function encodePayload() : void
	{
		$this->putByte($this->page);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->putByte($this->totalPages);
		}
		$this->putBlockPosition($this->x, $this->y, $this->z);
		if ($this->protocol < ProtocolInfo::PROTOCOL_662) {
			$this->putBool($this->dropBook);
		}
	}

	public function mustBeDecoded() : bool
	{
		return true;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleLecternUpdate($this);
	}
}
