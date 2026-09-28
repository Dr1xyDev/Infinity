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

class ServerboundDataDrivenScreenClosedPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::SERVERBOUND_DATA_DRIVEN_SCREEN_CLOSED_PACKET;

	/** Close reasons are sent as strings on the wire */
	public const CLOSE_REASON_PROGRAMMATIC_CLOSE = "programmaticclose";
	public const CLOSE_REASON_PROGRAMMATIC_CLOSE_ALL = "programmaticcloseall";
	public const CLOSE_REASON_CLIENT_CANCELED = "clientcanceled";
	public const CLOSE_REASON_USER_BUSY = "userbusy";
	public const CLOSE_REASON_INVALID_FORM = "invalidform";

	public int $formId = 0;
	public string $closeReason = self::CLOSE_REASON_PROGRAMMATIC_CLOSE;

	/**
	 * @generate-create-func
	 */
	public static function create(int $formId, string $closeReason) : self{
		$result = new self();
		$result->formId = $formId;
		$result->closeReason = $closeReason;
		return $result;
	}

	protected function decodePayload() : void{
		$this->formId = $this->getLInt();
		$this->closeReason = $this->getString();
	}

	protected function encodePayload() : void{
		$this->putLInt($this->formId);
		$this->putString($this->closeReason);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleServerboundDataDrivenScreenClosed($this);
	}
}
