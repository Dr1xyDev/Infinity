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
use pocketmine\level\generator\vanilla\entity\Living;
use pocketmine\level\generator\vanilla\entity\projectile\Projectile;
use pocketmine\level\generator\vanilla\event\Cancellable;
use pocketmine\level\generator\vanilla\item\Item;

use function count;

/**
 * @phpstan-extends EntityEvent<Living>
 */
class EntityShootBowEvent extends EntityEvent implements Cancellable
{
	/** @var Item */
	private $bow;
	/** @var Projectile */
	private $projectile;
	/** @var float */
	private $force;

	public function __construct(Living $shooter, Item $bow, Projectile $projectile, float $force)
	{
		$this->entity = $shooter;
		$this->bow = $bow;
		$this->projectile = $projectile;
		$this->force = $force;
	}

	/**
	 * @return Living
	 */
	public function getEntity()
	{
		return $this->entity;
	}

	public function getBow() : Item
	{
		return $this->bow;
	}

	/**
	 * Returns the entity considered as the projectile in this event.
	 *
	 * NOTE: This might not return a Projectile if a plugin modified the target entity.
	 */
	public function getProjectile() : Entity
	{
		return $this->projectile;
	}

	public function setProjectile(Entity $projectile) : void
	{
		if ($projectile !== $this->projectile) {
			if (count($this->projectile->getViewers()) === 0) {
				$this->projectile->close();
			}
			$this->projectile = $projectile;
		}
	}

	public function getForce() : float
	{
		return $this->force;
	}

	public function setForce(float $force) : void
	{
		$this->force = $force;
	}
}
