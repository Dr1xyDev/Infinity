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
use pocketmine\level\generator\vanilla\network\mcpe\protocol\PacketDecodeException;

abstract class DynamicValue{
	abstract public function getTypeId() : int;

	abstract protected function writeValue(NetworkBinaryStream $out) : void;

	final public function write(NetworkBinaryStream $out) : void{
		$this->writeValue($out);
	}

	final public static function read(NetworkBinaryStream $in, int $type) : ?self{
		//TODO: I don't like putting this here (cyclic dependency) but I don't know where else to put it for now.
		//Really we need to revamp how unions are handled in general, but that's a job for another time
		return match($type){
			DynamicValueType::NULL => null,
			DynamicValueBool::ID => DynamicValueBool::readValue($in),
			DynamicValueLong::ID => DynamicValueLong::readValue($in),
			DynamicValueDouble::ID => DynamicValueDouble::readValue($in),
			DynamicValueString::ID => DynamicValueString::readValue($in),
			DynamicValueList::ID => DynamicValueList::readValue($in),
			DynamicValueMap::ID => DynamicValueMap::readValue($in),
			default => throw new PacketDecodeException("Unknown dynamic value type $type")
		};
	}
}
