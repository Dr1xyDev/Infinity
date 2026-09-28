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

final class DynamicValueString extends DynamicValue{
	use GetTypeIdFromConstTrait;

	public const ID = DynamicValueType::STRING;

	public function __construct(
		private string $value
	){}

	public function getValue() : string{ return $this->value; }

	protected static function readValue(NetworkBinaryStream $in) : self{
		return new self($in->getString());
	}

	protected function writeValue(NetworkBinaryStream $out) : void{
		$out->putString($this->value);
	}
}
