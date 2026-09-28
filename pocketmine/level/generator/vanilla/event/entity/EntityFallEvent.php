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

class EntityFallEvent extends EntityEvent implements Cancellable
{
	protected float $fallDistance;

	public function __construct(Entity $entity, float $fallDistance)
	{
		$this->entity = $entity;
		$this->fallDistance = $fallDistance;
	}

	public function getFallDistance() : float
	{
		return $this->fallDistance;
	}

	public function setFallDistance(float $fallDistance) : void
	{
		$this->fallDistance = $fallDistance;
	}
}
