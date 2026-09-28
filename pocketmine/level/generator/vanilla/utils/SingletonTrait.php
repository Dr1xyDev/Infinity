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

trait SingletonTrait
{
	/** @var self|null */
	private static $instance = null;

	private static function make() : self
	{
		return new self();
	}

	public static function getInstance() : self
	{
		if (self::$instance === null) {
			self::$instance = self::make();
		}
		return self::$instance;
	}

	public static function setInstance(self $instance) : void
	{
		self::$instance = $instance;
	}

	public static function reset() : void
	{
		self::$instance = null;
	}
}
