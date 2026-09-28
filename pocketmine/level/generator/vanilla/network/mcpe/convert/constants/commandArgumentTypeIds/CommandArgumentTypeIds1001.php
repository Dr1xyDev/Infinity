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

final class CommandArgumentTypeIds1001
{
	private function __construct()
	{
		//NOOP
	}

	public const ARG_TYPE_INT = 1;
	public const ARG_TYPE_FLOAT = 3;
	public const ARG_TYPE_VALUE = 4;
	public const ARG_TYPE_WILDCARD_INT = 5;
	public const ARG_TYPE_OPERATOR = 6;
	public const ARG_TYPE_COMPARE_OPERATOR = 7;
	public const ARG_TYPE_TARGET = 8;

	public const ARG_TYPE_WILDCARD_TARGET = 10;

	public const ARG_TYPE_FILEPATH = 17;

	public const ARG_TYPE_FULL_INTEGER_RANGE = 23;

	public const ARG_TYPE_EQUIPMENT_SLOT = 47;
	public const ARG_TYPE_STRING = 56;

	public const ARG_TYPE_INT_POSITION = 64;
	public const ARG_TYPE_POSITION = 65;

	public const ARG_TYPE_MESSAGE = 68;

	public const ARG_TYPE_RAWTEXT = 70;

	public const ARG_TYPE_JSON = 74;

	public const ARG_TYPE_BLOCK_STATES = 84;

	public const ARG_TYPE_TIMEMARKER_NAME = 86;

	public const ARG_TYPE_COMMAND = 88;

}
