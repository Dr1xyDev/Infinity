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

use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\GetTypeIdFromConstTrait;

final class PrimitiveShapePyramidPayload extends PrimitiveShapePayload{
	use GetTypeIdFromConstTrait;

	public const ID = PrimitiveShapeType::PAYLOAD_TYPE_PYRAMID;

	public function __construct(
		private float $width,
		private ?float $depth,
		private float $height,
	){}

	public function getWidth() : float{ return $this->width; }

	public function getDepth() : ?float{ return $this->depth; }

	public function getHeight() : float{ return $this->height; }

	public static function read(NetworkBinaryStream $in) : self{
		return new self(
			$in->getLFloat(),
			$in->getOptional($in->getLFloat(...)),
			$in->getLFloat()
		);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putLFloat($this->width);
		$out->putOptional($this->depth, $out->putLFloat(...));
		$out->putLFloat($this->height);
	}
}
