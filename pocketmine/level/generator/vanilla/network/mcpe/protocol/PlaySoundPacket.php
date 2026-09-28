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

class PlaySoundPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::PLAY_SOUND_PACKET;

	public string $soundName;
	public float $x = 0.0;
	public float $y = 0.0;
	public float $z = 0.0;
	public float $volume;
	public float $pitch;
	/** @since 1.26.40 */
	public int $loopCount = 0;
	/** @since 1.26.50 */
	public bool $bypassListenerRangeCheck = false;
	public ?int $serverSoundHandle = null;
	/** @since 1.26.50 */
	public ?float $playbackPositionSeconds = null;

	protected function decodePayload() : void
	{
		$this->soundName = $this->getString();
		$this->getBlockPosition($this->x, $this->y, $this->z);
		$this->x /= 8;
		$this->y /= 8;
		$this->z /= 8;
		$this->volume = $this->getLFloat();
		$this->pitch = $this->getLFloat();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_2168) {
			$this->loopCount = $this->getVarInt();
		}
		if ($this->protocol >= ProtocolInfo::PROTOCOL_2193) {
			$this->bypassListenerRangeCheck = $this->getBool();
		}
		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_975) {
			$this->serverSoundHandle = $this->getOptional($this->getLLong(...));
		}
		if ($this->protocol >= ProtocolInfo::PROTOCOL_2193) {
			$this->playbackPositionSeconds = $this->getOptional($this->getLFloat(...));
		}
	}

	protected function encodePayload() : void
	{
		$this->putString($this->soundName);
		$this->putBlockPosition((int) ($this->x * 8), (int) ($this->y * 8), (int) ($this->z * 8));
		$this->putLFloat($this->volume);
		$this->putLFloat($this->pitch);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_2168) {
			$this->putVarInt($this->loopCount);
		}
		if ($this->protocol >= ProtocolInfo::PROTOCOL_2193) {
			$this->putBool($this->bypassListenerRangeCheck);
		}
		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_975) {
			$this->putOptional($this->serverSoundHandle, $this->putLLong(...));
		}
		if ($this->protocol >= ProtocolInfo::PROTOCOL_2193) {
			$this->putOptional($this->playbackPositionSeconds, $this->putLFloat(...));
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handlePlaySound($this);
	}
}
