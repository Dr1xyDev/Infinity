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

enum OriginDataType : int
{
	use PacketIntEnumTrait;

	case ORIGIN_PLAYER = 0;
	case ORIGIN_BLOCK = 1;
	case ORIGIN_MINECART_BLOCK = 2;
	case ORIGIN_DEV_CONSOLE = 3;
	case ORIGIN_TEST = 4;
	case ORIGIN_AUTOMATION_PLAYER = 5;
	case ORIGIN_CLIENT_AUTOMATION = 6;
	case ORIGIN_DEDICATED_SERVER = 7;
	case ORIGIN_ENTITY = 8;
	case ORIGIN_VIRTUAL = 9;
	case ORIGIN_GAME_ARGUMENT = 10;
	case ORIGIN_ENTITY_SERVER = 11;
	case ORIGIN_PRECOMPILED = 12;
	case ORIGIN_GAME_DIRECTOR_ENTITY_SERVER = 13;
	case ORIGIN_SCRIPTING = 14;
	case ORIGIN_EXECUTE_CONTEXT = 15;

	private const OUTPUT_TYPES = [ // enum case references requires PHP 8.2
		0 => "player",
		1 => "commandblock",
		2 => "minecartcommandblock",
		3 => "devconsole",
		4 => "test",
		5 => "automationplayer",
		6 => "clientautomation",
		7 => "dedicatedserver",
		8 => "entity",
		9 => "virtual",
		10 => "gameargument",
		11 => "entityserver",
		12 => "precompiled",
		13 => "gamedirectorentityserver",
		14 => "scripting",
		15 => "executecontext"
	];

	public function getName() : string{
		return self::OUTPUT_TYPES[$this->value];
	}

	public static function fromName(string $name) : self{
		static $cache = null;
		if($cache === null){
			$cache = array_flip(self::OUTPUT_TYPES);
		}

		$value = $cache[$name] ?? throw new PacketDecodeException("Invalid raw value $name for " . static::class);

		return self::fromPacket($value);
	}
}
