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

final class DynamicValueList extends DynamicValue{
	use GetTypeIdFromConstTrait;

	public const ID = DynamicValueType::LIST;

	/**
	 * @param (DynamicValue|null)[] $values
	 * @phpstan-param list<DynamicValue|null> $values
	 */
	public function __construct(
		private array $values
	){}

	/**
	 * @return (DynamicValue|null)[]
	 * @phpstan-return list<DynamicValue|null>
	 */
	public function getValues() : array{
		return $this->values;
	}

	protected static function readValue(NetworkBinaryStream $in) : self{
		$size = $in->getUnsignedVarInt();
		$values = [];
		for($i = 0; $i < $size; ++$i){
			$type = $in->getLInt();
			$values[] = DynamicValue::read($in, $type);
		}
		return new self($values);
	}

	protected function writeValue(NetworkBinaryStream $out) : void{
		$out->putUnsignedVarInt(count($this->values));
		foreach($this->values as $value){
			$out->putLInt($value?->getTypeId() ?? DynamicValueType::NULL);
			$value?->write($out);
		}
	}
}
