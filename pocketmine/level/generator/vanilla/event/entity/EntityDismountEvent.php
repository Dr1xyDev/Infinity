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

class EntityDismountEvent extends EntityEvent implements Cancellable
{
	protected Entity $ridden;
	protected bool $immediate;

	public function __construct(Entity $entity, Entity $ridden, bool $immediate = false)
	{
		$this->entity = $entity;
		$this->ridden = $ridden;
		$this->immediate = $immediate;
	}

	public function getRidden() : Entity
	{
		return $this->ridden;
	}

	public function getImmediate() : bool
	{
		return $this->immediate;
	}
}
