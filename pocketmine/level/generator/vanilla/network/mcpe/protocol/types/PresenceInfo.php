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

/**
 * @see ServerPresenceInfoPacket&ServerJoinInformation
 */
final class PresenceInfo{
	public function __construct(
		private ?string $experienceName,
		private ?string $worldName,
		private string $richPresenceId
	){}

	public function getExperienceName() : ?string{ return $this->experienceName; }

	public function getWorldName() : ?string{ return $this->worldName; }

	public function getRichPresenceId() : string{ return $this->richPresenceId; }

	public static function read(NetworkBinaryStream $in) : self{
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_2168) {
			//since 1.26.40 only the rich presence ID remains, as an optional
			return new self(null, null, $in->getOptional($in->getString(...)) ?? "");
		}
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_1001) {
			$experienceName = $in->getOptional($in->getString(...));
			$worldName = $in->getOptional($in->getString(...));
			$richPresenceId = $in->getString();
		} else {
			$experienceName = $in->getString();
			$worldName = $in->getString();
		}

		return new self($experienceName, $worldName, $richPresenceId ?? "");
	}

	public function write(NetworkBinaryStream $out) : void{
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_2168) {
			$out->putOptional($this->richPresenceId !== "" ? $this->richPresenceId : null, $out->putString(...));
			return;
		}
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_1001) {
			$out->putOptional($this->experienceName, $out->putString(...));
			$out->putOptional($this->worldName, $out->putString(...));
			$out->putString($this->richPresenceId);
		} else {
			$out->putString($this->experienceName ?? "");
			$out->putString($this->worldName ?? "");
		}
	}
}
