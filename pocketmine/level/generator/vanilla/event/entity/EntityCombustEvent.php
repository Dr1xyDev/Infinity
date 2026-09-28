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

namespace pocketmine\level\generator\vanilla\event\entity;

use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\event\Cancellable;

/**
 * @phpstan-extends EntityEvent<Entity>
 */
class EntityCombustEvent extends EntityEvent implements Cancellable
{
	/** @var int */
	protected $duration;

	public function __construct(Entity $combustee, int $duration)
	{
		$this->entity = $combustee;
		$this->duration = $duration;
	}

	/**
	 * Returns the duration in seconds the entity will burn for.
	 */
	public function getDuration() : int
	{
		return $this->duration;
	}

	public function setDuration(int $duration) : void
	{
		$this->duration = $duration;
	}
}
