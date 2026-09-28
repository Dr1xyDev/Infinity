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

final class SubChunkPosition
{
	public function __construct(
		private int $x,
		private int $y,
		private int $z,
	) {
	}

	public function getX() : int
	{
		return $this->x;
	}

	public function getY() : int
	{
		return $this->y;
	}

	public function getZ() : int
	{
		return $this->z;
	}

	public static function readFixedInts(NetworkBinaryStream $in) : self{
		if($in->getProtocol() >= ProtocolInfo::PROTOCOL_1001){
			$x = $in->getLInt();
			$y = $in->getLInt();
			$z = $in->getLInt();
		}else{
			$x = $in->getVarInt();
			$y = $in->getVarInt();
			$z = $in->getVarInt();
		}

		return new self($x, $y, $z);
	}

	public static function readVarInts(NetworkBinaryStream $in) : self{
		$x = $in->getVarInt();
		$y = $in->getVarInt();
		$z = $in->getVarInt();

		return new self($x, $y, $z);
	}

	public function writeFixedInts(NetworkBinaryStream $out) : void{
		if($out->getProtocol() >= ProtocolInfo::PROTOCOL_1001){
			$out->putLInt($this->x);
			$out->putLInt($this->y);
			$out->putLInt($this->z);
		} else {
			$out->putVarInt($this->x);
			$out->putVarInt($this->y);
			$out->putVarInt($this->z);
		}
	}

	public function writeVarInts(NetworkBinaryStream $out) : void{
		$out->putVarInt($this->x);
		$out->putVarInt($this->y);
		$out->putVarInt($this->z);
	}
}
