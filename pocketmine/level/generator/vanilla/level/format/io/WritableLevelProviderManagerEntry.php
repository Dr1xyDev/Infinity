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

use pocketmine\level\generator\vanilla\level\LevelCreationOptions;

final class WritableLevelProviderManagerEntry extends LevelProviderManagerEntry
{
	public function __construct(
		\Closure $isValid,
		private \Closure $fromPath,
		private \Closure $generate
	) {
		parent::__construct($isValid);
	}

	public function fromPath(string $path) : WritableLevelProvider
	{
		return ($this->fromPath)($path);
	}

	/**
	 * Generates world manifest files and any other things needed to initialize a new world on disk
	 */
	public function generate(string $path, string $name, LevelCreationOptions $options) : void
	{
		($this->generate)($path, $name, $options);
	}
}
