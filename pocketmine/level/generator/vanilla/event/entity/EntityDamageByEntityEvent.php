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

use pocketmine\level\generator\vanilla\entity\Effect;
use pocketmine\level\generator\vanilla\entity\Entity;
use pocketmine\level\generator\vanilla\entity\Living;

/**
 * Called when an entity takes damage from another entity.
 */
class EntityDamageByEntityEvent extends EntityDamageEvent
{
	/** @var int */
	protected $damagerEntityId;
	/** @var float */
	protected $knockBack;

	/**
	 * @param float[] $modifiers
	 */
	public function __construct(Entity $damager, Entity $entity, int $cause, float $damage, array $modifiers = [], float $knockBack = Living::DEFAULT_KNOCKBACK_FORCE)
	{
		$this->damagerEntityId = $damager->getId();
		$this->knockBack = $knockBack;
		parent::__construct($entity, $cause, $damage, $modifiers);
		$this->addAttackerModifiers($damager);
	}

	protected function addAttackerModifiers(Entity $damager) : void
	{
		if ($damager instanceof Living) { //TODO: move this to entity classes
			if ($damager->hasEffect(Effect::STRENGTH)) {
				$this->setModifier($this->getBaseDamage() * 0.3 * $damager->getEffect(Effect::STRENGTH)->getEffectLevel(), self::MODIFIER_STRENGTH);
			}

			if ($damager->hasEffect(Effect::WEAKNESS)) {
				$this->setModifier(-($this->getBaseDamage() * 0.2 * $damager->getEffect(Effect::WEAKNESS)->getEffectLevel()), self::MODIFIER_WEAKNESS);
			}
		}
	}

	/**
	 * Returns the attacking entity, or null if the attacker has been killed or closed.
	 */
	public function getDamager() : ?Entity
	{
		return $this->getEntity()->getLevel()->getServer()->findEntity($this->damagerEntityId);
	}

	public function getKnockBack() : float
	{
		return $this->knockBack;
	}

	public function setKnockBack(float $knockBack) : void
	{
		$this->knockBack = $knockBack;
	}
}
