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

final class SubChunkPacketEntryWithoutCache
{
	public function __construct(
		private SubChunkPacketEntryCommon $base
	) {
	}

	public function getBase() : SubChunkPacketEntryCommon
	{
		return $this->base;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$entry = new self(SubChunkPacketEntryCommon::read($in, false));
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_2168) {
			$in->getOptional($in->getLLong(...)); //blob hash, meaningless without the cache
		}
		return $entry;
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$this->base->write($out, false);
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_2168) {
			$out->putBool(false); //no blob hash (optional since 1.26.40)
		}
	}
}
