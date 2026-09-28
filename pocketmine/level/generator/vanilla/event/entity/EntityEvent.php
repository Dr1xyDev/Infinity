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

/**
 * Entity related Events, like spawn, inventory, attack...
 */

namespace pocketmine\level\generator\vanilla\event\entity;

use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\event\Event;

/**
 * @phpstan-template TEntity of Entity
 */
abstract class EntityEvent extends Event
{
	public function __construct(
		protected Entity $entity,
	) {
	}

	/**
	 * @return Entity
	 * @phpstan-return TEntity
	 */
	public function getEntity()
	{
		return $this->entity;
	}
}
