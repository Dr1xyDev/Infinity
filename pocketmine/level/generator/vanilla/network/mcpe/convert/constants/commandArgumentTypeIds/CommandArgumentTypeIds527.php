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

final class CommandArgumentTypeIds527
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
	public const ARG_TYPE_COMPARE_OPERATOR = 0x07;
	public const ARG_TYPE_TARGET = 0x08;

	public const ARG_TYPE_WILDCARD_TARGET = 0x0a;

	public const ARG_TYPE_FILEPATH = 0x11;

	public const ARG_TYPE_FULL_INTEGER_RANGE = 0x17;

	public const ARG_TYPE_EQUIPMENT_SLOT = 0x26;
	public const ARG_TYPE_STRING = 0x27;

	public const ARG_TYPE_INT_POSITION = 0x2f;
	public const ARG_TYPE_POSITION = 0x30;

	public const ARG_TYPE_MESSAGE = 0x33;

	public const ARG_TYPE_RAWTEXT = 0x35;

	public const ARG_TYPE_JSON = 0x39;

	public const ARG_TYPE_BLOCK_STATES = 0x43;

	public const ARG_TYPE_COMMAND = 0x46;

}
