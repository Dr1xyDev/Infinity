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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\cereal;

use pocketmine\level\generator\vanilla\network\mcpe\NetworkBinaryStream;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\GetTypeIdFromConstTrait;
use function count;

final class DynamicValueMap extends DynamicValue{
	use GetTypeIdFromConstTrait;

	public const ID = DynamicValueType::MAP;

	/**
	 * @param (DynamicValue|null)[] $value
	 * @phpstan-param array<string, DynamicValue|null> $value
	 */
	public function __construct(
		private array $value
	){}

	/**
	 * @return (DynamicValue|null)[]
	 * @phpstan-return array<string, DynamicValue|null>
	 */
	public function getValue() : array{ return $this->value; }

	protected static function readValue(NetworkBinaryStream $in) : self{
		$value = [];

		for($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; $i++){
			$key = $in->getString();
			//YIKES! unchecked recursion ?!?!?! thank god this never gets sent by the client...
			$type = $in->getLInt();
			$value[$key] = DynamicValue::read($in, $type);
		}

		return new self($value);
	}

	protected function writeValue(NetworkBinaryStream $out) : void{
		$out->putUnsignedVarInt(count($this->value));
		foreach($this->value as $key => $value){
			$out->putString((string) $key);//make sure we don't get any unexpected strings casted to int
			$out->putLInt($value?->getTypeId() ?? DynamicValueType::NULL);
			$value?->write($out);
		}
	}
}
