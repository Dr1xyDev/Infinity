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

namespace pocketmine\level\generator\vanilla\entity\behavior;

final class BehaviorEntry
{
	/** @var int */
	protected $priority;
	/** @var Behavior */
	protected $behavior;

	public function __construct(int $priority, Behavior $behavior)
	{
		$this->priority = $priority;
		$this->behavior = $behavior;
	}

	public function getPriority() : int
	{
		return $this->priority;
	}

	public function getBehavior() : Behavior
	{
		return $this->behavior;
	}
}
