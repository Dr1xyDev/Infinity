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

final class ServerJoinInformation{

	public function __construct(
		private ?GatheringJoinInfo $gatheringJoinInfo,
		private ?StoreEntryPointInfo $storeEntryPointInfo = null,
		private ?PresenceInfo $presenceInfo = null
	){}

	public function getGatheringJoinInfo() : ?GatheringJoinInfo{ return $this->gatheringJoinInfo; }

	public function getStoreEntryPointInfo() : ?StoreEntryPointInfo{ return $this->storeEntryPointInfo; }

	public function getPresenceInfo() : ?PresenceInfo{ return $this->presenceInfo; }

	public static function read(NetworkBinaryStream $in) : self{
		$gatheringJoinInfo = $in->getOptional(fn () => GatheringJoinInfo::read($in));
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_975) {
			$storeEntryPointInfo = $in->getOptional(fn() => StoreEntryPointInfo::read($in));
			$presenceInfo = $in->getOptional(fn() => PresenceInfo::read($in));
		}

		return new self(
			$gatheringJoinInfo,
			$storeEntryPointInfo ?? null,
			$presenceInfo ?? null
		);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putOptional($this->gatheringJoinInfo, fn(GatheringJoinInfo $info) => $info->write($out));
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_975) {
			$out->putOptional($this->storeEntryPointInfo, fn(StoreEntryPointInfo $info) => $info->write($out));
			$out->putOptional($this->presenceInfo, fn(PresenceInfo $info) => $info->write($out));
		}
	}
}
