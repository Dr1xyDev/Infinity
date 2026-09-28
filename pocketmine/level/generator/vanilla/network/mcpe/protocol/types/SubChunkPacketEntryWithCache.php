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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PacketDecodeException;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;

final class SubChunkPacketEntryWithCache
{
	public function __construct(
		private SubChunkPacketEntryCommon $base,
		private int $usedBlobHash
	) {
	}

	public function getBase() : SubChunkPacketEntryCommon
	{
		return $this->base;
	}

	public function getUsedBlobHash() : int
	{
		return $this->usedBlobHash;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$base = SubChunkPacketEntryCommon::read($in, true);
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_2168 && !$in->getBool()) {
			throw new PacketDecodeException("Missing blob hash for a subchunk entry with the cache enabled");
		}
		$usedBlobHash = $in->getLLong();

		return new self($base, $usedBlobHash);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$this->base->write($out, true);
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_2168) {
			$out->putBool(true); //optional since 1.26.40
		}
		$out->putLLong($this->usedBlobHash);
	}
}
