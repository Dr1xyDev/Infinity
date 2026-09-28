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

namespace pocketmine\level\generator\vanilla\scheduler;

use pocketmine\level\generator\vanilla\timings\TimingsHandler;

final class TimingsControlTask extends AsyncTask
{
	private const ENABLE = 1;
	private const DISABLE = 2;
	private const RELOAD = 3;

	private function __construct(
		private int $operation
	) {
	}

	public static function setEnabled(bool $enable) : self
	{
		return new self($enable ? self::ENABLE : self::DISABLE);
	}

	public static function reload() : self
	{
		return new self(self::RELOAD);
	}

	public function onRun() : void
	{
		if ($this->operation === self::ENABLE) {
			TimingsHandler::setEnabled(true);
			\GlobalLogger::get()->debug("Enabled timings");
		} elseif ($this->operation === self::DISABLE) {
			TimingsHandler::setEnabled(false);
			\GlobalLogger::get()->debug("Disabled timings");
		} elseif ($this->operation === self::RELOAD) {
			TimingsHandler::reload();
			\GlobalLogger::get()->debug("Reset timings");
		} else {
			throw new \InvalidArgumentException("Invalid operation $this->operation");
		}
	}
}
