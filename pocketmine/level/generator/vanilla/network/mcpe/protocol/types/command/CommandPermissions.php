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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\command;

use pocketmine\level\generator\vanilla\network\mcpe\protocol\PacketDecodeException;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\PacketIntEnumTrait;
use function array_flip;

enum CommandPermissions : int{
	use PacketIntEnumTrait;

	case NORMAL = 0;
	case OPERATOR = 1;
	case AUTOMATION = 2; //command blocks
	case HOST = 3; //hosting player on LAN multiplayer
	case OWNER = 4; //server terminal on BDS
	case INTERNAL = 5;

	private const PERMISSION_NAMES = [ // enum case references requires PHP 8.2
		0 => "any",
		1 => "gamedirectors",
		2 => "admin",
		3 => "host",
		4 => "owner",
		5 => "internal",
	];

	public function getName() : string{
		return self::PERMISSION_NAMES[$this->value];
	}

	public static function fromName(string $name) : self{
		static $cache = null;
		if($cache === null){
			$cache = array_flip(self::PERMISSION_NAMES);
		}

		$value = $cache[$name] ?? throw new PacketDecodeException("Invalid raw value $name for " . static::class);

		return self::fromPacket($value);
	}
}
