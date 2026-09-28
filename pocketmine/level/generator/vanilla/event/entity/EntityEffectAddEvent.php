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

use pocketmine\level\generator\vanilla\entity\EffectInstance;
use pocketmine\level\generator\vanilla\entity\Entity;

/**
 * Called when an effect is added to an Entity.
 */
class EntityEffectAddEvent extends EntityEffectEvent
{
	/** @var EffectInstance|null */
	private $oldEffect;

	public function __construct(Entity $entity, EffectInstance $effect, ?EffectInstance $oldEffect = null)
	{
		parent::__construct($entity, $effect);
		$this->oldEffect = $oldEffect;
	}

	/**
	 * Returns whether the effect addition will replace an existing effect already applied to the entity.
	 */
	public function willModify() : bool
	{
		return $this->hasOldEffect();
	}

	public function hasOldEffect() : bool
	{
		return $this->oldEffect instanceof EffectInstance;
	}

	public function getOldEffect() : ?EffectInstance
	{
		return $this->oldEffect;
	}
}
