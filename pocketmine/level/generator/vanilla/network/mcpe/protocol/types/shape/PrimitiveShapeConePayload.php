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

use pocketmine\level\generator\vanilla\math\Vector2;
use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\GetTypeIdFromConstTrait;

final class PrimitiveShapeConePayload extends PrimitiveShapePayload{
	use GetTypeIdFromConstTrait;

	public const ID = PrimitiveShapeType::PAYLOAD_TYPE_CONE;

	public function __construct(
		private Vector2 $radii,
		private float $height,
		private int $segments,
	){}

	public function getRadii() : Vector2{ return $this->radii; }

	public function getHeight() : float{ return $this->height; }

	public function getSegments() : int{ return $this->segments; }

	public static function read(NetworkBinaryStream $in) : self{
		return new self(
			$in->getVector2(),
			$in->getLFloat(),
			$in->getByte()
		);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putVector2($this->radii);
		$out->putLFloat($this->height);
		$out->putByte($this->segments);
	}
}
