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

final class DeviceOS
{
	public const UNKNOWN = -1;
	public const ANDROID = 1;
	public const IOS = 2;
	public const OSX = 3;
	public const AMAZON = 4;
	public const GEAR_VR = 5;
	public const HOLOLENS = 6;
	public const WINDOWS_10 = 7;
	public const WIN32 = 8;
	public const DEDICATED = 9;
	public const TVOS = 10;
	public const PLAYSTATION = 11;
	public const NINTENDO = 12;
	public const XBOX = 13;
	public const WINDOWS_PHONE = 14;

}
