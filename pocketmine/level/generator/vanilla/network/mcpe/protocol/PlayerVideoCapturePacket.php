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

class PlayerVideoCapturePacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::PLAYER_VIDEO_CAPTURE_PACKET;

	private bool $recording;
	private ?int $frameRate;
	private ?string $filePrefix;

	/**
	 * @generate-create-func
	 */
	private static function create(bool $recording, ?int $frameRate, ?string $filePrefix) : self
	{
		$result = new self();
		$result->recording = $recording;
		$result->frameRate = $frameRate;
		$result->filePrefix = $filePrefix;
		return $result;
	}

	public static function createStartRecording(int $frameRate, string $filePrefix) : self
	{
		return self::create(true, $frameRate, $filePrefix);
	}

	public static function createStopRecording() : self
	{
		return self::create(false, null, null);
	}

	public function isRecording() : bool
	{
		return $this->recording;
	}

	public function getFrameRate() : ?int
	{
		return $this->frameRate;
	}

	public function getFilePrefix() : ?string
	{
		return $this->filePrefix;
	}

	protected function decodePayload() : void
	{
		$this->recording = $this->getBool();
		if ($this->recording) {
			$this->frameRate = $this->getLInt();
			$this->filePrefix = $this->getString();
		}
	}

	protected function encodePayload() : void
	{
		$this->putBool($this->recording);
		if ($this->recording) {
			if ($this->frameRate === null) { // this should never be the case
				throw new \LogicException("PlayerUpdateEntityOverridesPacket with recording=true require a frame rate to be provided");
			}

			if ($this->filePrefix === null) { // this should never be the case
				throw new \LogicException("PlayerUpdateEntityOverridesPacket with recording=true require a file prefix to be provided");
			}

			$this->putLInt($this->frameRate);
			$this->putString($this->filePrefix);
		}
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handlePlayerVideoCapturePacket($this);
	}
}
