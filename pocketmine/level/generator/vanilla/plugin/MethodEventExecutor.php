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

namespace pocketmine\level\generator\vanilla\plugin;

use pocketmine\level\generator\vanilla\event\Event;
use pocketmine\level\generator\vanilla\event\Listener;

class MethodEventExecutor implements EventExecutor
{
	/** @var string */
	private $method;

	/**
	 * @param string $method
	 */
	public function __construct($method)
	{
		$this->method = $method;
	}

	public function execute(Listener $listener, Event $event)
	{
		$listener->{$this->getMethod()}($event);
	}

	/**
	 * @return string
	 */
	public function getMethod()
	{
		return $this->method;
	}
}
