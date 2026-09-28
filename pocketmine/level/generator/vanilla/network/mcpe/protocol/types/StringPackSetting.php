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

final class StringPackSetting extends PackSetting{
	public const ID = PackSettingType::STRING;

	private string $value;

	public function __construct(string $name, string $value){
		parent::__construct($name);
		$this->value = $value;
	}

	public function getValue() : string{
		return $this->value;
	}

	public function getTypeId() : PackSettingType{
		return self::ID;
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putString($this->value);
	}

	public static function read(NetworkBinaryStream $in, string $name) : self{
		return new self($name, $in->getString());
	}
}
