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

use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;

final class WorldPosition{

	public function __construct(
		public Vector3 $position,
		public int $dimensionId
	){}

	public static function read(NetworkBinaryStream $in) : self{
		return new self(
			$in->getVector3(),
			$in->getVarInt()
		);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putVector3($this->position);
		$out->putVarInt($this->dimensionId);
	}
}
