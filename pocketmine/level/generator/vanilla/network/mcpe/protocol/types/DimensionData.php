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
use pocketmine\level\generator\vanilla\utils\UUID;

final class DimensionData
{
	private UUID $packId;

	public function __construct(
		private int $maxHeight,
		private int $minHeight,
		private int $generator,
		private int $dimensionType,
		?UUID $packId = null, //since 1.26.40
		private string $defaultBiome = "" //since 1.26.50
	) {
		$this->packId = $packId ?? new UUID();
	}

	public function getMaxHeight() : int{ return $this->maxHeight; }

	public function getMinHeight() : int{ return $this->minHeight; }

	public function getGenerator() : int{ return $this->generator; }

	public function getDimensionType() : int{ return $this->dimensionType; }

	public function getPackId() : UUID{ return $this->packId; }

	public function getDefaultBiome() : string{ return $this->defaultBiome; }

	public static function read(NetworkBinaryStream $in) : self
	{
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_2193) {
			//since 1.26.50 the bounds are sent as minimum Y + height range
			$minHeight = $in->getVarInt();
			$maxHeight = $minHeight + $in->getVarInt();
		} else {
			$maxHeight = $in->getVarInt();
			$minHeight = $in->getVarInt();
		}
		$generator = $in->getVarInt();
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_975) {
			$dimensionType = $in->getVarInt();
		}
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_2168) {
			$packId = $in->getUUID();
		}
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_2193) {
			$defaultBiome = $in->getString();
		}

		return new self($maxHeight, $minHeight, $generator, $dimensionType ?? 0, $packId ?? null, $defaultBiome ?? "");
	}

	public function write(NetworkBinaryStream $out) : void
	{
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_2193) {
			$out->putVarInt($this->minHeight);
			$out->putVarInt($this->maxHeight - $this->minHeight);
		} else {
			$out->putVarInt($this->maxHeight);
			$out->putVarInt($this->minHeight);
		}
		$out->putVarInt($this->generator);
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_975) {
			$out->putVarInt($this->dimensionType);
		}
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_2168) {
			$out->putUUID($this->packId);
		}
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_2193) {
			$out->putString($this->defaultBiome);
		}
	}
}
