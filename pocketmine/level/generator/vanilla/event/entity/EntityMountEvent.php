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

class EntityMountEvent extends EntityEvent implements Cancellable
{
	protected Entity $ridden;
	protected int $seatNumber;
	protected bool $causedByRider;

	public function __construct(Entity $entity, Entity $ridden, int $seatNumber = 0, bool $causedByRider = true)
	{
		$this->entity = $entity;
		$this->ridden = $ridden;
		$this->seatNumber = $seatNumber;
		$this->causedByRider = $causedByRider;
	}

	public function getRidden() : Entity
	{
		return $this->ridden;
	}

	public function getSeatNumber() : int
	{
		return $this->seatNumber;
	}

	public function getCausedByRider() : bool
	{
		return $this->causedByRider;
	}
}
