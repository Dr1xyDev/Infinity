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

namespace pocketmine\level\generator\vanilla\level\format\io;

abstract class LevelProviderManagerEntry
{
	protected function __construct(
		protected \Closure $isValid
	) {
	}

	/**
	 * Tells if the path is a valid world.
	 * This must tell if the current format supports opening the files in the directory
	 */
	public function isValid(string $path) : bool
	{
		return ($this->isValid)($path);
	}

	abstract public function fromPath(string $path) : LevelProvider;
}
