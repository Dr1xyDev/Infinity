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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\ProtocolInfo;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\GetTypeIdFromConstTrait;
use pocketmine\level\generator\vanilla\utils\Color;

final class PrimitiveShapeTextPayload extends PrimitiveShapePayload{
	use GetTypeIdFromConstTrait;

	public const ID = PrimitiveShapeType::PAYLOAD_TYPE_TEXT;

	public function __construct(
		private string $text,
		private bool $useRotation,
		private ?Color $backgroundColor,
		private bool $depthTest,
		private bool $showBackface,
		private bool $showTextBackface,
		private float $lineGapHeight = 0.0 //since 1.26.50
	){}

	public function getText() : string{ return $this->text; }

	public function useRotation() : bool{ return $this->useRotation; }

	public function getBackgroundColor() : ?Color{ return $this->backgroundColor; }

	public function getLineGapHeight() : float{ return $this->lineGapHeight; }

	public function hasDepthTest() : bool{ return $this->depthTest; }

	public function hasShowBackface() : bool{ return $this->showBackface; }

	public function hasShowTextBackface() : bool{ return $this->showTextBackface; }

	public static function read(NetworkBinaryStream $in) : self{
		$text = $in->getString();
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_975) {
			$useRotation = $in->getBool();
			$backgroundColor = $in->getOptional(fn() => Color::fromARGB($in->getLInt()));
			if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_2193) {
				$lineGapHeight = $in->getLFloat();
			}
			$depthTest = $in->getBool();
			$showBackface = $in->getBool();
			$showTextBackface = $in->getBool();
		}

		return new self(
			$text,
			$useRotation ?? false,
			$backgroundColor ?? null,
			$depthTest ?? false,
			$showBackface ?? false,
			$showTextBackface ?? false,
			$lineGapHeight ?? 0.0
		);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putString($this->text);
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_975) {
			$out->putBool($this->useRotation);
			$out->putOptional($this->backgroundColor, fn(Color $color) => $out->putLInt($color->toARGB()));
			if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_2193) {
				$out->putLFloat($this->lineGapHeight);
			}
			$out->putBool($this->depthTest);
			$out->putBool($this->showBackface);
			$out->putBool($this->showTextBackface);
		}
	}
}
