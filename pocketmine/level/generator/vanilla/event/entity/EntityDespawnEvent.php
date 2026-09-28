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

use pocketmine\level\generator\vanilla\entity\Creature;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\entity\Human;
use pocketmine\level\generator\vanilla\entity\object\ItemEntity;
use pocketmine\level\generator\vanilla\entity\projectile\Projectile;
use pocketmine\level\generator\vanilla\entity\Vehicle;

/**
 * Called when a entity is despawned
 * @phpstan-extends EntityEvent<Entity>
 */
class EntityDespawnEvent extends EntityEvent
{
	/** @var int */
	private $entityType;

	public function __construct(Entity $entity)
	{
		$this->entity = $entity;
		$this->entityType = $entity::NETWORK_ID;
	}

	/**
	 * @deprecated
	 */
	public function getType() : int
	{
		return $this->entityType;
	}

	/**
	 * @deprecated
	 */
	public function isCreature() : bool
	{
		return $this->entity instanceof Creature;
	}

	/**
	 * @deprecated
	 */
	public function isHuman() : bool
	{
		return $this->entity instanceof Human;
	}

	/**
	 * @deprecated
	 */
	public function isProjectile() : bool
	{
		return $this->entity instanceof Projectile;
	}

	/**
	 * @deprecated
	 */
	public function isVehicle() : bool
	{
		return $this->entity instanceof Vehicle;
	}

	/**
	 * @deprecated
	 */
	public function isItem() : bool
	{
		return $this->entity instanceof ItemEntity;
	}
}
