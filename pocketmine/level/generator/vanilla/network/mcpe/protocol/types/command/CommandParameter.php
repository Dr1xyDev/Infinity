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

use pocketmine\level\generator\vanilla\network\mcpe\protocol\AvailableCommandsPacket;

class CommandParameter
{
	public const FLAG_FORCE_COLLAPSE_ENUM = 0x1;
	public const FLAG_HAS_ENUM_CONSTRAINT = 0x2;

	public string $paramName;
	public int $paramType;
	public bool $isOptional;
	public int $flags = 0; //shows enum name if 1, always zero except for in /gamerule command
	public ?CommandEnum $enum = null;
	public ?string $postfix = null;

	public function __construct(string $paramName = "args", int $paramType = AvailableCommandsPacket::ARG_TYPE_RAWTEXT, bool $isOptional = true, int $flags = 0, ?CommandEnum $enum = null)
	{
		$this->paramName = $paramName;
		$this->paramType = AvailableCommandsPacket::ARG_FLAG_VALID | $paramType;
		$this->isOptional = $isOptional;
		$this->flags = $flags;
		$this->enum = $enum;
	}

	private static function baseline(string $name, int $type, int $flags, bool $optional) : self
	{
		$result = new self();
		$result->paramName = $name;
		$result->paramType = $type;
		$result->flags = $flags;
		$result->isOptional = $optional;
		return $result;
	}

	public static function standard(string $name, int $type, int $flags = 0, bool $optional = false) : self
	{
		return self::baseline($name, AvailableCommandsPacket::ARG_FLAG_VALID | $type, $flags, $optional);
	}

	public static function postfixed(string $name, string $postfix, int $flags = 0, bool $optional = false) : self
	{
		$result = self::baseline($name, AvailableCommandsPacket::ARG_FLAG_POSTFIX, $flags, $optional);
		$result->postfix = $postfix;
		return $result;
	}

	public static function enum(string $name, CommandEnum $enum, int $flags, bool $optional = false) : self
	{
		$result = self::baseline($name, AvailableCommandsPacket::ARG_FLAG_ENUM | AvailableCommandsPacket::ARG_FLAG_VALID, $flags, $optional);
		$result->enum = $enum;
		return $result;
	}
}
