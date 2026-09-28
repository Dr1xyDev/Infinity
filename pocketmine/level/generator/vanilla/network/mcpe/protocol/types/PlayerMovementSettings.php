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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types;

use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;

final class PlayerMovementSettings
{
	public function __construct(
		private ServerAuthMovementMode $movementType,
		private int $rewindHistorySize,
		private bool $serverAuthoritativeBlockBreaking
	) {
	}

	public function getMovementType() : ServerAuthMovementMode
	{
		return $this->movementType;
	}

	public function getRewindHistorySize() : int
	{
		return $this->rewindHistorySize;
	}

	public function isServerAuthoritativeBlockBreaking() : bool
	{
		return $this->serverAuthoritativeBlockBreaking;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		if ($in->getProtocol() < ProtocolInfo::PROTOCOL_818) {
			if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_419) {
				$movementType = ServerAuthMovementMode::fromPacket($in->getVarInt());
			} else {
				$movementType = $in->getBool() ? ServerAuthMovementMode::SERVER_AUTHORITATIVE_V2 : ServerAuthMovementMode::LEGACY_CLIENT_AUTHORITATIVE_V1;
			}
		}

		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_428) {
			$rewindHistorySize = $in->getVarInt();
			$serverAuthBlockBreaking = $in->getBool();
		}
		return new self($movementType ?? ServerAuthMovementMode::SERVER_AUTHORITATIVE_V3, $rewindHistorySize ?? 0, $serverAuthBlockBreaking ?? false);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		if ($out->getProtocol() < ProtocolInfo::PROTOCOL_818) {
			if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_419) {
				$out->putVarInt($this->movementType->value);
			} else {
				$out->putBool($this->movementType !== ServerAuthMovementMode::LEGACY_CLIENT_AUTHORITATIVE_V1);
			}
		}

		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_428) {
			$out->putVarInt($this->rewindHistorySize);
			$out->putBool($this->serverAuthoritativeBlockBreaking);
		}
	}
}
