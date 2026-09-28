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

class ShowCreditsPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::SHOW_CREDITS_PACKET;

	public const STATUS_START_CREDITS = 0;
	public const STATUS_END_CREDITS = 1;

	/** @var int */
	public $playerEid;
	/** @var int */
	public $status;

	protected function decodePayload() : void
	{
		$this->playerEid = $this->getEntityRuntimeId();
		$this->status = $this->getVarInt();
	}

	protected function encodePayload() : void
	{
		$this->putEntityRuntimeId($this->playerEid);
		$this->putVarInt($this->status);
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleShowCredits($this);
	}
}
