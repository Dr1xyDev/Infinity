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

final class AttributeData{

	public function __construct(
		public int $type,
		public ?bool $boolValue = null,
		public ?int $boolOperation = null,
		public ?float $floatValue = null,
		public ?int $floatOperation = null,
		public ?float $floatConstraintMin = null,
		public ?float $floatConstraintMax = null,
		public ?int $colourValue = null,
		public ?int $colourOperation = null
	){}

	public static function read(NetworkBinaryStream $in) : self{
		$type = $in->getUnsignedVarInt();

		return match($type){
			AttributeDataType::BOOL => new self(
				type: $type,
				boolValue: $in->getBool(),
				boolOperation: $in->getOptional($in->getLInt(...))
			),
			AttributeDataType::FLOAT => new self(
				type: $type,
				floatValue: $in->getLFloat(),
				floatOperation: $in->getOptional($in->getLInt(...)),
				floatConstraintMin: $in->getOptional($in->getLFloat(...)),
				floatConstraintMax: $in->getOptional($in->getLFloat(...))
			),
			AttributeDataType::COLOUR => new self(
				type: $type,
				colourValue: $in->getLInt(),
				colourOperation: $in->getOptional($in->getLInt(...))
			),
			default => throw new \UnexpectedValueException("Unknown attribute data type $type")
		};
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putUnsignedVarInt($this->type);
		match($this->type){
			AttributeDataType::BOOL => $this->writeBool($out),
			AttributeDataType::FLOAT => $this->writeFloat($out),
			AttributeDataType::COLOUR => $this->writeColour($out),
			default => throw new \UnexpectedValueException("Unknown attribute data type $this->type")
		};
	}

	private function writeBool(NetworkBinaryStream $out) : void{
		$out->putBool($this->boolValue ?? false);
		$out->putOptional($this->boolOperation, $out->putLInt(...));
	}

	private function writeFloat(NetworkBinaryStream $out) : void{
		$out->putLFloat($this->floatValue ?? 0.0);
		$out->putOptional($this->floatOperation, $out->putLInt(...));
		$out->putOptional($this->floatConstraintMin, $out->putLFloat(...));
		$out->putOptional($this->floatConstraintMax, $out->putLFloat(...));
	}

	private function writeColour(NetworkBinaryStream $out) : void{
		$out->putLInt($this->colourValue ?? 0);
		$out->putOptional($this->colourOperation, $out->putLInt(...));
	}
}
