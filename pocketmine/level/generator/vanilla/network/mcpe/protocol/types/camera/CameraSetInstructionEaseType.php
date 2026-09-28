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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol\types\camera;

final class CameraSetInstructionEaseType
{
	private function __construct()
	{
		//NOOP
	}

	public const LINEAR = 0;
	public const SPRING = 1;
	public const IN_QUAD = 2;
	public const OUT_QUAD = 3;
	public const IN_OUT_QUAD = 4;
	public const IN_CUBIC = 5;
	public const OUT_CUBIC = 6;
	public const IN_OUT_CUBIC = 7;
	public const IN_QUART = 8;
	public const OUT_QUART = 9;
	public const IN_OUT_QUART = 10;
	public const IN_QUINT = 11;
	public const OUT_QUINT = 12;
	public const IN_OUT_QUINT = 13;
	public const IN_SINE = 14;
	public const OUT_SINE = 15;
	public const IN_OUT_SINE = 16;
	public const IN_EXPO = 17;
	public const OUT_EXPO = 18;
	public const IN_OUT_EXPO = 19;
	public const IN_CIRC = 20;
	public const OUT_CIRC = 21;
	public const IN_OUT_CIRC = 22;
	public const IN_BOUNCE = 23;
	public const OUT_BOUNCE = 24;
	public const IN_OUT_BOUNCE = 25;
	public const IN_BACK = 26;
	public const OUT_BACK = 27;
	public const IN_OUT_BACK = 28;
	public const IN_ELASTIC = 29;
	public const OUT_ELASTIC = 30;
	public const IN_OUT_ELASTIC = 31;

	/**
	 * Ease names used on the wire since 1.26.10 (spline keyframes) and in camera JSON definitions
	 */
	public static function toName(int $easeType) : string
	{
		foreach ((new \ReflectionClass(self::class))->getConstants() as $name => $value) {
			if ($value === $easeType) {
				return strtolower($name);
			}
		}
		return "linear";
	}

	public static function fromName(string $name) : int
	{
		$constant = self::class . "::" . strtoupper($name);
		return defined($constant) ? (int) constant($constant) : self::LINEAR;
	}
}
