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

final class PrimitiveShapeArrowPayload extends PrimitiveShapePayload{
	use GetTypeIdFromConstTrait;

	public const ID = PrimitiveShapeType::PAYLOAD_TYPE_ARROW;

	public function __construct(
		private ?Vector3 $lineEndLocation,
		private ?float $arrowHeadLength,
		private ?float $arrowHeadRadius,
		private ?int $segments,
	){}

	public function getLineEndLocation() : ?Vector3{ return $this->lineEndLocation; }

	public function getArrowHeadLength() : ?float{ return $this->arrowHeadLength; }

	public function getArrowHeadRadius() : ?float{ return $this->arrowHeadRadius; }

	public function getSegments() : ?int{ return $this->segments; }

	public static function read(NetworkBinaryStream $in) : self{
		return new self(
			$in->getOptional($in->getVector3(...)),
			$in->getOptional($in->getLFloat(...)),
			$in->getOptional($in->getLFloat(...)),
			$in->getOptional($in->getByte(...)),
		);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putOptional($this->lineEndLocation, $out->putVector3(...));
		$out->putOptional($this->arrowHeadLength, $out->putLFloat(...));
		$out->putOptional($this->arrowHeadRadius, $out->putLFloat(...));
		$out->putOptional($this->segments, $out->putByte(...));
	}
}
