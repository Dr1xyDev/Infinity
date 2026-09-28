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

use pocketmine\level\generator\vanilla\network\mcpe\protocol\PacketDecodeException;
use function array_flip;

enum DebugRendererType : int
{
	use PacketIntEnumTrait;

	case CLEAR_NUMBER = 1;
	case ADD_CUBE_NUMBER = 2;

	private const DEBUG_RENDERER_TYPES = [ // enum case references requires PHP 8.2
		1 => "cleardebugmarkers",
		2 => "adddebugmarkercube"
	];

	public function getName() : string{
		return self::DEBUG_RENDERER_TYPES[$this->value];
	}

	public static function fromName(string $name) : self{
		static $cache = null;
		if($cache === null){
			$cache = array_flip(self::DEBUG_RENDERER_TYPES);
		}

		$value = $cache[$name] ?? throw new PacketDecodeException("Invalid raw value $name for " . static::class);

		return self::fromPacket($value);
	}
}
