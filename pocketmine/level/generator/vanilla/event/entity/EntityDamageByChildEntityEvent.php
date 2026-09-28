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

/**
 * Called when an entity takes damage from an entity sourced from another entity, for example being hit by a snowball thrown by a Player.
 */
class EntityDamageByChildEntityEvent extends EntityDamageByEntityEvent
{
	/** @var int */
	private $childEntityEid;

	/**
	 * @param float[] $modifiers
	 */
	public function __construct(Entity $damager, Entity $childEntity, Entity $entity, int $cause, float $damage, array $modifiers = [], float $knockBack = Living::DEFAULT_KNOCKBACK_FORCE)
	{
		$this->childEntityEid = $childEntity->getId();
		parent::__construct($damager, $entity, $cause, $damage, $modifiers, $knockBack);
	}

	/**
	 * Returns the entity which caused the damage, or null if the entity has been killed or closed.
	 */
	public function getChild() : ?Entity
	{
		return $this->getEntity()->getLevel()->getServer()->findEntity($this->childEntityEid);
	}
}
