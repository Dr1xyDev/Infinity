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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\shape;

use pocketmine\level\generator\vanilla\math\Vector3;
use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\GetTypeIdFromConstTrait;

final class PrimitiveShapeEllipsoidPayload extends PrimitiveShapePayload{
	use GetTypeIdFromConstTrait;

	public const ID = PrimitiveShapeType::PAYLOAD_TYPE_ELLIPSOID;

	public function __construct(
		private Vector3 $radii,
		private int $segmentsPerAxis,
	){}

	public function getRadii() : Vector3{ return $this->radii; }

	public function getSegmentsPerAxis() : int{ return $this->segmentsPerAxis; }

	public static function read(NetworkBinaryStream $in) : self{
		return new self(
			$in->getVector3(),
			$in->getByte()
		);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putVector3($this->radii);
		$out->putByte($this->segmentsPerAxis);
	}
}
