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

namespace pocketmine\level\generator\vanilla\network\mcpe\convert\constants\commandArgumentTypeIds;

final class CommandArgumentTypeIds428
{
	private function __construct()
	{
		//NOOP
	}

	public const ARG_TYPE_INT = 0x01;
	public const ARG_TYPE_FLOAT = 0x03;
	public const ARG_TYPE_VALUE = 0x04;
	public const ARG_TYPE_WILDCARD_INT = 0x05;
	public const ARG_TYPE_OPERATOR = 0x06;
	public const ARG_TYPE_TARGET = 0x07;

	public const ARG_TYPE_WILDCARD_TARGET = 0x08;

	public const ARG_TYPE_FILEPATH = 0x10;

	public const ARG_TYPE_STRING = 0x20;

	public const ARG_TYPE_POSITION = 0x28;

	public const ARG_TYPE_MESSAGE = 0x2c;

	public const ARG_TYPE_RAWTEXT = 0x2e;

	public const ARG_TYPE_JSON = 0x32;

	public const ARG_TYPE_COMMAND = 0x3f;

}
