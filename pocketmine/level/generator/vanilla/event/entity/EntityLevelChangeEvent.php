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
use pocketmine\level\generator\vanilla\level\Level;

/**
 * @phpstan-extends EntityEvent<Entity>
 */
class EntityLevelChangeEvent extends EntityEvent implements Cancellable
{
	/** @var Level */
	private $originLevel;
	/** @var Level */
	private $targetLevel;

	public function __construct(Entity $entity, Level $originLevel, Level $targetLevel)
	{
		$this->entity = $entity;
		$this->originLevel = $originLevel;
		$this->targetLevel = $targetLevel;
	}

	public function getOrigin() : Level
	{
		return $this->originLevel;
	}

	public function getTarget() : Level
	{
		return $this->targetLevel;
	}
}
