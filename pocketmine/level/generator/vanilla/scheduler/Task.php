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

use pocketmine\level\generator\vanilla\utils\Utils;

abstract class Task
{
	/** @var TaskHandler|null */
	private $taskHandler = null;

	/**
	 * @return TaskHandler|null
	 */
	final public function getHandler()
	{
		return $this->taskHandler;
	}

	final public function getTaskId() : int
	{
		if ($this->taskHandler !== null) {
			return $this->taskHandler->getTaskId();
		}

		return -1;
	}

	public function getName() : string
	{
		return Utils::getNiceClassName($this);
	}

	final public function setHandler(?TaskHandler $taskHandler = null)
	{
		if ($this->taskHandler === null || $taskHandler === null) {
			$this->taskHandler = $taskHandler;
		}
	}

	/**
	 * Actions to execute when run
	 *
	 * @return void
	 */
	abstract public function onRun(int $currentTick);

	/**
	 * Actions to execute if the Task is cancelled
	 */
	public function onCancel()
	{

	}
}
