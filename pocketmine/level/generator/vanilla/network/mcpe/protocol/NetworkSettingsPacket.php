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

#include <rules/DataPacket.h>

use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;

class NetworkSettingsPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::NETWORK_SETTINGS_PACKET;

	public const COMPRESS_NOTHING = 0;
	public const COMPRESS_EVERYTHING = 1;

	public int $compressionThreshold;
	public int $compressionAlgorithm;
	public bool $enableClientThrottling;
	public int $clientThrottleThreshold;
	public float $clientThrottleScalar;

	public function canBeSentBeforeLogin() : bool
	{
		return true;
	}

	protected function decodePayload() : void
	{
		$this->compressionThreshold = $this->getLShort();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_554) {
			$this->compressionAlgorithm = $this->getLShort();
			$this->enableClientThrottling = $this->getBool();
			$this->clientThrottleThreshold = $this->getByte();
			$this->clientThrottleScalar = $this->getLFloat();
		}
	}

	protected function encodePayload() : void
	{
		$this->putLShort($this->compressionThreshold);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_554) {
			$this->putLShort($this->compressionAlgorithm);
			$this->putBool($this->enableClientThrottling);
			$this->putByte($this->clientThrottleThreshold);
			$this->putLFloat($this->clientThrottleScalar);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleNetworkSettings($this);
	}
}
