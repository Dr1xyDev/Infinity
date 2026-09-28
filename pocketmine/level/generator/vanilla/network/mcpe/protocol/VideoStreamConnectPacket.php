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

class VideoStreamConnectPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::VIDEO_STREAM_CONNECT_PACKET;

	public const ACTION_CONNECT = 0;
	public const ACTION_DISCONNECT = 1;

	/** @var string */
	public $serverUri;
	/** @var float */
	public $frameSendFrequency;
	/** @var int */
	public $action;
	/** @var int */
	public $resolutionX;
	/** @var int */
	public $resolutionY;

	protected function decodePayload() : void
	{
		$this->serverUri = $this->getString();
		$this->frameSendFrequency = $this->getLFloat();
		$this->action = $this->getByte();
		$this->resolutionX = $this->getLInt();
		$this->resolutionY = $this->getLInt();
	}

	protected function encodePayload() : void
	{
		$this->putString($this->serverUri);
		$this->putLFloat($this->frameSendFrequency);
		$this->putByte($this->action);
		$this->putLInt($this->resolutionX);
		$this->putLInt($this->resolutionY);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleVideoStreamConnect($this);
	}
}
