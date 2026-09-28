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

class StopSoundPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::STOP_SOUND_PACKET;

	public string $soundName;
	public bool $stopAll;
	public bool $stopMusicLegacy = true;

	protected function decodePayload() : void
	{
		$this->soundName = $this->getString();
		$this->stopAll = $this->getBool();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_712) {
			$this->stopMusicLegacy = $this->getBool();
		}
	}

	protected function encodePayload() : void
	{
		$this->putString($this->soundName);
		$this->putBool($this->stopAll);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_712) {
			$this->putBool($this->stopMusicLegacy);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleStopSound($this);
	}
}
