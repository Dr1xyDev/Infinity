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

namespace pocketmine\level\generator\vanilla\block\utils;

class ColorBlockMetaHelper
{
	public const WHITE = 0;
	public const ORANGE = 1;
	public const MAGENTA = 2;
	public const LIGHT_BLUE = 3;
	public const YELLOW = 4;
	public const LIME = 5;
	public const PINK = 6;
	public const GRAY = 7;
	public const LIGHT_GRAY = 8;
	public const CYAN = 9;
	public const PURPLE = 10;
	public const BLUE = 11;
	public const BROWN = 12;
	public const GREEN = 13;
	public const RED = 14;
	public const BLACK = 15;

	public static function getColorFromMeta(int $meta) : string
	{
		static $names = [
			0 => "White",
			1 => "Orange",
			2 => "Magenta",
			3 => "Light Blue",
			4 => "Yellow",
			5 => "Lime",
			6 => "Pink",
			7 => "Gray",
			8 => "Light Gray",
			9 => "Cyan",
			10 => "Purple",
			11 => "Blue",
			12 => "Brown",
			13 => "Green",
			14 => "Red",
			15 => "Black"
		];

		return $names[$meta] ?? "Unknown";
	}

	public static function getMetaFromColor(string $name) : int
	{
		static $names = [
			"white" => 0,
			"orange" => 1,
			"magenta" => 2,
			"light_blue" => 3,
			"yellow" => 4,
			"lime" => 5,
			"pink" => 6,
			"gray" => 7,
			"light_gray" => 8,
			"cyan" => 9,
			"purple" => 10,
			"blue" => 11,
			"brown" => 12,
			"green" => 13,
			"red" => 14,
			"black" => 15
		];

		return $names[$name] ?? 0;
	}
}
