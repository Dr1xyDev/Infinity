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

// composer autoload doesn't use require_once and also pmmpthread can inherit things
if (defined('pocketmine\_GLOBAL_CONSTANTS_INCLUDED')) {
	return;
}
define('pocketmine\_GLOBAL_CONSTANTS_INCLUDED', true);

const UINT8_MAX = 0xff;
const INT8_MIN = -0x7f - 1;
const INT8_MAX = 0x7f;

const UINT16_MAX = 0xffff;
const INT16_MIN = -0x7fff - 1;
const INT16_MAX = 0x7fff;

const UINT32_MAX = 0xffffffff;
const INT32_MIN = -0x7fffffff - 1;
const INT32_MAX = 0x7fffffff;

const UINT64_MAX = 0xffffffffffffffff;
const INT64_MIN = -0x7fffffffffffffff - 1;
const INT64_MAX = 0x7fffffffffffffff;
