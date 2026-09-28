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

use InvalidArgumentException;

/**
 * Trait for enums serialized in packets. Provides a convenient helper method to read, validate and properly bail on
 * invalid values.
 */
trait PacketIntEnumTrait
{
	/**
	 * @throws InvalidArgumentException
	 */
	public static function fromPacket(int $value) : self
	{
		$enum = self::tryFrom($value);
		if ($enum === null) {
			throw new InvalidArgumentException("Invalid raw value $value for " . static::class);
		}

		return $enum;
	}
}
