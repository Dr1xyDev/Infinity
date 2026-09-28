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

namespace pocketmine\level\generator\vanilla\utils;

use LogicException;

trait NotSerializable
{
	/** @return mixed[] */
	final public function __serialize() : array
	{
		throw new LogicException("Serialization of " . static::class . " objects is not allowed");
	}

	/** @param mixed[] $data */
	final public function __unserialize(array $data) : void
	{
		throw new LogicException("Unserialization of " . static::class . " objects is not allowed");
	}
}
