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

final class DynamicValueLong extends DynamicValue{
	use GetTypeIdFromConstTrait;

	public const ID = DynamicValueType::LONG;

	public function __construct(
		private int $value
	){}

	public function getValue() : int{ return $this->value; }

	protected static function readValue(NetworkBinaryStream $in) : self{
		return new self($in->getLLong());
	}

	protected function writeValue(NetworkBinaryStream $out) : void{
		$out->putLLong($this->value);
	}
}
